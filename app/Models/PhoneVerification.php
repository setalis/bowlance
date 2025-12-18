<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class PhoneVerification extends Model
{
    protected $fillable = [
        'order_id',
        'phone',
        'telegram_phone',
        'code',
        'telegram_chat_id',
        'verification_token',
        'verified_at',
        'expires_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeActive(Builder $query): Builder
    {
        $maxAttempts = (int) config('verification.max_attempts', 3);

        return $query->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->where('attempts', '<', $maxAttempts);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function hasExceededAttempts(): bool
    {
        $maxAttempts = (int) config('verification.max_attempts', 3);

        return $this->attempts >= $maxAttempts;
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    public static function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function scopeByToken(Builder $query, string $token): Builder
    {
        return $query->where('verification_token', $token);
    }

    public function setCodeAttribute(?string $code): void
    {
        if ($code !== null) {
            $this->attributes['code'] = Hash::make($code);
        } else {
            $this->attributes['code'] = null;
        }
    }

    public function verifyCode(string $code): bool
    {
        $hashedCode = $this->getOriginal('code') ?? $this->attributes['code'] ?? $this->code;

        return Hash::check($code, $hashedCode);
    }
}
