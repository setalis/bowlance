<?php

namespace App\Models;

use Database\Factories\ConstructorCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConstructorCategory extends Model
{
    use HasFactory;

    protected static function newFactory(): ConstructorCategoryFactory
    {
        return ConstructorCategoryFactory::new();
    }

    protected $fillable = [
        'name',
        'sort_order',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(ConstructorProduct::class)->orderBy('sort_order');
    }
}
