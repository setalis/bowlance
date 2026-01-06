<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            ->paginate(10);

        $completedOrders = Order::with('items')
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

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

        // Автоматическая регистрация пользователя, если не авторизован
        $user = Auth::user();
        if (! $user) {
            // Ищем пользователя по телефону
            $user = User::where('phone', $data['customer_phone'])->first();

            // Если пользователь не найден, создаем нового
            if (! $user) {
                // Генерируем email на основе телефона, если его нет
                $email = $data['customer_phone'].'@temp.local';

                // Проверяем, не существует ли уже пользователь с таким email
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    // Если пользователь существует, проверяем, не используется ли телефон другим пользователем
                    $existingUserWithPhone = User::where('phone', $data['customer_phone'])
                        ->where('id', '!=', $existingUser->id)
                        ->first();

                    if ($existingUserWithPhone) {
                        // Если телефон уже используется другим пользователем, возвращаем ошибку
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Пользователь с таким номером телефона уже зарегистрирован. Пожалуйста, войдите в систему под этим номером.',
                                'errors' => [
                                    'customer_phone' => ['Пользователь с таким номером телефона уже зарегистрирован. Пожалуйста, войдите в систему под этим номером.'],
                                ],
                            ], 422);
                        }

                        return redirect()->back()
                            ->withErrors(['customer_phone' => 'Пользователь с таким номером телефона уже зарегистрирован. Пожалуйста, войдите в систему под этим номером.'])
                            ->withInput();
                    }

                    // Если телефон не занят, обновляем телефон и генерируем токен, если его нет
                    $updateData = ['phone' => $data['customer_phone']];
                    if (! $existingUser->login_token) {
                        $updateData['login_token'] = bin2hex(random_bytes(32));
                    }
                    $existingUser->update($updateData);
                    $user = $existingUser;
                } else {
                    // Генерируем токен для входа
                    $loginToken = bin2hex(random_bytes(32));

                    // Создаем нового пользователя
                    $user = User::create([
                        'name' => $data['customer_name'],
                        'email' => $email,
                        'phone' => $data['customer_phone'],
                        'password' => Hash::make(uniqid('', true)), // Генерируем случайный пароль
                        'login_token' => $loginToken,
                    ]);

                    // Назначаем роль CUSTOMER, если она существует
                    $customerRole = Role::where('name', RoleName::CUSTOMER->value)->first();
                    if ($customerRole && ! $user->hasRole(RoleName::CUSTOMER)) {
                        $user->roles()->attach($customerRole);
                    }
                }
            } else {
                // Если пользователь найден, но у него нет токена - генерируем новый
                if (! $user->login_token) {
                    $user->update(['login_token' => bin2hex(random_bytes(32))]);
                }
            }

            // Автоматически авторизуем пользователя
            Auth::login($user);
        } else {
            // Если пользователь авторизован, НЕ обновляем его данные из заказа
            // Имя и телефон в заказе могут отличаться от данных пользователя
            // (например, заказ на другое имя или другой телефон для доставки)
            // Обновляем только телефон, если он изменился и пользователь не имеет роли администратора
            // (администраторы могут иметь другой телефон в системе)
            if (! $user->isAdmin() && $user->phone !== $data['customer_phone']) {
                // Проверяем, не используется ли этот телефон другим пользователем
                $existingUserWithPhone = User::where('phone', $data['customer_phone'])
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existingUserWithPhone) {
                    // Если телефон уже используется другим пользователем, возвращаем ошибку
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Пользователь с таким номером телефона уже зарегистрирован. Пожалуйста, войдите в систему под этим номером.',
                            'errors' => [
                                'customer_phone' => ['Пользователь с таким номером телефона уже зарегистрирован. Пожалуйста, войдите в систему под этим номером.'],
                            ],
                        ], 422);
                    }

                    return redirect()->back()
                        ->withErrors(['customer_phone' => 'Пользователь с таким номером телефона уже зарегистрирован. Пожалуйста, войдите в систему под этим номером.'])
                        ->withInput();
                }

                // Обновляем телефон только для обычных пользователей, если он не занят
                $user->update(['phone' => $data['customer_phone']]);
            }
            // Имя пользователя НЕ обновляется из заказа - оно остается в профиле пользователя
        }

        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_address' => $data['customer_address'] ?? null,
            'delivery_type' => $data['delivery_type'] ?? 'pickup',
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
                'login_token' => $user->login_token, // Возвращаем токен для сохранения в localStorage
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
