<?php

use App\Models\DishCategory;
use App\Models\User;

test('guests are redirected to login when accessing dish categories index', function () {
    $this->get(route('admin.dish-categories.index'))->assertRedirect('/login');
});

test('authenticated users can view dish categories index', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.dish-categories.index'))->assertOk();
});

test('authenticated users can view create dish category form', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.dish-categories.create'))->assertOk();
});

test('authenticated users can create a dish category', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('admin.dish-categories.store'), [
        'name' => 'Основные блюда',
    ]);

    $response->assertRedirect(route('admin.dish-categories.index'));
    $this->assertDatabaseHas('dish_categories', ['name' => 'Основные блюда']);
});

test('dish category name is required', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('admin.dish-categories.store'), []);

    $response->assertSessionHasErrors('name');
});

test('dish category name must be unique', function () {
    $this->actingAs(User::factory()->create());
    DishCategory::factory()->create(['name' => 'Салаты']);

    $response = $this->post(route('admin.dish-categories.store'), [
        'name' => 'Салаты',
    ]);

    $response->assertSessionHasErrors('name');
});

test('authenticated users can view edit dish category form', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();

    $this->get(route('admin.dish-categories.edit', $category))->assertOk();
});

test('authenticated users can update a dish category', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create(['name' => 'Старое название']);

    $response = $this->put(route('admin.dish-categories.update', $category), [
        'name' => 'Новое название',
    ]);

    $response->assertRedirect(route('admin.dish-categories.index'));
    $this->assertDatabaseHas('dish_categories', ['id' => $category->id, 'name' => 'Новое название']);
});

test('authenticated users can delete a dish category', function () {
    $this->actingAs(User::factory()->create());
    $category = DishCategory::factory()->create();

    $response = $this->delete(route('admin.dish-categories.destroy', $category));

    $response->assertRedirect(route('admin.dish-categories.index'));
    $this->assertDatabaseMissing('dish_categories', ['id' => $category->id]);
});
