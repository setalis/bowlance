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
        $update = $request->all();
        $botToken = config('verification.telegram.bot_token');

        if (! isset($update['message'])) {
            return response()->json(['ok' => true]);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';

        // Обработка команды /start с токеном
        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text);
            $token = $parts[1] ?? null;

            if ($token && $chatId) {
                $verification = $this->verificationService->completeVerificationStart($token, (string) $chatId);

                if ($verification) {
                    $responseText = "✅ Код подтверждения отправлен!\n\nПроверьте сообщение с кодом и введите его на сайте для подтверждения заказа.";
                } else {
                    $responseText = '❌ Ошибка: токен верификации недействителен или истек. Пожалуйста, попробуйте снова на сайте.';
                }

                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $responseText,
                ]);
            } else {
                // Если команда /start без токена
                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => "👋 Добро пожаловать!\n\nДля подтверждения заказа перейдите на сайт и нажмите кнопку 'Подтвердить через Telegram'.",
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }
}
