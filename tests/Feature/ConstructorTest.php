<?php

use App\Models\ConstructorCategory;
use App\Models\ConstructorProduct;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Http;

test('constructor categories api returns all categories with products', function () {
    $category = ConstructorCategory::factory()->create([
        'name' => 'Гарнир, крупа',
        'sort_order' => 1,
    ]);

    $product = ConstructorProduct::factory()->create([
        'constructor_category_id' => $category->id,
        'name' => 'Картофель запеченный',
        'price' => 50.00,
    ]);

    $response = $this->getJson('/api/constructor/categories');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'categories' => [
            '*' => [
                'id',
                'name',
                'sort_order',
                'products' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'image',
                        'sort_order',
                    ],
                ],
            ],
        ],
    ]);

    $response->assertJsonPath('categories.0.name', 'Гарнир, крупа');
    $response->assertJsonPath('categories.0.products.0.name', 'Картофель запеченный');
});

test('order can be created with constructor item', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $category1 = ConstructorCategory::factory()->create(['name' => 'Гарнир, крупа']);
    $category2 = ConstructorCategory::factory()->create(['name' => 'Мясо, рыба, птица']);

    $product1 = ConstructorProduct::factory()->create([
        'constructor_category_id' => $category1->id,
        'name' => 'Картофель запеченный',
        'price' => 50.00,
    ]);

    $product2 = ConstructorProduct::factory()->create([
        'constructor_category_id' => $category2->id,
        'name' => 'Говядина (сирлоин)',
        'price' => 200.00,
    ]);

    $constructorData = [
        'type' => 'constructor',
        'categories' => [
            $category1->id => [
                'category_name' => $category1->name,
                'product_id' => $product1->id,
                'product_name' => $product1->name,
                'price' => $product1->price,
            ],
            $category2->id => [
                'category_name' => $category2->name,
                'product_id' => $product2->id,
                'product_name' => $product2->name,
                'price' => $product2->price,
            ],
        ],
        'total_price' => 250.00,
    ];

    $response = $this->postJson('/api/orders', [
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'customer_address' => 'Test Address',
        'items' => [
            [
                'dish_id' => null,
                'dish_name' => 'Боул (конструктор)',
                'price' => 250.00,
                'quantity' => 1,
                'constructor_data' => $constructorData,
            ],
        ],
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'status' => 'pending_verification',
        'total' => 250.00,
    ]);

    $order = Order::where('customer_phone', '+995123456789')->first();
    $orderItem = $order->items()->first();

    expect($orderItem->dish_id)->toBeNull();
    expect($orderItem->dish_name)->toBe('Боул (конструктор)');
    expect($orderItem->constructor_data)->toBeArray();
    expect($orderItem->constructor_data['type'])->toBe('constructor');
    expect($orderItem->isConstructor())->toBeTrue();
});

test('order item correctly identifies constructor items', function () {
    $order = Order::create([
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'status' => 'new',
        'total' => 600.00,
    ]);

    $constructorItem = OrderItem::create([
        'order_id' => $order->id,
        'dish_id' => null,
        'dish_name' => 'Боул (конструктор)',
        'price' => 250.00,
        'quantity' => 1,
        'constructor_data' => [
            'type' => 'constructor',
            'categories' => [],
            'total_price' => 250.00,
        ],
    ]);

    $regularItem = OrderItem::create([
        'order_id' => $order->id,
        'dish_id' => null,
        'dish_name' => 'Борщ',
        'price' => 350.00,
        'quantity' => 1,
        'constructor_data' => null,
    ]);

    expect($constructorItem->isConstructor())->toBeTrue();
    expect($regularItem->isConstructor())->toBeFalse();
});

