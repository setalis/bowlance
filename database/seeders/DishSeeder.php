<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\DishCategory;
use Illuminate\Database\Seeder;

class DishSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainDishesCategory = DishCategory::where('name', 'Основные блюда')->first();
        $saladsCategory = DishCategory::where('name', 'Салаты')->first();

        $dishes = [
            // Основные блюда
            [
                'name' => 'Боул Power',
                'description' => 'Крупа гречневая, куриное филе, перец болгарский (запечённый), картофель (запеченный) брокколи.',
                'price' => 0,
                'dish_category_id' => $mainDishesCategory->id,
                'weight_volume' => '450 г',
                'calories' => 525,
                'proteins' => 46.00,
                'fats' => 18.00,
                'carbohydrates' => 46.00,
                'fiber' => 8.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Боул с лососем пашот',
                'description' => 'Боул с лососем пашот, диким и цельнозерновым рисом. Основное блюдо. Выход - 350г без соуса.',
                'price' => 0,
                'dish_category_id' => $mainDishesCategory->id,
                'weight_volume' => '350 г',
                'calories' => 458,
                'proteins' => 42.00,
                'fats' => 21.00,
                'carbohydrates' => 35.00,
                'fiber' => 7.00,
                'sort_order' => 2,
            ],
            [
                'name' => 'Боул с говядиной, бататом и красной чечевицей',
                'description' => 'Боул с говядиной, бататом и красной чечевицей. Основное блюдо. Выход - 440г без соуса.',
                'price' => 0,
                'dish_category_id' => $mainDishesCategory->id,
                'weight_volume' => '440 г',
                'calories' => 495,
                'proteins' => 38.00,
                'fats' => 17.00,
                'carbohydrates' => 46.00,
                'fiber' => 10.00,
                'sort_order' => 3,
            ],
            [
                'name' => 'Боул с мозаикой из тофу и огурца',
                'description' => 'Боул с мозаикой из тофу и огурца, креветками и цельнозерновым рисом. Основное блюдо. Выход — 400г.',
                'price' => 0,
                'dish_category_id' => $mainDishesCategory->id,
                'weight_volume' => '400 г',
                'calories' => 465,
                'proteins' => 34.00,
                'fats' => 18.00,
                'carbohydrates' => 46.00,
                'fiber' => 7.00,
                'sort_order' => 4,
            ],
            [
                'name' => 'Боул с куриным бедром сувлаки',
                'description' => 'Боул с куриным бедром сувлаки, запеченным картофелем, брокколи, цветной капустой и овощами. Основное блюдо. Выход - 370г без соуса.',
                'price' => 0,
                'dish_category_id' => $mainDishesCategory->id,
                'weight_volume' => '370 г',
                'calories' => 470,
                'proteins' => 33.00,
                'fats' => 15.00,
                'carbohydrates' => 44.00,
                'fiber' => 7.00,
                'sort_order' => 5,
            ],
            // Салаты
            [
                'name' => 'Салат с курицей в лимонном соусе',
                'description' => 'Салат с курицей в лимонном соусе, мягким творогом и зеленью. Салаты. Выход — 315 г',
                'price' => 0,
                'dish_category_id' => $saladsCategory->id,
                'weight_volume' => '315 г',
                'calories' => 275,
                'proteins' => 33.00,
                'fats' => 12.00,
                'carbohydrates' => 8.50,
                'fiber' => 4.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Салат с тунцом татаки',
                'description' => 'Салат с тунцом татаки, маринованным редисом, авокадо, кейлом и т.д. Салаты. Выход - 330г.',
                'price' => 0,
                'dish_category_id' => $saladsCategory->id,
                'weight_volume' => '330 г',
                'calories' => 282,
                'proteins' => 33.00,
                'fats' => 10.00,
                'carbohydrates' => 16.00,
                'fiber' => 7.00,
                'sort_order' => 2,
            ],
        ];

        foreach ($dishes as $dish) {
            Dish::firstOrCreate(
                ['name' => $dish['name']],
                $dish
            );
        }
    }
}
