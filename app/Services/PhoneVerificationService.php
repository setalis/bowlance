<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PhoneVerification;
use Carbon\Carbon;

class PhoneVerificationService
{
    public function __construct(
        private readonly TelegramVerificationService $telegramService
    ) {}

    public function startVerification(Order $order, string $phone): PhoneVerification
    {
        $token = PhoneVerification::generateToken();
        $expiresMinutes = max(1, (int) config('verification.code_expires_minutes', 10));
        $expiresAt = Carbon::now()->addMinutes($expiresMinutes);

        $verification = PhoneVerification::create([
            'order_id' => $order->id,
            'phone' => $phone,
            'verification_token' => $token,
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ]);

        return $verification;
    }

    public function completeVerificationStart(string $token, string $chatId): ?PhoneVerification
    {
        // Очищаем токен от возможных пробелов и спецсимволов
        $token = trim($token);
        $originalToken = $token;

        // Ищем верификацию по точному совпадению токена
        $verification = PhoneVerification::byToken($token)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        // Если не найдено по точному совпадению, пробуем найти по началу токена
        // (на случай, если Telegram обрезал токен)
        if (! $verification && strlen($token) >= 32) {
            $tokenPrefix = substr($token, 0, 32);
            $verification = PhoneVerification::where('verification_token', 'LIKE', $tokenPrefix.'%')
                ->whereNull('verified_at')
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($verification) {
                \Log::info('Verification found by token prefix', [
                    'original_token' => $originalToken,
                    'token_prefix' => $tokenPrefix,
                    'found_token' => $verification->verification_token,
                    'verification_id' => $verification->id,
                ]);
            }
        }

        if (! $verification) {
            // Проверяем, существует ли токен вообще
            $tokenRecord = PhoneVerification::byToken($originalToken)->first();

            if ($tokenRecord) {
                // Токен существует, но не прошел проверки
                $isExpired = $tokenRecord->expires_at->isPast();
                $isVerified = $tokenRecord->verified_at !== null;
                $expiresIn = $tokenRecord->expires_at->diffInMinutes(now());

                \Log::warning('Verification token found but invalid', [
                    'token' => $originalToken,
                    'token_length' => strlen($originalToken),
                    'chat_id' => $chatId,
                    'verification_id' => $tokenRecord->id,
                    'order_id' => $tokenRecord->order_id,
                    'is_expired' => $isExpired,
                    'is_verified' => $isVerified,
                    'expires_at' => $tokenRecord->expires_at->toDateTimeString(),
                    'now' => now()->toDateTimeString(),
                    'expires_in_minutes' => $expiresIn,
                    'telegram_chat_id' => $tokenRecord->telegram_chat_id,
                ]);
            } else {
                // Токен не существует в базе
                \Log::warning('Verification token not found in database', [
                    'token' => $originalToken,
                    'token_length' => strlen($originalToken),
                    'chat_id' => $chatId,
                ]);
            }

            return null;
        }

        // Обновляем telegram_chat_id если его еще нет, или если это тот же чат
        if ($verification->telegram_chat_id === null || $verification->telegram_chat_id === $chatId) {
            $verification->update([
                'telegram_chat_id' => $chatId,
            ]);

            // Отправляем кнопку "поделиться номером" только если номер еще не проверен
            if ($verification->telegram_phone === null) {
                $this->telegramService->sendCode($verification->phone, $chatId, '');
            }

            return $verification;
        }

        // Если токен используется другим чатом, возвращаем null
        \Log::warning('Token used by different chat', [
            'token' => $token,
            'expected_chat_id' => $verification->telegram_chat_id,
            'received_chat_id' => $chatId,
        ]);

        return null;
    }

    public function verifyPhoneNumber(string $token, string $chatId, string $telegramPhone): array
    {
        $verification = PhoneVerification::byToken($token)
            ->where('telegram_chat_id', $chatId)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            return [
                'success' => false,
                'message' => 'Токен верификации недействителен или истек.',
            ];
        }

        // Нормализуем номера для сравнения (убираем все кроме цифр)
        $normalizedPhone = preg_replace('/[^0-9]/', '', $verification->phone);
        $normalizedTelegramPhone = preg_replace('/[^0-9]/', '', $telegramPhone);

        // Проверяем совпадение (учитываем возможные варианты с +7 и 8)
        $phoneMatches = $this->comparePhones($normalizedPhone, $normalizedTelegramPhone);

        if (! $phoneMatches) {
            return [
                'success' => false,
                'message' => 'Введенный номер на сайте не совпадает с номером Telegram. Пожалуйста, используйте тот же номер, который вы указали при оформлении заказа.',
            ];
        }

        // Номера совпадают - генерируем код и отправляем
        $code = PhoneVerification::generateCode();
        $verification->update([
            'telegram_phone' => $telegramPhone,
            'code' => $code,
        ]);

        $this->telegramService->sendVerificationCode($verification->phone, $chatId, $code);

        return [
            'success' => true,
            'message' => 'Номер подтвержден! Код подтверждения отправлен.',
        ];
    }

    private function comparePhones(string $phone1, string $phone2): bool
    {
        // Убираем префиксы +7, 7, 8 в начале
        $phone1 = preg_replace('/^(\+?7|8)/', '', $phone1);
        $phone2 = preg_replace('/^(\+?7|8)/', '', $phone2);

        // Сравниваем последние 10 цифр
        $phone1 = substr($phone1, -10);
        $phone2 = substr($phone2, -10);

        return $phone1 === $phone2;
    }

    public function initiateVerification(Order $order, string $phone, string $chatId): PhoneVerification
    {
        $code = PhoneVerification::generateCode();
        $expiresMinutes = max(1, (int) config('verification.code_expires_minutes', 10));
        $expiresAt = Carbon::now()->addMinutes($expiresMinutes);

        $verification = PhoneVerification::create([
            'order_id' => $order->id,
            'phone' => $phone,
            'code' => $code,
            'telegram_chat_id' => $chatId,
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ]);

        $this->telegramService->sendCode($phone, $chatId, $code);

        return $verification;
    }

    public function verifyCode(Order $order, string $code): bool
    {
        $verification = PhoneVerification::forOrder($order->id)
            ->active()
            ->latest()
            ->first();

        if (! $verification) {
            return false;
        }

        if ($verification->isExpired()) {
            return false;
        }

        if ($verification->hasExceededAttempts()) {
            return false;
        }

        if (! $verification->verifyCode($code)) {
            $verification->incrementAttempts();

            return false;
        }

        $verification->markAsVerified();
        $order->update(['status' => 'new']);

        return true;
    }
}
