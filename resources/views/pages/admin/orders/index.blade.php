@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Заказы">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Заказы</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    @if (session('status'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('status')" />
        </div>
    @endif

    <div class="space-y-6">
        <!-- Невыполненные заказы -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark md:p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Невыполненные заказы
                </h3>
            </div>

            @if($pendingOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-cyan-300 dark:bg-gray-800">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">ID</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Клиент</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Телефон</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Товары</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Сумма</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Статус</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90 text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingOrders as $order)
                                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">#{{ $order->id }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">{{ $order->customer_name }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $order->customer_phone }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div class="space-y-2">
                                            @foreach($order->items as $item)
                                                <div>
                                                    <div class="font-medium">{{ $item->dish_name }} x{{ $item->quantity }}</div>
                                                    @if($item->isConstructor() && $item->constructor_data)
                                                        <div class="ml-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                            @foreach($item->constructor_data['categories'] ?? [] as $category)
                                                                <div>• {{ $category['category_name'] ?? '' }}: {{ $category['product_name'] ?? '' }} ({{ number_format($category['price'] ?? 0, 2) }} ₾)</div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($order->total, 2) }} ₾</td>
                                    <td class="px-4 py-4">
                                        @php
                                            $statusLabels = [
                                                'new' => 'Новый',
                                                'preparing' => 'Приготовление',
                                                'delivering' => 'Доставка',
                                                'completed' => 'Выполнен',
                                            ];
                                            $statusColors = [
                                                'new' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                'preparing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                                'delivering' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                            ];
                                        @endphp
                                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <select 
                                                name="status" 
                                                onchange="this.form.submit()"
                                                class="text-sm rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                                            >
                                                <option value="new" {{ $order->status === 'new' ? 'selected' : '' }}>Новый</option>
                                                <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>Приготовление</option>
                                                <option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>Доставка</option>
                                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Выполнен</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.orders.edit', $order) }}"
                                                class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                                title="Редактировать">
                                                <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('edit') !!}</span>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.orders.destroy', $order) }}"
                                                onsubmit="return confirm('Вы уверены, что хотите удалить этот заказ?');"
                                                class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-red-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-red-400"
                                                    title="Удалить">
                                                    <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('delete') !!}</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">Нет невыполненных заказов</p>
                </div>
            @endif
        </div>

        <!-- Выполненные заказы -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark md:p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Выполненные заказы
                </h3>
            </div>

            @if($completedOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-cyan-300 dark:bg-gray-800">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">ID</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Клиент</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Телефон</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Товары</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Сумма</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Дата выполнения</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90 text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedOrders as $order)
                                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">#{{ $order->id }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">{{ $order->customer_name }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $order->customer_phone }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div class="space-y-2">
                                            @foreach($order->items as $item)
                                                <div>
                                                    <div class="font-medium">{{ $item->dish_name }} x{{ $item->quantity }}</div>
                                                    @if($item->isConstructor() && $item->constructor_data)
                                                        <div class="ml-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                            @foreach($item->constructor_data['categories'] ?? [] as $category)
                                                                <div>• {{ $category['category_name'] ?? '' }}: {{ $category['product_name'] ?? '' }} ({{ number_format($category['price'] ?? 0, 2) }} ₾)</div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($order->total, 2) }} ₾</td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $order->completed_at ? $order->completed_at->format('d.m.Y H:i') : '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.orders.edit', $order) }}"
                                                class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                                title="Редактировать">
                                                <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('edit') !!}</span>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.orders.destroy', $order) }}"
                                                onsubmit="return confirm('Вы уверены, что хотите удалить этот заказ?');"
                                                class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-red-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-red-400"
                                                    title="Удалить">
                                                    <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('delete') !!}</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $completedOrders->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">Нет выполненных заказов</p>
                </div>
            @endif
        </div>
    </div>
@endsection

