<?php

namespace Database\Seeders;

use App\Models\ConstructorCategory;
use App\Models\ConstructorProduct;
use Illuminate\Database\Seeder;

class ConstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Гарнир, крупа
        $category1 = ConstructorCategory::create([
            'name' => 'Гарнир, крупа',
            'sort_order' => 1,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category1->id,
            'name' => 'Картофель запеченный',
            'price' => 50.00,
            'sort_order' => 1,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category1->id,
            'name' => 'Гречка',
            'price' => 45.00,
            'sort_order' => 2,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category1->id,
            'name' => 'Рис бурый',
            'price' => 40.00,
            'sort_order' => 3,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category1->id,
            'name' => 'Киноа',
            'price' => 55.00,
            'sort_order' => 4,
        ]);

        // 2. Мясо, рыба, птица
        $category2 = ConstructorCategory::create([
            'name' => 'Мясо, рыба, птица',
            'sort_order' => 2,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category2->id,
            'name' => 'Говядина (сирлоин)',
            'price' => 200.00,
            'sort_order' => 1,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category2->id,
            'name' => 'Куриное филе',
            'price' => 150.00,
            'sort_order' => 2,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category2->id,
            'name' => 'Лосось',
            'price' => 250.00,
            'sort_order' => 3,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category2->id,
            'name' => 'Индейка',
            'price' => 160.00,
            'sort_order' => 4,
        ]);

        // 3. Овощи
        $category3 = ConstructorCategory::create([
            'name' => 'Овощи',
            'sort_order' => 3,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category3->id,
            'name' => 'Огурец',
            'price' => 30.00,
            'sort_order' => 1,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category3->id,
            'name' => 'Помидор',
            'price' => 35.00,
            'sort_order' => 2,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category3->id,
            'name' => 'Перец болгарский',
            'price' => 40.00,
            'sort_order' => 3,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category3->id,
            'name' => 'Брокколи',
            'price' => 45.00,
            'sort_order' => 4,
        ]);

        // 4. Зелень
        $category4 = ConstructorCategory::create([
            'name' => 'Зелень',
            'sort_order' => 4,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category4->id,
            'name' => 'Укроп',
            'price' => 20.00,
            'sort_order' => 1,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category4->id,
            'name' => 'Петрушка',
            'price' => 20.00,
            'sort_order' => 2,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category4->id,
            'name' => 'Базилик',
            'price' => 25.00,
            'sort_order' => 3,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category4->id,
            'name' => 'Кинза',
            'price' => 22.00,
            'sort_order' => 4,
        ]);

        // 5. Соусы
        $category5 = ConstructorCategory::create([
            'name' => 'Соусы',
            'sort_order' => 5,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category5->id,
            'name' => 'Ткемали',
            'price' => 50.00,
            'sort_order' => 1,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category5->id,
            'name' => 'Тартар',
            'price' => 45.00,
            'sort_order' => 2,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category5->id,
            'name' => 'Сырный соус',
            'price' => 55.00,
            'sort_order' => 3,
        ]);

        ConstructorProduct::create([
            'constructor_category_id' => $category5->id,
            'name' => 'Оливковое масло',
            'price' => 40.00,
            'sort_order' => 4,
        ]);
    }
}