test('constructor data is properly stored in order items', function () {
    $order = Order::create([
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'status' => 'new',
        'total' => 250.00,
    ]);

    $constructorData = [
        'type' => 'constructor',
        'categories' => [
            1 => [
                'category_name' => 'Гарнир, крупа',
                'product_id' => 5,
                'product_name' => 'Картофель запеченный',
                'price' => 50.00,
            ],
            2 => [
                'category_name' => 'Мясо, рыба, птица',
                'product_id' => 10,
                'product_name' => 'Говядина (сирлоин)',
                'price' => 200.00,
            ],
        ],
        'total_price' => 250.00,
    ];

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'dish_id' => null,
        'dish_name' => 'Боул (конструктор)',
        'price' => 250.00,
        'quantity' => 1,
        'constructor_data' => $constructorData,
    ]);

    $orderItem->refresh();

    expect($orderItem->constructor_data)->toBeArray();
    expect($orderItem->constructor_data['type'])->toBe('constructor');
    expect($orderItem->constructor_data['categories'][1]['product_name'])->toBe('Картофель запеченный');
    expect($orderItem->constructor_data['categories'][2]['product_name'])->toBe('Говядина (сирлоин)');
});

test('order can be created with constructor item with multiple products per category', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $category1 = ConstructorCategory::factory()->create(['name' => 'Гарнир, крупа']);
    $category2 = ConstructorCategory::factory()->create(['name' => 'Овощи']);

    $product1 = ConstructorProduct::factory()->create([
        'constructor_category_id' => $category1->id,
        'name' => 'Картофель запеченный',
        'price' => 50.00,
    ]);

    $product2 = ConstructorProduct::factory()->create([
        'constructor_category_id' => $category1->id,
        'name' => 'Гречка',
        'price' => 45.00,
    ]);

    $product3 = ConstructorProduct::factory()->create([
        'constructor_category_id' => $category2->id,
        'name' => 'Огурец',
        'price' => 30.00,
    ]);

    $product4 = ConstructorProduct::factory()->create([
        'constructor_category_id' => $category2->id,
        'name' => 'Помидор',
        'price' => 35.00,
    ]);

    $constructorData = [
        'type' => 'constructor',
        'categories' => [
            $category1->id => [
                'category_name' => $category1->name,
                'products' => [
                    [
                        'product_id' => $product1->id,
                        'product_name' => $product1->name,
                        'price' => $product1->price,
                    ],
                    [
                        'product_id' => $product2->id,
                        'product_name' => $product2->name,
                        'price' => $product2->price,
                    ],
                ],
            ],
            $category2->id => [
                'category_name' => $category2->name,
                'products' => [
                    [
                        'product_id' => $product3->id,
                        'product_name' => $product3->name,
                        'price' => $product3->price,
                    ],
                    [
                        'product_id' => $product4->id,
                        'product_name' => $product4->name,
                        'price' => $product4->price,
                    ],
                ],
            ],
        ],
        'total_price' => 160.00,
    ];

    $response = $this->postJson('/api/orders', [
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'customer_address' => 'Test Address',
        'items' => [
            [
                'dish_id' => null,
                'dish_name' => 'Боул (конструктор)',
                'price' => 160.00,
                'quantity' => 1,
                'constructor_data' => $constructorData,
            ],
        ],
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);

    $order = Order::where('customer_phone', '+995123456789')->first();
    $orderItem = $order->items()->first();

    expect($orderItem->dish_id)->toBeNull();
    expect($orderItem->dish_name)->toBe('Боул (конструктор)');
    expect($orderItem->constructor_data)->toBeArray();
    expect($orderItem->constructor_data['type'])->toBe('constructor');
    expect($orderItem->isConstructor())->toBeTrue();

    // Проверяем множественный выбор
    $category1Data = $orderItem->constructor_data['categories'][$category1->id];
    expect($category1Data['products'])->toBeArray();
    expect(count($category1Data['products']))->toBe(2);
    expect($category1Data['products'][0]['product_name'])->toBe('Картофель запеченный');
    expect($category1Data['products'][1]['product_name'])->toBe('Гречка');

    $category2Data = $orderItem->constructor_data['categories'][$category2->id];
    expect($category2Data['products'])->toBeArray();
    expect(count($category2Data['products']))->toBe(2);
});
