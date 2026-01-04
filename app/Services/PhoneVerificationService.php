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
        $tokenLength = strlen($token);

        \Log::info('Starting verification token search', [
            'received_token' => $originalToken,
            'token_length' => $tokenLength,
            'chat_id' => $chatId,
        ]);

        // Ищем верификацию по точному совпадению токена
        $verification = PhoneVerification::byToken($token)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        $searchMethod = 'exact_match';

        // Если не найдено по точному совпадению, пробуем найти по префиксу токена
        // (на случай, если Telegram обрезал токен)
        if (! $verification && $tokenLength >= 16) {
            // Пробуем разные длины префикса: 64, 48, 32, 24, 16 символов
            $prefixLengths = [64, 48, 32, 24, 16];

            foreach ($prefixLengths as $prefixLength) {
                if ($tokenLength >= $prefixLength) {
                    $tokenPrefix = substr($token, 0, $prefixLength);
                    $verification = PhoneVerification::where('verification_token', 'LIKE', $tokenPrefix.'%')
                        ->whereNull('verified_at')
                        ->where('expires_at', '>', now())
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($verification) {
                        $searchMethod = "prefix_{$prefixLength}_chars";
                        \Log::info('Verification found by token prefix', [
                            'original_token' => $originalToken,
                            'token_prefix' => $tokenPrefix,
                            'prefix_length' => $prefixLength,
                            'found_token' => $verification->verification_token,
                            'found_token_length' => strlen($verification->verification_token),
                            'verification_id' => $verification->id,
                            'search_method' => $searchMethod,
                        ]);
                        break;
                    }
                }
            }
        }

        // Если не найдено по префиксу, пробуем найти по последним символам токена
        // (на случай, если начало токена было обрезано)
        if (! $verification && $tokenLength >= 16) {
            $suffixLengths = [32, 24, 16];

            foreach ($suffixLengths as $suffixLength) {
                if ($tokenLength >= $suffixLength) {
                    $tokenSuffix = substr($token, -$suffixLength);
                    $verification = PhoneVerification::where('verification_token', 'LIKE', '%'.$tokenSuffix)
                        ->whereNull('verified_at')
                        ->where('expires_at', '>', now())
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($verification) {
                        $searchMethod = "suffix_{$suffixLength}_chars";
                        \Log::info('Verification found by token suffix', [
                            'original_token' => $originalToken,
                            'token_suffix' => $tokenSuffix,
                            'suffix_length' => $suffixLength,
                            'found_token' => $verification->verification_token,
                            'found_token_length' => strlen($verification->verification_token),
                            'verification_id' => $verification->id,
                            'search_method' => $searchMethod,
                        ]);
                        break;
                    }
                }
            }
        }

        // Если не найдено, пробуем найти по частичному совпадению в середине токена
        // (на случай, если обрезаны и начало, и конец)
        if (! $verification && $tokenLength >= 16) {
            // Берем среднюю часть токена
            $middleStart = (int) floor(($tokenLength - 16) / 2);
            $middlePart = substr($token, $middleStart, 16);

            if (strlen($middlePart) >= 16) {
                $verification = PhoneVerification::where('verification_token', 'LIKE', '%'.$middlePart.'%')
                    ->whereNull('verified_at')
                    ->where('expires_at', '>', now())
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($verification) {
                    $searchMethod = 'middle_part_16_chars';
                    \Log::info('Verification found by token middle part', [
                        'original_token' => $originalToken,
                        'token_middle_part' => $middlePart,
                        'found_token' => $verification->verification_token,
                        'found_token_length' => strlen($verification->verification_token),
                        'verification_id' => $verification->id,
                        'search_method' => $searchMethod,
                    ]);
                }
            }
        }

        if (! $verification) {
            // Проверяем, существует ли токен вообще (для диагностики)
            $tokenRecord = PhoneVerification::byToken($originalToken)->first();

            // Также проверяем активные верификации для этого чата
            $activeVerifications = PhoneVerification::where('telegram_chat_id', $chatId)
                ->whereNull('verified_at')
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'verification_token', 'created_at', 'expires_at', 'order_id']);

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
                    'active_verifications_for_chat' => $activeVerifications->map(fn ($v) => [
                        'id' => $v->id,
                        'token_length' => strlen($v->verification_token ?? ''),
                        'token_start' => substr($v->verification_token ?? '', 0, 16),
                        'created_at' => $v->created_at,
                    ])->toArray(),
                ]);
            } else {
                // Токен не существует в базе
                \Log::warning('Verification token not found in database', [
                    'token' => $originalToken,
                    'token_length' => strlen($originalToken),
                    'chat_id' => $chatId,
                    'active_verifications_for_chat' => $activeVerifications->map(fn ($v) => [
                        'id' => $v->id,
                        'token_length' => strlen($v->verification_token ?? ''),
                        'token_start' => substr($v->verification_token ?? '', 0, 16),
                        'created_at' => $v->created_at,
                    ])->toArray(),
                    'search_attempts' => [
                        'exact_match' => true,
                        'prefix_64' => $tokenLength >= 64,
                        'prefix_48' => $tokenLength >= 48,
                        'prefix_32' => $tokenLength >= 32,
                        'prefix_24' => $tokenLength >= 24,
                        'prefix_16' => $tokenLength >= 16,
                        'suffix_32' => $tokenLength >= 32,
                        'suffix_24' => $tokenLength >= 24,
                        'suffix_16' => $tokenLength >= 16,
                        'middle_part' => $tokenLength >= 16,
                    ],
                ]);
            }

            return null;
        }

        // Логируем успешный поиск
        \Log::info('Verification token found successfully', [
            'received_token' => $originalToken,
            'received_token_length' => $tokenLength,
            'found_token' => $verification->verification_token,
            'found_token_length' => strlen($verification->verification_token),
            'verification_id' => $verification->id,
            'order_id' => $verification->order_id,
            'search_method' => $searchMethod,
            'chat_id' => $chatId,
        ]);

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

        // Логируем номера для отладки
        \Log::info('Comparing phone numbers', [
            'order_phone' => $verification->phone,
            'order_phone_normalized' => $normalizedPhone,
            'telegram_phone' => $telegramPhone,
            'telegram_phone_normalized' => $normalizedTelegramPhone,
            'verification_id' => $verification->id,
            'order_id' => $verification->order_id,
        ]);

        // Проверяем совпадение (по полному совпадению или по последним 10 цифрам)
        $phoneMatches = $this->comparePhones($normalizedPhone, $normalizedTelegramPhone);

        if (! $phoneMatches) {
            \Log::warning('Phone numbers do not match - verification rejected', [
                'order_phone' => $verification->phone,
                'order_phone_normalized' => $normalizedPhone,
                'telegram_phone' => $telegramPhone,
                'telegram_phone_normalized' => $normalizedTelegramPhone,
                'verification_id' => $verification->id,
                'order_id' => $verification->order_id,
                'order_status' => $verification->order->status ?? 'unknown',
            ]);

            return [
                'success' => false,
                'message' => 'Введенный номер на сайте не совпадает с номером Telegram. Пожалуйста, используйте тот же номер, который вы указали при оформлении заказа.',
            ];
        }

        // Номера совпадают — подтверждаем без кода
        // ВАЖНО: Обновляем верификацию только если номера совпали
        try {
            $verification->update([
                'telegram_phone' => $telegramPhone,
                'verified_at' => now(),
                'code' => null,
            ]);

            \Log::info('Phone verification successful - updating order status', [
                'verification_id' => $verification->id,
                'order_id' => $verification->order_id,
                'order_status_before' => $verification->order->status ?? 'unknown',
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

                \Log::info('Phone verification successful (without telegram_phone column) - updating order status', [
                    'verification_id' => $verification->id,
                    'order_id' => $verification->order_id,
                    'order_status_before' => $verification->order->status ?? 'unknown',
                ]);
            } else {
                // Другая ошибка БД - пробрасываем дальше
                throw $e;
            }
        }

        // Обновляем статус заказа ТОЛЬКО если верификация прошла успешно
        // Это происходит только если номера совпали (код выше выполнился)
        $order = $verification->order;
        if ($order && $order->status === 'pending_verification') {
            $order->update(['status' => 'new']);
            \Log::info('Order status updated to "new" after successful phone verification', [
                'order_id' => $order->id,
                'verification_id' => $verification->id,
            ]);
        } else {
            \Log::warning('Order status was NOT updated - order status is not pending_verification', [
                'order_id' => $order->id ?? 'unknown',
                'order_status' => $order->status ?? 'unknown',
                'verification_id' => $verification->id,
            ]);
        }

        // Отправляем подтверждение в Telegram с кнопкой для возврата на сайт
        $returnUrl = config('app.url');
        $this->telegramService->sendPhoneVerifiedSuccess($chatId, $verification->phone, $returnUrl);

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

        // Если один из номеров пустой, не совпадают
        if (empty($phone1) || empty($phone2)) {
            return false;
        }

        // Сравниваем последние 10 цифр (универсально для разных стран)
        // Это работает для большинства международных форматов
        $last10Phone1 = substr($phone1, -10);
        $last10Phone2 = substr($phone2, -10);

        if ($last10Phone1 === $last10Phone2 && strlen($last10Phone1) === 10 && strlen($last10Phone2) === 10) {
            return true;
        }

        // Сравниваем последние 9 цифр (для некоторых форматов)
        if (strlen($phone1) >= 9 && strlen($phone2) >= 9) {
            $last9Phone1 = substr($phone1, -9);
            $last9Phone2 = substr($phone2, -9);
            if ($last9Phone1 === $last9Phone2 && strlen($last9Phone1) === 9 && strlen($last9Phone2) === 9) {
                return true;
            }
        }

        // Убираем проверку на суффикс - она слишком мягкая и может привести к ложным совпадениям
        // Вместо этого используем только точное совпадение или совпадение последних N цифр

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
