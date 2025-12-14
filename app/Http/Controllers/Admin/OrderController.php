<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
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
            'status' => 'new',
            'total' => $total,
        ]);

        foreach ($data['items'] as $item) {
            $order->items()->create([
                'dish_id' => $item['dish_id'],
                'dish_name' => $item['dish_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно создан.',
                'order' => $order->load('items'),
            ]);
        }

        return to_route('admin.orders.index')
            ->with('status', 'Заказ успешно создан.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'completed' && $order->status !== 'completed') {
            $data['completed_at'] = now();
        }

        $order->update($data);

        return to_route('admin.orders.index')
            ->with('status', 'Заказ успешно обновлен.');
    }
}
