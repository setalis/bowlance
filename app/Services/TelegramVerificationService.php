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

        $message = "Ваш код подтверждения для заказа: {$code}\n\nТелефон: {$phone}\n\nКод действителен в течение 10 минут.";

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
}
