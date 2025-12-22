<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'dish_id',
        'dish_name',
        'price',
        'quantity',
        'constructor_data',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'constructor_data' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function isConstructor(): bool
    {
        return $this->constructor_data !== null && isset($this->constructor_data['type']) && $this->constructor_data['type'] === 'constructor';
    }
}
