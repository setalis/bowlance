<?php

use App\Http\Controllers\Admin\DishCategoryController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Admin\RestaurantController;
use Illuminate\Support\Facades\Route;
 
Route::group([
    'prefix'     => 'admin',
    'as'         => 'admin.',
    'middleware' => ['auth'],
], function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('index');
    
    Route::resource('/restaurants', RestaurantController::class);
    Route::resource('/dish-categories', DishCategoryController::class);
    Route::resource('/dishes', DishController::class);
});