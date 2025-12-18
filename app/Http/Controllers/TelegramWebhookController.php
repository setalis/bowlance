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
            $contact = $message['contact'] ?? null;

            \Log::info('Processing message', ['chat_id' => $chatId, 'text' => $text, 'has_contact' => $contact !== null]);

            // Обработка контакта (кнопка "поделиться номером")
            if ($contact && $chatId) {
                $telegramPhone = $contact['phone_number'] ?? null;
                $contactUserId = $contact['user_id'] ?? null;

                \Log::info('Processing contact', [
                    'chat_id' => $chatId,
                    'telegram_phone' => $telegramPhone,
                    'contact_user_id' => $contactUserId,
                ]);

                // Проверяем, что контакт принадлежит пользователю, который отправил сообщение
                if ($contactUserId && (string) $contactUserId !== (string) $chatId) {
                    $responseText = '❌ Ошибка: вы можете поделиться только своим номером телефона.';
                } else {
                    // Ищем активную верификацию для этого чата
                    $verification = \App\Models\PhoneVerification::where('telegram_chat_id', (string) $chatId)
                        ->whereNull('verified_at')
                        ->where('expires_at', '>', now())
                        ->whereNotNull('verification_token')
                        ->latest()
                        ->first();

                    if ($verification && $telegramPhone) {
                        // Сохраняем текущий статус заказа ДО верификации
                        $orderBeforeVerification = $verification->order;
                        $orderStatusBefore = $orderBeforeVerification ? $orderBeforeVerification->status : null;

                        $result = $this->verificationService->verifyPhoneNumber(
                            $verification->verification_token,
                            (string) $chatId,
                            $telegramPhone
                        );

                        $responseText = $result['message'];

                        // Проверяем, что заказ не был обновлен, если верификация не прошла
                        if (! $result['success']) {
                            // Перезагружаем заказ из БД, чтобы убедиться, что статус не изменился
                            $orderAfterVerification = $verification->order->fresh();
                            if ($orderAfterVerification && $orderStatusBefore && $orderAfterVerification->status !== $orderStatusBefore) {
                                // КРИТИЧЕСКАЯ ОШИБКА: статус заказа изменился, хотя верификация не прошла!
                                \Log::error('CRITICAL: Order status changed despite failed verification!', [
                                    'order_id' => $orderAfterVerification->id,
                                    'order_status_before' => $orderStatusBefore,
                                    'order_status_after' => $orderAfterVerification->status,
                                    'verification_id' => $verification->id,
                                    'verification_success' => $result['success'],
                                ]);

                                // Откатываем изменение статуса
                                $orderAfterVerification->update(['status' => $orderStatusBefore]);
                                \Log::warning('Order status rolled back to original status', [
                                    'order_id' => $orderAfterVerification->id,
                                    'restored_status' => $orderStatusBefore,
                                ]);
                            }
                        }

                        \Log::info('Phone verification result', [
                            'success' => $result['success'],
                            'verification_id' => $verification->id,
                            'order_id' => $orderBeforeVerification->id ?? null,
                            'order_status_before' => $orderStatusBefore,
                            'order_status_after' => $orderBeforeVerification ? $orderBeforeVerification->fresh()->status : null,
                        ]);
                    } else {
                        $responseText = '❌ Ошибка: не найдена активная верификация. Пожалуйста, начните процесс верификации на сайте.';
                        \Log::warning('No active verification found for contact', ['chat_id' => $chatId]);
                    }
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

                return response()->json(['ok' => true]);
            }

            // Обработка команды /start с токеном
            if (str_starts_with($text, '/start')) {
                // Извлекаем токен из команды /start TOKEN
                // Telegram может передавать токен как часть текста после /start
                $parts = explode(' ', $text, 2);
                $token = $parts[1] ?? null;

                // Декодируем токен на случай URL-кодирования
                if ($token) {
                    $token = urldecode($token);
                    $token = trim($token);
                }

                \Log::info('Processing /start command', [
                    'text' => $text,
                    'text_length' => strlen($text),
                    'token' => $token,
                    'token_length' => $token ? strlen($token) : 0,
                    'chat_id' => $chatId,
                    'parts_count' => count($parts),
                    'all_parts' => $parts,
                ]);

                if ($token && $chatId) {
                    $verification = $this->verificationService->completeVerificationStart($token, (string) $chatId);

                    if ($verification) {
                        // Кнопка отправляется внутри completeVerificationStart через sendCode
                        // Дополнительно отправляем приветственное сообщение с кнопкой для надежности
                        $responseText = "👋 Добро пожаловать!\n\nДля подтверждения заказа необходимо поделиться номером телефона. Нажмите кнопку ниже.";
                        \Log::info('Verification started successfully', [
                            'verification_id' => $verification->id,
                            'order_id' => $verification->order_id,
                            'chat_id' => $chatId,
                        ]);

                        // Отправляем сообщение с кнопкой (дублируем для надежности)
                        $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                            'chat_id' => $chatId,
                            'text' => $responseText,
                            'reply_markup' => [
                                'keyboard' => [
                                    [
                                        [
                                            'text' => '📱 Поделиться номером',
                                            'request_contact' => true,
                                        ],
                                    ],
                                ],
                                'one_time_keyboard' => true,
                                'resize_keyboard' => true,
                            ],
                        ]);

                        if (! $response->successful()) {
                            \Log::error('Failed to send Telegram message with button', [
                                'status' => $response->status(),
                                'body' => $response->body(),
                                'chat_id' => $chatId,
                            ]);
                        } else {
                            \Log::info('Telegram message with button sent successfully', [
                                'chat_id' => $chatId,
                                'verification_id' => $verification->id,
                            ]);
                        }
                    } else {
                        $responseText = '❌ Ошибка: токен верификации недействителен или истек. Пожалуйста, попробуйте снова на сайте.';
                        \Log::warning('Verification failed', [
                            'token' => $token,
                            'token_length' => strlen($token),
                            'chat_id' => $chatId,
                        ]);

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
