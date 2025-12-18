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
            // Проверяем наличие колонки telegram_phone безопасно
            try {
                $telegramPhone = $verification->telegram_phone ?? null;
                if ($telegramPhone === null) {
                    $this->telegramService->sendCode($verification->phone, $chatId, '');
                }
            } catch (\Exception $e) {
                // Если колонка отсутствует, просто отправляем кнопку
                \Log::warning('Could not check telegram_phone, sending button anyway', [
                    'error' => $e->getMessage(),
                ]);
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

        // Проверяем совпадение (по полному совпадению или по последним 10 цифрам)
        $phoneMatches = $this->comparePhones($normalizedPhone, $normalizedTelegramPhone);

        if (! $phoneMatches) {
            return [
                'success' => false,
                'message' => 'Введенный номер на сайте не совпадает с номером Telegram. Пожалуйста, используйте тот же номер, который вы указали при оформлении заказа.',
            ];
        }

        // Номера совпадают — подтверждаем без кода
        try {
            $verification->update([
                'telegram_phone' => $telegramPhone,
                'verified_at' => now(),
                'code' => null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Если ошибка связана с отсутствием колонки, обновляем без telegram_phone
            if (str_contains($e->getMessage(), 'no such column: telegram_phone')) {
                \Log::error('telegram_phone column does not exist in database. Please run migration.', [
                    'migration' => '2025_12_18_161353_add_telegram_phone_to_phone_verifications_table',
                    'error' => $e->getMessage(),
                ]);

                // Обновляем без telegram_phone
                $verification->update([
                    'verified_at' => now(),
                    'code' => null,
                ]);
            } else {
                // Другая ошибка БД - пробрасываем дальше
                throw $e;
            }
        }

        // Обновляем статус заказа, если он ожидал верификации
        $order = $verification->order;
        if ($order && $order->status === 'pending_verification') {
            $order->update(['status' => 'new']);
        }

        // Отправляем подтверждение в Telegram
        $this->telegramService->sendPhoneVerifiedSuccess($chatId, $verification->phone);

        return [
            'success' => true,
            'message' => 'Номер подтвержден! Заказ подтвержден.',
        ];
    }

    private function comparePhones(string $phone1, string $phone2): bool
    {
        // Полное совпадение
        if ($phone1 === $phone2) {
            return true;
        }

        // Сравниваем последние 10 цифр (универсально для разных стран)
        $last10Phone1 = substr($phone1, -10);
        $last10Phone2 = substr($phone2, -10);

        if ($last10Phone1 === $last10Phone2 && strlen($last10Phone1) === 10) {
            return true;
        }

        // Если длины разные, но один номер является суффиксом другого
        if (str_ends_with($phone1, $phone2) || str_ends_with($phone2, $phone1)) {
            return true;
        }

        return false;
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
