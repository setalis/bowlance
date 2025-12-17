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
        $expiresMinutes = (int) config('verification.code_expires_minutes', 10) ?: 10;
        $expiresAt = Carbon::now()->addMinutes((int) $expiresMinutes);

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
        $verification = PhoneVerification::byToken($token)
            ->whereNull('telegram_chat_id')
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            return null;
        }

        $code = PhoneVerification::generateCode();
        $verification->update([
            'telegram_chat_id' => $chatId,
            'code' => $code,
        ]);

        $this->telegramService->sendCode($verification->phone, $chatId, $code);

        return $verification;
    }

    public function initiateVerification(Order $order, string $phone, string $chatId): PhoneVerification
    {
        $code = PhoneVerification::generateCode();
        $expiresMinutes = (int) config('verification.code_expires_minutes', 10) ?: 10;
        $expiresAt = Carbon::now()->addMinutes((int) $expiresMinutes);

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
