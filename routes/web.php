<?php

use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\IconPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.frontend.home', ['title' => 'Главная']);
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard', ['title' => 'Dashboard']);
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Route::resource('restaurants', RestaurantController::class);
    Route::get('/icons', [IconPreviewController::class, 'index'])->name('icons.preview');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
