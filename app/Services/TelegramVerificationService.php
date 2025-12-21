<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramVerificationService
{
    public function __construct() {}

    public function sendCode(string $phone, string $chatId, string $code): bool
    {
        $botToken = config('verification.telegram.bot_token');

        if (empty($botToken)) {
            Log::error('Telegram bot token is not configured');

            return false;
        }

        $message = "Для подтверждения заказа необходимо поделиться номером телефона.\n\nНажмите кнопку ниже, чтобы поделиться номером из Telegram.";

        try {
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
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

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to send Telegram message', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while sending Telegram message', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendVerificationCode(string $phone, string $chatId, string $code): bool
    {
        $botToken = config('verification.telegram.bot_token');

        if (empty($botToken)) {
            Log::error('Telegram bot token is not configured');

            return false;
        }

        $message = "✅ Номер подтвержден!\n\nВаш код подтверждения для заказа: <b>{$code}</b>\n\nТелефон: {$phone}\n\nКод действителен в течение 10 минут.\n\nВведите этот код на сайте для завершения подтверждения заказа.";

        try {
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to send Telegram message', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while sending Telegram message', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendPhoneVerifiedSuccess(string $chatId, string $phone, ?string $returnUrl = null): bool
    {
        $botToken = config('verification.telegram.bot_token');

        if (empty($botToken)) {
            Log::error('Telegram bot token is not configured');

            return false;
        }

        $appUrl = $returnUrl ?? config('app.url');
        $message = "✅ <b>Номер успешно подтвержден!</b>\n\nТелефон: {$phone}\n\nВаш заказ подтвержден и принят в обработку. Нажмите кнопку ниже, чтобы вернуться на сайт.";

        $replyMarkup = null;
        if ($appUrl) {
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🔙 Вернуться на сайт',
                            'url' => $appUrl,
                        ],
                    ],
                ],
            ];
        }

        try {
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'reply_markup' => $replyMarkup,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to send Telegram success message', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while sending Telegram success message', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
