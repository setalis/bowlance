<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\IconPreviewController;
use App\Http\Controllers\PhoneVerificationController;
use App\Models\ConstructorCategory;
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

Route::get('/api/constructor/categories', function () {
    $categories = ConstructorCategory::with('products')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return response()->json([
        'categories' => $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'sort_order' => $category->sort_order,
                'products' => $category->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image' => $product->image ? Storage::url($product->image) : null,
                        'sort_order' => $product->sort_order,
                        'description' => $product->description,
                        'weight_volume' => $product->weight_volume,
                        'calories' => $product->calories,
                        'proteins' => $product->proteins,
                        'fats' => $product->fats,
                        'carbohydrates' => $product->carbohydrates,
                        'fiber' => $product->fiber,
                    ];
                }),
            ];
        }),
    ]);
})->name('api.constructor.categories');

Route::get('/api/csrf-token', function (\Illuminate\Http\Request $request) {
    // Если сессия истекла или отсутствует, создаем новую
    if (! $request->hasSession() || ! $request->session()->has('_token')) {
        $request->session()->regenerate();
    }

    return response()->json(['token' => csrf_token()]);
})->name('api.csrf-token');

Route::post('/api/orders', [OrderController::class, 'store'])->name('api.orders.store');

Route::post('/api/phone/verification/start', [PhoneVerificationController::class, 'start'])->name('api.phone.verification.start');
Route::post('/api/phone/verification/send', [PhoneVerificationController::class, 'sendCode'])->name('api.phone.verification.send');
Route::post('/api/phone/verification/verify', [PhoneVerificationController::class, 'verifyCode'])->name('api.phone.verification.verify');
Route::get('/api/phone/verification/check-status', [PhoneVerificationController::class, 'checkStatus'])->name('api.phone.verification.check-status');
Route::post('/api/telegram/webhook', [App\Http\Controllers\TelegramWebhookController::class, 'handle'])->name('api.telegram.webhook');

Route::post('/api/login/verification/send', [App\Http\Controllers\Auth\LoginVerificationController::class, 'sendCode'])->name('api.login.verification.send');
Route::post('/api/login/verification/verify', [App\Http\Controllers\Auth\LoginVerificationController::class, 'verifyCode'])->name('api.login.verification.verify');

Route::get('/dashboard', function () {
    return view('dashboard', ['title' => 'Dashboard']);
})->middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
});

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    // Route::resource('restaurants', RestaurantController::class);
    Route::get('/icons', [IconPreviewController::class, 'index'])->name('icons.preview');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
