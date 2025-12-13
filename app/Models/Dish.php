<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dish extends Model
{
    /** @use HasFactory<\Database\Factories\DishFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'dish_category_id',
        'image',
        'weight_volume',
        'calories',
        'proteins',
        'fats',
        'carbohydrates',
        'fiber',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'proteins' => 'decimal:2',
            'fats' => 'decimal:2',
            'carbohydrates' => 'decimal:2',
            'fiber' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DishCategory::class, 'dish_category_id');
    }
}
