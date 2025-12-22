<?php

namespace Database\Factories;

use App\Models\ConstructorCategory;
use App\Models\ConstructorProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConstructorProduct>
 */
class ConstructorProductFactory extends Factory
{
    protected $model = ConstructorProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'constructor_category_id' => ConstructorCategory::factory(),
            'name' => fake()->words(2, true),
            'price' => fake()->randomFloat(2, 20, 300),
            'image' => fake()->optional()->imageUrl(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
