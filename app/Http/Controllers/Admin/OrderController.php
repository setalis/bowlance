<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $pendingOrders = Order::with('items')
            ->where('status', '!=', 'completed')
            ->orderByRaw("CASE 
                WHEN status = 'pending_verification' THEN 0 
                WHEN status = 'new' THEN 1 
                WHEN status = 'preparing' THEN 2 
                WHEN status = 'delivering' THEN 3 
                ELSE 4 
            END")
            ->orderBy('created_at', 'desc')
            ->get();

        $completedOrders = Order::with('items')
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(15);

        return view('pages.admin.orders.index', [
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $order = Order::create([
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_address' => $data['customer_address'] ?? null,
            'status' => 'pending_verification',
            'total' => $total,
        ]);

        foreach ($data['items'] as $item) {
            $order->items()->create([
                'dish_id' => $item['dish_id'] ?? null,
                'dish_name' => $item['dish_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'constructor_data' => $item['constructor_data'] ?? null,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Заказ создан. Требуется подтверждение телефона через Telegram.',
                'order' => $order->load('items'),
                'requires_verification' => true,
                'order_id' => $order->id,
                'phone' => $order->customer_phone,
            ]);
        }

        return to_route('admin.orders.index')
            ->with('status', 'Заказ успешно создан.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order): View
    {
        $order->load('items');
        $dishes = Dish::orderBy('name')->get();

        return view('pages.admin.orders.edit', [
            'order' => $order,
            'dishes' => $dishes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        // Если обновляется только статус (быстрое изменение через select)
        if (count($data) === 1 && isset($data['status'])) {
            if ($data['status'] === 'completed' && $order->status !== 'completed') {
                $order->update([
                    'status' => $data['status'],
                    'completed_at' => now(),
                ]);
            } else {
                $order->update(['status' => $data['status']]);
            }

            return to_route('admin.orders.index')
                ->with('status', 'Статус заказа успешно обновлен.');
        }

        // Полное обновление заказа (из формы редактирования)
        if ($data['status'] === 'completed' && $order->status !== 'completed') {
            $data['completed_at'] = now();
        }

        // Если пересчитываем товары
        if (isset($data['items'])) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            $data['total'] = $total;

            // Удаляем старые товары и создаем новые
            $order->items()->delete();
            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'dish_id' => $item['dish_id'],
                    'dish_name' => $item['dish_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);
            }
            unset($data['items']);
        }

        $order->update($data);

        return to_route('admin.orders.index')
            ->with('status', 'Заказ успешно обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return to_route('admin.orders.index')
            ->with('status', 'Заказ успешно удален.');
    }
}
