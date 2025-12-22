@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Редактировать заказ">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('admin.orders.index') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Заказы</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Редактировать заказ</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    @if (session('status'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('status')" />
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark md:p-6">
        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Информация о клиенте -->
            <div>
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Информация о клиенте</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="customer_name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Имя клиента <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="customer_name"
                            name="customer_name"
                            value="{{ old('customer_name', $order->customer_name) }}"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                        />
                        @error('customer_name')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Телефон <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="customer_phone"
                            name="customer_phone"
                            value="{{ old('customer_phone', $order->customer_phone) }}"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                        />
                        @error('customer_phone')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="customer_address" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Адрес доставки
                        </label>
                        <textarea
                            id="customer_address"
                            name="customer_address"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                        >{{ old('customer_address', $order->customer_address) }}</textarea>
                        @error('customer_address')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Статус заказа <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                        >
                            <option value="new" {{ old('status', $order->status) === 'new' ? 'selected' : '' }}>Новый</option>
                            <option value="preparing" {{ old('status', $order->status) === 'preparing' ? 'selected' : '' }}>Приготовление</option>
                            <option value="delivering" {{ old('status', $order->status) === 'delivering' ? 'selected' : '' }}>Доставка</option>
                            <option value="completed" {{ old('status', $order->status) === 'completed' ? 'selected' : '' }}>Выполнен</option>
                        </select>
                        @error('status')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Товары в заказе -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Товары в заказе</h3>
                    <button
                        type="button"
                        id="add-item-btn"
                        class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 focus:outline-hidden focus:ring-4 focus:ring-brand-300 dark:bg-brand-500 dark:hover:bg-brand-600 dark:focus:ring-brand-800"
                    >
                        Добавить товар
                    </button>
                </div>

                <div id="order-items" class="space-y-4">
                    @foreach($order->items as $index => $item)
                        <div class="order-item rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            @if($item->isConstructor() && $item->constructor_data)
                                <div class="mb-3 rounded-lg bg-orange-50 p-3 dark:bg-orange-900/20">
                                    <div class="font-semibold text-gray-900 dark:text-white mb-2">{{ $item->dish_name }} x{{ $item->quantity }}</div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                        @foreach($item->constructor_data['categories'] ?? [] as $category)
                                            @if(isset($category['products']) && is_array($category['products']))
                                                {{-- Новый формат - массив продуктов --}}
                                                @foreach($category['products'] as $product)
                                                    <div>• {{ $category['category_name'] ?? '' }}: {{ $product['product_name'] ?? '' }} ({{ number_format($product['price'] ?? 0, 2) }} ₾)</div>
                                                @endforeach
                                            @elseif(isset($category['product_name']))
                                                {{-- Старый формат - один продукт (обратная совместимость) --}}
                                                <div>• {{ $category['category_name'] ?? '' }}: {{ $category['product_name'] ?? '' }} ({{ number_format($category['price'] ?? 0, 2) }} ₾)</div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="mt-2 text-sm font-semibold text-orange-600 dark:text-orange-400">
                                        Итого: {{ number_format($item->price, 2) }} ₾
                                    </div>
                                </div>
                                <input type="hidden" name="items[{{ $index }}][dish_id]" value="">
                                <input type="hidden" name="items[{{ $index }}][dish_name]" value="{{ $item->dish_name }}">
                                <input type="hidden" name="items[{{ $index }}][price]" value="{{ $item->price }}">
                                <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}">
                                <input type="hidden" name="items[{{ $index }}][constructor_data]" value="{{ json_encode($item->constructor_data) }}">
                            @else
                            <div class="grid gap-4 md:grid-cols-5">
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Блюдо <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="items[{{ $index }}][dish_id]"
                                        required
                                        class="dish-select h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                                    >
                                        <option value="">Выберите блюдо</option>
                                        @foreach($dishes as $dish)
                                            <option value="{{ $dish->id }}" {{ $item->dish_id == $dish->id ? 'selected' : '' }} data-price="{{ $dish->price }}">
                                                {{ $dish->name }} - {{ number_format($dish->price, 2) }} ₾
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[{{ $index }}][dish_name]" class="dish-name" value="{{ $item->dish_name }}">
                                    <input type="hidden" name="items[{{ $index }}][price]" class="dish-price" value="{{ $item->price }}">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Количество <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][quantity]"
                                        value="{{ $item->quantity }}"
                                        min="1"
                                        required
                                        class="item-quantity h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                                    />
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Цена за шт.
                                    </label>
                                    <input
                                        type="text"
                                        readonly
                                        value="{{ number_format($item->price, 2) }} ₾"
                                        class="item-price-display h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                    />
                                </div>

                                <div class="flex items-end">
                                    <button
                                        type="button"
                                        class="remove-item-btn inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 focus:outline-hidden focus:ring-4 focus:ring-red-300 dark:border-red-700 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20 dark:focus:ring-red-800"
                                    >
                                        Удалить
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-end">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-4">
                            <span class="text-lg font-semibold text-gray-800 dark:text-white/90">Итого:</span>
                            <span id="order-total" class="text-xl font-bold text-brand-600">
                                {{ number_format($order->total, 2) }} ₾
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                <a
                    href="{{ route('admin.orders.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-hidden focus:ring-4 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                >
                    Отмена
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 focus:outline-hidden focus:ring-4 focus:ring-brand-300 dark:bg-brand-500 dark:hover:bg-brand-600 dark:focus:ring-brand-800"
                >
                    Сохранить изменения
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemIndex = {{ $order->items->count() }};
            const orderItemsContainer = document.getElementById('order-items');
            const addItemBtn = document.getElementById('add-item-btn');
            const dishes = @json($dishes);

            // Добавление нового товара
            addItemBtn.addEventListener('click', function() {
                const itemHtml = `
                    <div class="order-item rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="grid gap-4 md:grid-cols-5">
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Блюдо <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="items[${itemIndex}][dish_id]"
                                    required
                                    class="dish-select h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                                >
                                    <option value="">Выберите блюдо</option>
                                    ${dishes.map(dish => `
                                        <option value="${dish.id}" data-price="${dish.price}">
                                            ${dish.name} - ${parseFloat(dish.price).toFixed(2)} ₾
                                        </option>
                                    `).join('')}
                                </select>
                                <input type="hidden" name="items[${itemIndex}][dish_name]" class="dish-name">
                                <input type="hidden" name="items[${itemIndex}][price]" class="dish-price">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Количество <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="items[${itemIndex}][quantity]"
                                    value="1"
                                    min="1"
                                    required
                                    class="item-quantity h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                                />
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Цена за шт.
                                </label>
                                <input
                                    type="text"
                                    readonly
                                    value="0.00 ₾"
                                    class="item-price-display h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                                />
                            </div>

                            <div class="flex items-end">
                                <button
                                    type="button"
                                    class="remove-item-btn inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 focus:outline-hidden focus:ring-4 focus:ring-red-300 dark:border-red-700 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20 dark:focus:ring-red-800"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                orderItemsContainer.insertAdjacentHTML('beforeend', itemHtml);
                itemIndex++;
                attachEventListeners();
            });

            // Удаление товара
            function attachEventListeners() {
                document.querySelectorAll('.remove-item-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        this.closest('.order-item').remove();
                        calculateTotal();
                    });
                });

                document.querySelectorAll('.dish-select').forEach(select => {
                    select.addEventListener('change', function() {
                        const option = this.options[this.selectedIndex];
                        const price = parseFloat(option.dataset.price || 0);
                        const dishName = option.text.split(' - ')[0];
                        
                        const item = this.closest('.order-item');
                        item.querySelector('.dish-name').value = dishName;
                        item.querySelector('.dish-price').value = price;
                        item.querySelector('.item-price-display').value = price.toFixed(2) + ' ₾';
                        
                        calculateTotal();
                    });
                });

                document.querySelectorAll('.item-quantity').forEach(input => {
                    input.addEventListener('input', calculateTotal);
                });
            }

            // Расчет общей суммы
            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.order-item').forEach(item => {
                    const price = parseFloat(item.querySelector('.dish-price').value || 0);
                    const quantity = parseFloat(item.querySelector('.item-quantity').value || 0);
                    total += price * quantity;
                });
                document.getElementById('order-total').textContent = total.toFixed(2) + ' ₾';
            }

            attachEventListeners();
        });
    </script>
@endsection










