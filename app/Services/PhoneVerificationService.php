<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PhoneVerification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PhoneVerificationService
{
    public function __construct(
        private readonly TelegramVerificationService $telegramService
    ) {}

    public function startVerification(Order $order, string $phone): PhoneVerification
    {
        // Помечаем все старые неверифицированные верификации для этого заказа как истекшие
        // чтобы они не конфликтовали с новой верификацией
        PhoneVerification::where('order_id', $order->id)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => Carbon::now()->subMinute()]);

        $token = PhoneVerification::generateToken();
        $expiresMinutes = max(1, (int) config('verification.code_expires_minutes', 10));
        $expiresAt = Carbon::now()->addMinutes($expiresMinutes);

        \Log::info('Creating phone verification', [
            'order_id' => $order->id,
            'phone' => $phone,
            'token' => $token,
            'token_length' => strlen($token),
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);

        $verification = PhoneVerification::create([
            'order_id' => $order->id,
            'phone' => $phone,
            'verification_token' => $token,
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ]);

        \Log::info('Phone verification created successfully', [
            'verification_id' => $verification->id,
            'order_id' => $verification->order_id,
            'token' => $verification->verification_token,
            'token_length' => strlen($verification->verification_token),
        ]);

        return $verification;
    }

    public function completeVerificationStart(string $token, string $chatId): ?PhoneVerification
    {
        // Очищаем токен от возможных пробелов и спецсимволов
        $token = trim($token);
        $originalToken = $token;
        $tokenLength = strlen($token);

        \Log::info('Searching for verification token', [
            'token' => $originalToken,
            'token_length' => $tokenLength,
            'chat_id' => $chatId,
        ]);

        // Ищем верификацию по точному совпадению токена
        $verification = PhoneVerification::byToken($token)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($verification) {
            \Log::info('Verification found by exact match', [
                'verification_id' => $verification->id,
                'order_id' => $verification->order_id,
                'token' => $verification->verification_token,
            ]);
        }

        // Если не найдено по точному совпадению, пробуем найти по началу токена
        // (на случай, если Telegram обрезал токен) - только если токен длиной >= 16 символов
        if (! $verification && $tokenLength >= 16) {
            $tokenPrefix = substr($token, 0, 16);
            \Log::info('Trying to find by prefix', [
                'prefix' => $tokenPrefix,
                'prefix_length' => strlen($tokenPrefix),
            ]);

            $verification = PhoneVerification::where('verification_token', 'LIKE', $tokenPrefix.'%')
                ->whereNull('verified_at')
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($verification) {
                \Log::info('Verification found by prefix', [
                    'verification_id' => $verification->id,
                    'order_id' => $verification->order_id,
                    'found_token' => $verification->verification_token,
                    'found_token_length' => strlen($verification->verification_token),
                ]);
            }
        }

        if (! $verification) {
            // Проверяем, существует ли токен вообще (без проверки срока действия)
            $tokenRecord = PhoneVerification::byToken($originalToken)->first();

            // Также проверяем активные верификации для этого заказа
            $recentVerifications = PhoneVerification::whereNull('verified_at')
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'order_id', 'verification_token', 'created_at', 'expires_at']);

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
                    'recent_verifications' => $recentVerifications->map(fn ($v) => [
                        'id' => $v->id,
                        'order_id' => $v->order_id,
                        'token_length' => strlen($v->verification_token ?? ''),
                        'token_start' => substr($v->verification_token ?? '', 0, 16),
                    ])->toArray(),
                ]);
            } else {
                // Токен не существует в базе
                \Log::warning('Verification token not found in database', [
                    'token' => $originalToken,
                    'token_length' => strlen($originalToken),
                    'chat_id' => $chatId,
                    'recent_verifications' => $recentVerifications->map(fn ($v) => [
                        'id' => $v->id,
                        'order_id' => $v->order_id,
                        'token_length' => strlen($v->verification_token ?? ''),
                        'token_start' => substr($v->verification_token ?? '', 0, 16),
                    ])->toArray(),
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
        // Используем прямой запрос к БД для гарантированного обновления статуса
        $orderId = $verification->order_id;
        $order = Order::find($orderId);

        if ($order) {
            $orderStatusBefore = $order->status;

            // Обновляем статус заказа, если он еще не подтвержден
            if ($order->status === 'pending_verification') {
                // Используем прямой SQL запрос для гарантированного обновления
                $updatedRows = DB::table('orders')
                    ->where('id', $orderId)
                    ->where('status', 'pending_verification')
                    ->update(['status' => 'new', 'updated_at' => now()]);

                // Перезагружаем заказ для проверки
                $order = Order::find($orderId);

                \Log::info('Order status updated to "new" after successful phone verification', [
                    'order_id' => $orderId,
                    'verification_id' => $verification->id,
                    'status_before' => $orderStatusBefore,
                    'status_after' => $order->status,
                    'updated_rows' => $updatedRows,
                ]);
            } else {
                \Log::warning('Order status was NOT updated - order status is not pending_verification', [
                    'order_id' => $orderId,
                    'order_status' => $order->status,
                    'verification_id' => $verification->id,
                ]);
            }
        } else {
            \Log::error('Order not found for verification', [
                'verification_id' => $verification->id,
                'order_id' => $orderId,
            ]);
        }

        // Отправляем подтверждение в Telegram с кнопкой для возврата на сайт
        // Добавляем параметр return=true и order_id для обработки возврата на ту же страницу
        $baseUrl = config('app.url');
        $returnUrl = $baseUrl.'?return=true&order_id='.$orderId;
        $this->telegramService->sendPhoneVerifiedSuccess($chatId, $verification->phone, $returnUrl, $orderId);

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

        // Загружаем заказ заново из базы данных для получения актуального статуса
        $orderId = $order->id;
        $order = Order::find($orderId);

        if ($order && $order->status === 'pending_verification') {
            $orderStatusBefore = $order->status;

            // Используем прямой SQL запрос для гарантированного обновления
            $updatedRows = DB::table('orders')
                ->where('id', $orderId)
                ->where('status', 'pending_verification')
                ->update(['status' => 'new', 'updated_at' => now()]);

            // Перезагружаем заказ для проверки
            $order = Order::find($orderId);

            \Log::info('Order status updated to "new" after successful code verification', [
                'order_id' => $orderId,
                'verification_id' => $verification->id,
                'status_before' => $orderStatusBefore,
                'status_after' => $order->status,
                'updated_rows' => $updatedRows,
            ]);
        } else {
            \Log::warning('Order status was NOT updated after code verification - order status is not pending_verification', [
                'order_id' => $orderId ?? 'unknown',
                'order_status' => $order->status ?? 'unknown',
                'verification_id' => $verification->id,
            ]);
        }

        return true;
    }
}
