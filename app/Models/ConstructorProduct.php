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
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ConstructorCategory::class);
    }
}
