<?php

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\User;

test('guests are redirected to login when accessing dishes index', function () {
    $this->get(route('admin.dishes.index'))->assertRedirect('/login');
});

test('authenticated users can view dishes index', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.dishes.index'))->assertOk();
});

test('authenticated users can view create dish form', function () {
    $this->actingAs(User::factory()->create());
    DishCategory::factory()->create();

    $this->get(route('admin.dishes.create'))->assertOk();
});

test('authenticated users can create a dish', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();

    $response = $this->post(route('admin.dishes.store'), [
        'name' => 'Борщ',
        'description' => 'Традиционный украинский суп',
        'price' => 350.00,
        'dish_category_id' => $category->id,
    ]);

    $response->assertRedirect(route('admin.dishes.index'));
    $this->assertDatabaseHas('dishes', [
        'name' => 'Борщ',
        'description' => 'Традиционный украинский суп',
        'price' => 350.00,
        'dish_category_id' => $category->id,
    ]);
});

test('dish name is required', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();

    $response = $this->post(route('admin.dishes.store'), [
        'price' => 350.00,
        'dish_category_id' => $category->id,
    ]);

    $response->assertSessionHasErrors('name');
});

test('dish price is required', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();

    $response = $this->post(route('admin.dishes.store'), [
        'name' => 'Борщ',
        'dish_category_id' => $category->id,
    ]);

    $response->assertSessionHasErrors('price');
});

test('dish price must be numeric', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();

    $response = $this->post(route('admin.dishes.store'), [
        'name' => 'Борщ',
        'price' => 'не число',
        'dish_category_id' => $category->id,
    ]);

    $response->assertSessionHasErrors('price');
});

test('dish price must be positive', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();

    $response = $this->post(route('admin.dishes.store'), [
        'name' => 'Борщ',
        'price' => -100,
        'dish_category_id' => $category->id,
    ]);

    $response->assertSessionHasErrors('price');
});

test('dish category is required', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('admin.dishes.store'), [
        'name' => 'Борщ',
        'price' => 350.00,
    ]);

    $response->assertSessionHasErrors('dish_category_id');
});

test('dish category must exist', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('admin.dishes.store'), [
        'name' => 'Борщ',
        'price' => 350.00,
        'dish_category_id' => 999,
    ]);

    $response->assertSessionHasErrors('dish_category_id');
});

test('authenticated users can view edit dish form', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();
    $dish = Dish::factory()->create(['dish_category_id' => $category->id]);

    $this->get(route('admin.dishes.edit', $dish))->assertOk();
});

test('authenticated users can update a dish', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();
    $dish = Dish::factory()->create(['dish_category_id' => $category->id]);

    $response = $this->put(route('admin.dishes.update', $dish), [
        'name' => 'Обновленный борщ',
        'description' => 'Новое описание',
        'price' => 400.00,
        'dish_category_id' => $category->id,
    ]);

    $response->assertRedirect(route('admin.dishes.index'));
    $this->assertDatabaseHas('dishes', [
        'id' => $dish->id,
        'name' => 'Обновленный борщ',
        'price' => 400.00,
    ]);
});

test('authenticated users can delete a dish', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();
    $dish = Dish::factory()->create(['dish_category_id' => $category->id]);

    $response = $this->delete(route('admin.dishes.destroy', $dish));

    $response->assertRedirect(route('admin.dishes.index'));
    $this->assertDatabaseMissing('dishes', ['id' => $dish->id]);
});
