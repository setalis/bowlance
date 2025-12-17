<?php

namespace App\Http\Controllers;

use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationService $verificationService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            // Проверка секретного токена webhook (если настроен)
            $secretToken = config('verification.telegram.webhook_secret_token');
            if ($secretToken) {
                $receivedToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
                if ($receivedToken !== $secretToken) {
                    \Log::warning('Telegram webhook: Invalid secret token', [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    return response()->json(['ok' => false, 'error' => 'Unauthorized'], 401);
                }
            }

            // Проверка IP адресов Telegram (опционально, но рекомендуется)
            $telegramIps = [
                '149.154.160.0/20',
                '91.108.4.0/22',
            ];
            $clientIp = $request->ip();
            $isFromTelegram = $this->isIpInRange($clientIp, $telegramIps);

            if (! $isFromTelegram) {
                \Log::warning('Telegram webhook: Request from unknown IP', [
                    'ip' => $clientIp,
                    'user_agent' => $request->userAgent(),
                ]);
                // Не блокируем, так как IP могут меняться, но логируем
            }

            $update = $request->all();
            $botToken = config('verification.telegram.bot_token');

            \Log::info('Telegram webhook received', [
                'ip' => $clientIp,
                'is_from_telegram' => $isFromTelegram,
            ]);

            if (empty($botToken)) {
                \Log::error('Telegram bot token is not configured');

                return response()->json(['ok' => false, 'error' => 'Bot token not configured'], 500);
            }

            if (! isset($update['message'])) {
                \Log::info('No message in update');

                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'] ?? null;
            $text = $message['text'] ?? '';

            \Log::info('Processing message', ['chat_id' => $chatId, 'text' => $text]);

            // Обработка команды /start с токеном
            if (str_starts_with($text, '/start')) {
                $parts = explode(' ', $text);
                $token = $parts[1] ?? null;

                if ($token && $chatId) {
                    \Log::info('Processing /start with token', ['token' => $token, 'chat_id' => $chatId]);

                    $verification = $this->verificationService->completeVerificationStart($token, (string) $chatId);

                    if ($verification) {
                        $responseText = "✅ Код подтверждения отправлен!\n\nПроверьте сообщение с кодом и введите его на сайте для подтверждения заказа.";
                        \Log::info('Verification completed successfully', ['verification_id' => $verification->id]);
                    } else {
                        $responseText = '❌ Ошибка: токен верификации недействителен или истек. Пожалуйста, попробуйте снова на сайте.';
                        \Log::warning('Verification failed', ['token' => $token]);
                    }

                    $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $responseText,
                    ]);

                    if (! $response->successful()) {
                        \Log::error('Failed to send Telegram message', [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                } else {
                    // Если команда /start без токена
                    \Log::info('Processing /start without token');

                    $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => "👋 Добро пожаловать!\n\nДля подтверждения заказа перейдите на сайт и нажмите кнопку 'Подтвердить через Telegram'.",
                    ]);

                    if (! $response->successful()) {
                        \Log::error('Failed to send welcome message', [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                }
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            \Log::error('Telegram webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['ok' => false, 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * Проверка, находится ли IP адрес в диапазоне Telegram
     */
    private function isIpInRange(string $ip, array $ranges): bool
    {
        foreach ($ranges as $range) {
            if (str_contains($range, '/')) {
                [$subnet, $mask] = explode('/', $range);
                if ($this->ipInCidr($ip, $subnet, (int) $mask)) {
                    return true;
                }
            } elseif ($ip === $range) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка, находится ли IP в CIDR диапазоне
     */
    private function ipInCidr(string $ip, string $subnet, int $mask): bool
    {
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
