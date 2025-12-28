<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'status',
        'total',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function phoneVerification(): HasOne
    {
        return $this->hasOne(PhoneVerification::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPendingVerification(): bool
    {
        return $this->status === 'pending_verification';
    }
}
