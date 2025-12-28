<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class LoginVerification extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'code',
        'login_token',
        'telegram_chat_id',
        'expires_at',
        'attempts',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        return $this->attempts >= 3;
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
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

    public static function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function scopeByLoginToken($query, string $token)
    {
        return $query->where('login_token', $token);
    }
}
