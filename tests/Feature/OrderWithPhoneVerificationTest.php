<?php

use App\Models\Dish;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

test('order is created with pending_verification status', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $dish = Dish::factory()->create();

    $response = $this->postJson('/api/orders', [
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'customer_address' => 'Test Address',
        'items' => [
            [
                'dish_id' => $dish->id,
                'dish_name' => $dish->name,
                'price' => $dish->price,
                'quantity' => 1,
            ],
        ],
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);
    $response->assertJson(['requires_verification' => true]);

    $this->assertDatabaseHas('orders', [
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'status' => 'pending_verification',
    ]);
});

test('order requires verification before becoming active', function () {
    $order = Order::create([
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'customer_address' => 'Test Address',
        'status' => 'pending_verification',
        'total' => 100.00,
    ]);

    $this->assertTrue($order->isPendingVerification());
    $this->assertNotEquals('new', $order->status);
});
