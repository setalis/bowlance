<?php

use App\Http\Controllers\Admin\ConstructorCategoryController;
use App\Http\Controllers\Admin\ConstructorProductController;
use App\Http\Controllers\Admin\DishCategoryController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['auth', \App\Http\Middleware\EnsureUserIsAdmin::class],
], function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('index');

    Route::resource('/restaurants', RestaurantController::class);
    Route::resource('/dish-categories', DishCategoryController::class);
    Route::resource('/dishes', DishController::class);
    Route::resource('/orders', OrderController::class)->except(['create', 'show']);

    // Конструктор
    Route::resource('/constructor-categories', ConstructorCategoryController::class);
    Route::resource('/constructor-products', ConstructorProductController::class);
});
