<?php

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\IconPreviewController;
use App\Http\Controllers\PhoneVerificationController;
use App\Models\DishCategory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('pages.frontend.home', [
        'title' => 'Главная',
        'categories' => DishCategory::withCount('dishes')->orderBy('sort_order')->orderBy('name')->get(),
    ]);
})->name('home');

Route::get('/api/categories/{category}/dishes', function (DishCategory $category) {
    $dishes = $category->dishes()
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return response()->json([
        'category' => [
            'id' => $category->id,
            'name' => $category->name,
            'image' => $category->image ? Storage::url($category->image) : null,
        ],
        'dishes' => $dishes->map(function ($dish) {
            return [
                'id' => $dish->id,
                'name' => $dish->name,
                'description' => $dish->description,
                'price' => $dish->price,
                'image' => $dish->image ? Storage::url($dish->image) : null,
                'weight_volume' => $dish->weight_volume,
                'calories' => $dish->calories,
                'proteins' => $dish->proteins,
                'fats' => $dish->fats,
                'carbohydrates' => $dish->carbohydrates,
                'fiber' => $dish->fiber,
            ];
        }),
    ]);
})->name('api.categories.dishes');

Route::post('/api/orders', [OrderController::class, 'store'])->name('api.orders.store');

Route::post('/api/phone/verification/start', [PhoneVerificationController::class, 'start'])->name('api.phone.verification.start');
Route::post('/api/phone/verification/send', [PhoneVerificationController::class, 'sendCode'])->name('api.phone.verification.send');
Route::post('/api/phone/verification/verify', [PhoneVerificationController::class, 'verifyCode'])->name('api.phone.verification.verify');
Route::get('/api/phone/verification/check-status', [PhoneVerificationController::class, 'checkStatus'])->name('api.phone.verification.check-status');
Route::post('/api/telegram/webhook', [App\Http\Controllers\TelegramWebhookController::class, 'handle'])->name('api.telegram.webhook');

Route::get('/dashboard', function () {
    return view('dashboard', ['title' => 'Dashboard']);
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Route::resource('restaurants', RestaurantController::class);
    Route::get('/icons', [IconPreviewController::class, 'index'])->name('icons.preview');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
