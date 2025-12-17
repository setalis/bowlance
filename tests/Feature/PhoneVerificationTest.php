<?php

use App\Models\Order;
use App\Models\PhoneVerification;
use Illuminate\Support\Facades\Http;

test('can send verification code via telegram', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $order = Order::create([
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'customer_address' => 'Test Address',
        'status' => 'pending_verification',
        'total' => 100.00,
    ]);

    $response = $this->postJson('/api/phone/verification/send', [
        'phone' => '+995123456789',
        'telegram_chat_id' => '123456789',
        'order_id' => $order->id,
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('phone_verifications', [
        'order_id' => $order->id,
        'phone' => '+995123456789',
        'telegram_chat_id' => '123456789',
    ]);
});

test('can verify code and update order status', function () {
    $order = Order::create([
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'customer_address' => 'Test Address',
        'status' => 'pending_verification',
        'total' => 100.00,
    ]);

    $code = PhoneVerification::generateCode();
    $verification = PhoneVerification::create([
        'order_id' => $order->id,
        'phone' => '+995123456789',
        'code' => $code,
        'telegram_chat_id' => '123456789',
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);

    $response = $this->postJson('/api/phone/verification/verify', [
        'order_id' => $order->id,
        'code' => $code,
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);

    $order->refresh();
    $this->assertEquals('new', $order->status);

    $verification->refresh();
    $this->assertNotNull($verification->verified_at);
});

test('rejects invalid verification code', function () {
    $order = Order::create([
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'customer_address' => 'Test Address',
        'status' => 'pending_verification',
        'total' => 100.00,
    ]);

    $code = PhoneVerification::generateCode();
    PhoneVerification::create([
        'order_id' => $order->id,
        'phone' => '+995123456789',
        'code' => $code,
        'telegram_chat_id' => '123456789',
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);

    $response = $this->postJson('/api/phone/verification/verify', [
        'order_id' => $order->id,
        'code' => '000000',
    ]);

    $response->assertStatus(400);
    $response->assertJson(['success' => false]);

    $order->refresh();
    $this->assertEquals('pending_verification', $order->status);
});

test('rejects verification for non-pending order', function () {
    $order = Order::create([
        'customer_name' => 'Test User',
        'customer_phone' => '+995123456789',
        'customer_address' => 'Test Address',
        'status' => 'new',
        'total' => 100.00,
    ]);

    $response = $this->postJson('/api/phone/verification/send', [
        'phone' => '+995123456789',
        'telegram_chat_id' => '123456789',
        'order_id' => $order->id,
    ]);

    $response->assertStatus(400);
    $response->assertJson(['success' => false]);
});
