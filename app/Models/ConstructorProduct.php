<?php

namespace App\Models;

use Database\Factories\ConstructorProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConstructorProduct extends Model
{
    use HasFactory;

    protected static function newFactory(): ConstructorProductFactory
    {
        return ConstructorProductFactory::new();
    }

    protected $fillable = [
        'constructor_category_id',
        'name',
        'price',
        'image',
        'sort_order',
        'description',
        'weight_volume',
        'calories',
        'proteins',
        'fats',
        'carbohydrates',
        'fiber',
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
        return $this->belongsTo(ConstructorCategory::class, 'constructor_category_id');
    }
}
