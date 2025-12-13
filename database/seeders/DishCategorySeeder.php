<?php

namespace Database\Seeders;

use App\Models\DishCategory;
use Illuminate\Database\Seeder;

class DishCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Основные блюда',
            'Салаты',
            'Десерты',
            'Супы',
        ];

        foreach ($categories as $category) {
            DishCategory::firstOrCreate(['name' => $category]);
        }
    }
}
