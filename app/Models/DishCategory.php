<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DishCategory extends Model
{
    /** @use HasFactory<\Database\Factories\DishCategoryFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }
}
