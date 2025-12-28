@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Заголовок -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Личный кабинет</h1>
            <p class="text-gray-600">Добро пожаловать, {{ $user->name }}!</p>
        </div>

        <!-- Информация о пользователе -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Мои данные</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
                    <p class="text-gray-900">{{ $user->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <p class="text-gray-900">{{ $user->email }}</p>
                </div>
                @if($user->phone)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                    <p class="text-gray-900">{{ $user->phone }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- История заказов -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">История заказов</h2>

            @if($orders->count() > 0)
                <div class="space-y-4">
                    @foreach($orders as $order)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-2">
                                        <span class="text-lg font-semibold text-gray-900">Заказ #{{ $order->id }}</span>
                                        @php
                                            $statusLabels = [
                                                'new' => 'Новый',
                                                'pending_verification' => 'Ожидает подтверждения',
                                                'preparing' => 'Приготовление',
                                                'delivering' => 'Доставка',
                                                'completed' => 'Выполнен',
                                            ];
                                            $statusColors = [
                                                'new' => 'bg-blue-100 text-blue-800',
                                                'pending_verification' => 'bg-yellow-100 text-yellow-800',
                                                'preparing' => 'bg-purple-100 text-purple-800',
                                                'delivering' => 'bg-indigo-100 text-indigo-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                            ];
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        Дата: {{ $order->created_at->format('d.m.Y H:i') }}
                                    </p>
                                    <div class="space-y-2">
                                        @foreach($order->items as $item)
                                            <div class="text-sm text-gray-700">
                                                <span class="font-medium">{{ $item->dish_name }}</span>
                                                <span class="text-gray-500"> x{{ $item->quantity }}</span>
                                                <span class="text-gray-600"> - {{ number_format($item->price * $item->quantity, 2) }} ₾</span>
                                                @if($item->isConstructor() && $item->constructor_data)
                                                    <div class="ml-4 mt-1 text-xs text-gray-500">
                                                        @foreach($item->constructor_data['categories'] ?? [] as $category)
                                                            @if(isset($category['products']) && is_array($category['products']))
                                                                @foreach($category['products'] as $product)
                                                                    <div>• {{ $category['category_name'] ?? '' }}: {{ $product['product_name'] ?? '' }}</div>
                                                                @endforeach
                                                            @elseif(isset($category['product_name']))
                                                                <div>• {{ $category['category_name'] ?? '' }}: {{ $category['product_name'] ?? '' }}</div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-orange-600 mb-2">
                                        {{ number_format($order->total, 2) }} ₾
                                    </div>
                                    @if($order->customer_address)
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Адрес:</span> {{ $order->customer_address }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Пагинация -->
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет заказов</h3>
                    <p class="mt-1 text-sm text-gray-500">Вы еще не сделали ни одного заказа.</p>
                    <div class="mt-6">
                        <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                            Перейти к заказам
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
