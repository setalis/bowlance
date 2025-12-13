<?php

namespace Database\Factories;

use App\Models\DishCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dish>
 */
class DishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 100, 5000),
            'dish_category_id' => DishCategory::factory(),
            'weight_volume' => fake()->optional()->randomElement(['300 г', '500 г', '250 мл', '500 мл', '1 порция']),
            'calories' => fake()->optional()->numberBetween(100, 1000),
            'proteins' => fake()->optional()->randomFloat(2, 5, 50),
            'fats' => fake()->optional()->randomFloat(2, 5, 50),
            'carbohydrates' => fake()->optional()->randomFloat(2, 10, 100),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
