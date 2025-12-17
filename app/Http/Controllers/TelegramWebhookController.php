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
            $update = $request->all();
            $botToken = config('verification.telegram.bot_token');

            \Log::info('Telegram webhook received', ['update' => $update]);

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
}
