@extends('layouts.frontend')

@section('content')
    <!-- Баннер уведомления для Telegram WebView (всегда видимый) -->
    <div id="telegram-status-banner" class="hidden fixed top-0 left-0 right-0 z-[70] bg-white dark:bg-gray-800 shadow-lg border-b-2 border-orange-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="telegram-status-icon" class="flex-shrink-0">
                        <svg class="animate-spin h-6 w-6 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div>
                        <p id="telegram-status-text" class="text-sm font-medium text-gray-900 dark:text-white">
                            Проверка статуса заказа...
                        </p>
                    </div>
                </div>
                <button 
                    type="button" 
                    id="telegram-status-close"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    style="display: none;"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

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
                                    @if($order->status === 'pending_verification')
                                        <div class="mt-3">
                                            <button 
                                                type="button"
                                                onclick="openVerificationModal({{ $order->id }})"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.2 1.656-1.053 5.684-1.482 7.533-.19.856-.562 1.141-.925 1.17-.78.055-1.371-.515-2.127-1.009-.59-.39-.925-.606-1.5-1.009-.662-.45-.232-.697.144-1.101.098-.105 1.78-1.633 1.814-1.772.008-.033.016-.156-.06-.234-.075-.078-.184-.051-.264-.03-.112.027-1.89 1.2-5.336 3.523-.505.336-.96.5-1.371.492-.46-.009-1.344-.26-2.001-.475-.807-.268-1.45-.41-1.394-.867.027-.225.405-.456 1.113-.69 4.323-1.88 7.203-3.12 8.64-3.72 4.14-1.8 5.001-2.115 5.562-2.139.12-.005.39-.027.565.16.138.148.192.348.211.488.019.14.033.457-.019.705z"/>
                                                </svg>
                                                Подтвердить заказ
                                            </button>
                                        </div>
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

<!-- Modal статуса верификации для мобильного Telegram -->
<div id="telegram-return-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-[60] justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/80 dark:bg-gray-900/90"></div>
    
    <div class="relative p-4 w-full max-w-md max-h-full z-[60]">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 id="telegram-return-modal-title" class="text-xl font-semibold text-gray-900 dark:text-white">
                    Статус верификации
                </h3>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5 space-y-4">
                <div id="telegram-return-loading" class="text-center py-4">
                    <svg class="animate-spin h-8 w-8 text-orange-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Проверка статуса заказа...</p>
                </div>
                <div id="telegram-return-success" class="hidden text-center py-4">
                    <div class="mb-4">
                        <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">✅ Телефон подтвержден!</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Ваш заказ успешно принят и будет обработан.</p>
                    <button 
                        type="button" 
                        id="telegram-return-close-success-account"
                        class="w-full text-white bg-orange-500 hover:bg-orange-600 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-orange-500 dark:hover:bg-orange-600 dark:focus:ring-orange-800"
                    >
                        Закрыть
                    </button>
                </div>
                <div id="telegram-return-error" class="hidden text-center py-4">
                    <div class="mb-4">
                        <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Ошибка верификации</h4>
                    <p id="telegram-return-error-message-account" class="text-sm text-gray-600 dark:text-gray-400 mb-4"></p>
                    <button 
                        type="button" 
                        id="telegram-return-close-error-account"
                        class="w-full text-white bg-red-500 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800"
                    >
                        Закрыть
                    </button>
                </div>
                <div id="telegram-return-open-browser-account" class="hidden mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center mb-3">
                        Для лучшего опыта откройте эту страницу в обычном браузере
                    </p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                            <input 
                                type="text" 
                                id="telegram-browser-url-account"
                                readonly
                                class="flex-1 text-xs bg-transparent border-none text-gray-700 dark:text-gray-300 focus:outline-none"
                                value=""
                            />
                            <button 
                                type="button" 
                                id="telegram-copy-url-btn-account"
                                class="px-3 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded focus:outline-none focus:ring-2 focus:ring-blue-300"
                                title="Копировать ссылку"
                            >
                                📋 Копировать
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            Скопируйте ссылку и откройте её в обычном браузере (Chrome, Safari и т.д.)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения заказа -->
<div id="verification-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Подтверждение заказа</h3>
                <button 
                    type="button"
                    onclick="closeVerificationModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <div id="verification-step-1">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Для подтверждения заказа необходимо подтвердить номер телефона через Telegram. 
                        Нажмите кнопку ниже, чтобы открыть Telegram бота и получить код подтверждения.
                    </p>
                    <div id="verification-error-1" class="hidden text-red-600 dark:text-red-400 text-sm mb-4"></div>
                    <div class="flex flex-col gap-3">
                        <a 
                            id="telegram-bot-link-account"
                            href="#"
                            target="_blank"
                            class="w-full inline-flex items-center justify-center gap-2 text-white bg-[#0088cc] hover:bg-[#0077b5] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.2 1.656-1.053 5.684-1.482 7.533-.19.856-.562 1.141-.925 1.17-.78.055-1.371-.515-2.127-1.009-.59-.39-.925-.606-1.5-1.009-.662-.45-.232-.697.144-1.101.098-.105 1.78-1.633 1.814-1.772.008-.033.016-.156-.06-.234-.075-.078-.184-.051-.264-.03-.112.027-1.89 1.2-5.336 3.523-.505.336-.96.5-1.371.492-.46-.009-1.344-.26-2.001-.475-.807-.268-1.45-.41-1.394-.867.027-.225.405-.456 1.113-.69 4.323-1.88 7.203-3.12 8.64-3.72 4.14-1.8 5.001-2.115 5.562-2.139.12-.005.39-.027.565.16.138.148.192.348.211.488.019.14.033.457-.019.705z"/>
                            </svg>
                            Открыть Telegram бота
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            После открытия бота нажмите кнопку "Начать" или отправьте команду /start
                        </p>
                    </div>
                    <div id="waiting-for-code-account" class="hidden mt-4">
                        <div class="flex items-center justify-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Ожидание кода...
                        </div>
                    </div>
                </div>
                <div id="verification-step-2-account" class="hidden">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Код подтверждения отправлен в Telegram. Введите код для подтверждения заказа.
                    </p>
                    <div class="mb-4">
                        <label for="verification_code_account" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Код подтверждения <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="verification_code_account" 
                            name="verification_code_account" 
                            required
                            maxlength="6"
                            pattern="[0-9]{6}"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-orange-500 dark:focus:border-orange-500 text-center tracking-widest"
                            placeholder="000000"
                        >
                    </div>
                    <div id="verification-error-2-account" class="hidden text-red-600 dark:text-red-400 text-sm mb-4"></div>
                    <div class="flex gap-3">
                        <button 
                            type="button" 
                            id="back-button-account"
                            onclick="backToStep1()"
                            class="flex-1 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                        >
                            Назад
                        </button>
                        <button 
                            type="button" 
                            id="verify-code-button-account"
                            onclick="verifyCodeAccount()"
                            class="flex-1 text-white bg-orange-500 hover:bg-orange-600 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-orange-500 dark:hover:bg-orange-600 dark:focus:ring-orange-800"
                        >
                            Подтвердить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;

function openVerificationModal(orderId) {
    currentOrderId = orderId;
    document.getElementById('verification-modal').classList.remove('hidden');
    document.getElementById('verification-step-1').classList.remove('hidden');
    document.getElementById('verification-step-2-account').classList.add('hidden');
    document.getElementById('verification-error-1').classList.add('hidden');
    document.getElementById('verification-error-2-account').classList.add('hidden');
    document.getElementById('verification_code_account').value = '';
    document.getElementById('waiting-for-code-account').classList.add('hidden');
}

function closeVerificationModal() {
    document.getElementById('verification-modal').classList.add('hidden');
    currentOrderId = null;
}

function backToStep1() {
    document.getElementById('verification-step-1').classList.remove('hidden');
    document.getElementById('verification-step-2-account').classList.add('hidden');
    document.getElementById('verification-error-2-account').classList.add('hidden');
    document.getElementById('verification_code_account').value = '';
}

async function verifyCodeAccount() {
    const code = document.getElementById('verification_code_account').value;
    const errorDiv = document.getElementById('verification-error-2-account');
    const verifyButton = document.getElementById('verify-code-button-account');
    
    if (!code || code.length !== 6) {
        errorDiv.textContent = 'Введите 6-значный код';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    errorDiv.classList.add('hidden');
    verifyButton.disabled = true;
    verifyButton.textContent = 'Проверка...';
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch('{{ route("api.phone.verification.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                order_id: currentOrderId,
                code: code,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Успешное подтверждение
            showNotification('✅ Телефон подтвержден! Ваш заказ успешно принят.', 'success');
            closeVerificationModal();
            // Перезагружаем страницу для обновления статуса заказа
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            errorDiv.textContent = data.message || 'Неверный код или код истек. Попробуйте еще раз.';
            errorDiv.classList.remove('hidden');
            verifyButton.disabled = false;
            verifyButton.textContent = 'Подтвердить';
        }
    } catch (error) {
        console.error('Ошибка при проверке кода:', error);
        errorDiv.textContent = 'Ошибка при проверке кода. Попробуйте позже.';
        errorDiv.classList.remove('hidden');
        verifyButton.disabled = false;
        verifyButton.textContent = 'Подтвердить';
    }
}

// Функция для определения встроенного браузера Telegram
function isTelegramWebView() {
    const userAgent = navigator.userAgent || navigator.vendor || window.opera;
    // Проверяем User-Agent на наличие признаков Telegram WebView
    // Telegram WebView обычно содержит "Telegram" в User-Agent
    // Также проверяем наличие window.TelegramWebApp (официальный API Telegram)
    return userAgent.includes('Telegram') || 
           (typeof window.TelegramWebApp !== 'undefined') ||
           (window.navigator && window.navigator.userAgent && window.navigator.userAgent.includes('Telegram'));
}

// Инициализация обработчика для кнопки Telegram
// Обработка возврата на ту же страницу после верификации
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('return') === 'true') {
        const isTelegram = isTelegramWebView();
        const savedUrl = localStorage.getItem('verificationReturnUrl');
        // Получаем orderId из URL или localStorage
        const orderIdFromUrl = urlParams.get('order_id');
        const currentOrderId = orderIdFromUrl || localStorage.getItem('currentVerificationOrderId');
        
        console.log('Обнаружен параметр return=true, начинаю обработку...');
        console.log('Параметры:', {
            isTelegram: isTelegram,
            orderIdFromUrl: orderIdFromUrl,
            currentOrderId: currentOrderId,
            savedUrl: savedUrl
        });
        
        // ВСЕГДА показываем баннер при return=true, даже если нет orderId
        const banner = document.getElementById('telegram-status-banner');
        const bannerIcon = document.getElementById('telegram-status-icon');
        const bannerText = document.getElementById('telegram-status-text');
        const bannerClose = document.getElementById('telegram-status-close');
        
        if (banner) {
            console.log('Показываю баннер уведомления...');
            banner.classList.remove('hidden');
            document.body.style.paddingTop = banner.offsetHeight + 'px';
        }
        
        // ВСЕГДА показываем баннер и модальное окно при return=true
        // Это гарантирует, что пользователь увидит статус независимо от браузера
        if (currentOrderId) {
            // Сохраняем orderId в localStorage, если его там еще нет
            if (!localStorage.getItem('currentVerificationOrderId') && orderIdFromUrl) {
                localStorage.setItem('currentVerificationOrderId', orderIdFromUrl);
            }
            const modal = document.getElementById('telegram-return-modal');
            const loadingDiv = document.getElementById('telegram-return-loading');
            const successDiv = document.getElementById('telegram-return-success');
            const errorDiv = document.getElementById('telegram-return-error');
            const openBrowserDiv = document.getElementById('telegram-return-open-browser-account');
            
            if (modal) {
                // Показываем модальное окно
                modal.classList.remove('hidden');
                loadingDiv.classList.remove('hidden');
                successDiv.classList.add('hidden');
                errorDiv.classList.add('hidden');
                
                // Проверяем статус заказа
                async function checkVerificationStatus() {
                    try {
                        const checkResponse = await fetch(`/api/phone/verification/check-status?order_id=${currentOrderId}`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });
                        
                        if (!checkResponse.ok) {
                            throw new Error('Ошибка проверки статуса');
                        }
                        
                        const statusData = await checkResponse.json();
                        
                        loadingDiv.classList.add('hidden');
                        
                        if (statusData.success && (statusData.is_verified || statusData.order_status !== 'pending_verification')) {
                            // Верификация успешна
                            successDiv.classList.remove('hidden');
                            openBrowserDiv.classList.remove('hidden');
                            
                            // Обновляем баннер с успешным сообщением
                            if (banner && bannerIcon && bannerText && bannerClose) {
                                banner.classList.remove('hidden');
                                banner.classList.add('bg-green-50', 'dark:bg-green-900', 'border-green-500');
                                bannerIcon.innerHTML = `
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                `;
                                bannerText.textContent = '✅ Телефон подтвержден! Ваш заказ успешно принят и будет обработан.';
                                bannerText.classList.remove('text-gray-900', 'dark:text-white');
                                bannerText.classList.add('text-green-800', 'dark:text-green-100');
                                bannerClose.style.display = 'block';
                                bannerClose.onclick = function() {
                                    banner.classList.add('hidden');
                                    document.body.style.paddingTop = '0';
                                };
                            }
                            
                            // Очищаем все флаги верификации
                            localStorage.removeItem('pendingVerificationCheck');
                            localStorage.removeItem('currentVerificationOrderId');
                            localStorage.removeItem('verificationInProgress');
                            localStorage.removeItem('verificationStartedAt');
                            localStorage.removeItem('pendingVerificationSuccess');
                            localStorage.removeItem('verificationReturnUrl');
                            
                            // Обработчик закрытия успешного окна
                            const closeBtn = document.getElementById('telegram-return-close-success-account');
                            if (closeBtn) {
                                closeBtn.onclick = function() {
                                    modal.classList.add('hidden');
                                    // Правильно убираем параметр return из URL перед перезагрузкой
                                    const url = new URL(window.location.href);
                                    url.searchParams.delete('return');
                                    const newUrl = url.search ? url.pathname + url.search : url.pathname;
                                    window.history.replaceState({}, '', newUrl || window.location.pathname);
                                    // Обновляем страницу для отображения обновленного статуса заказа
                                    window.location.reload();
                                };
                            }
                        } else {
                            // Верификация не завершена или ошибка
                            errorDiv.classList.remove('hidden');
                            openBrowserDiv.classList.remove('hidden');
                            const errorMessage = document.getElementById('telegram-return-error-message-account');
                            if (errorMessage) {
                                errorMessage.textContent = 'Верификация еще не завершена. Пожалуйста, завершите процесс подтверждения в Telegram боте.';
                            }
                            
                            // Обновляем баннер с сообщением об ошибке
                            if (banner && bannerIcon && bannerText && bannerClose) {
                                banner.classList.remove('hidden');
                                banner.classList.add('bg-yellow-50', 'dark:bg-yellow-900', 'border-yellow-500');
                                bannerIcon.innerHTML = `
                                    <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                `;
                                bannerText.textContent = '⚠️ Верификация еще не завершена. Завершите процесс подтверждения в Telegram боте.';
                                bannerText.classList.remove('text-gray-900', 'dark:text-white');
                                bannerText.classList.add('text-yellow-800', 'dark:text-yellow-100');
                                bannerClose.style.display = 'block';
                                bannerClose.onclick = function() {
                                    banner.classList.add('hidden');
                                    document.body.style.paddingTop = '0';
                                };
                            }
                            
                            // Обработчик закрытия окна ошибки
                            const closeBtn = document.getElementById('telegram-return-close-error-account');
                            if (closeBtn) {
                                closeBtn.onclick = function() {
                                    modal.classList.add('hidden');
                                    // Правильно убираем параметр return из URL
                                    const url = new URL(window.location.href);
                                    url.searchParams.delete('return');
                                    const newUrl = url.search ? url.pathname + url.search : url.pathname;
                                    window.history.replaceState({}, '', newUrl || window.location.pathname);
                                };
                            }
                        }
                        
                        // Настройка ссылки для копирования
                        const browserUrlInput = document.getElementById('telegram-browser-url-account');
                        const copyUrlBtn = document.getElementById('telegram-copy-url-btn-account');
                        
                        if (openBrowserDiv && browserUrlInput) {
                            // Формируем полный URL без параметра return
                            const url = new URL(window.location.href);
                            url.searchParams.delete('return');
                            const cleanUrl = url.toString();
                            
                            // Устанавливаем значение в input
                            browserUrlInput.value = cleanUrl;
                            
                            // Обработчик кнопки копирования
                            if (copyUrlBtn) {
                                copyUrlBtn.onclick = function() {
                                    browserUrlInput.select();
                                    browserUrlInput.setSelectionRange(0, 99999); // Для мобильных устройств
                                    
                                    try {
                                        document.execCommand('copy');
                                        const originalText = copyUrlBtn.textContent;
                                        copyUrlBtn.textContent = '✓ Скопировано!';
                                        copyUrlBtn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                                        copyUrlBtn.classList.add('bg-green-500');
                                        
                                        setTimeout(() => {
                                            copyUrlBtn.textContent = originalText;
                                            copyUrlBtn.classList.remove('bg-green-500');
                                            copyUrlBtn.classList.add('bg-blue-500', 'hover:bg-blue-600');
                                        }, 2000);
                                    } catch (err) {
                                        console.error('Ошибка копирования:', err);
                                        // Fallback - показываем alert с ссылкой
                                        alert('Скопируйте эту ссылку:\n\n' + cleanUrl);
                                    }
                                };
                            }
                            
                            // Также можно кликнуть на input для копирования
                            browserUrlInput.onclick = function() {
                                this.select();
                                this.setSelectionRange(0, 99999);
                            };
                        }
                    } catch (error) {
                        console.error('Ошибка проверки статуса:', error);
                        loadingDiv.classList.add('hidden');
                        errorDiv.classList.remove('hidden');
                        openBrowserDiv.classList.remove('hidden');
                        const errorMessage = document.getElementById('telegram-return-error-message-account');
                        if (errorMessage) {
                            errorMessage.textContent = 'Ошибка при проверке статуса заказа. Попробуйте позже.';
                        }
                        
                        // Обновляем баннер с сообщением об ошибке
                        if (banner && bannerIcon && bannerText && bannerClose) {
                            banner.classList.remove('hidden');
                            banner.classList.add('bg-red-50', 'dark:bg-red-900', 'border-red-500');
                            bannerIcon.innerHTML = `
                                <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            `;
                            bannerText.textContent = '❌ Ошибка при проверке статуса заказа. Попробуйте позже.';
                            bannerText.classList.remove('text-gray-900', 'dark:text-white');
                            bannerText.classList.add('text-red-800', 'dark:text-red-100');
                            bannerClose.style.display = 'block';
                            bannerClose.onclick = function() {
                                banner.classList.add('hidden');
                                document.body.style.paddingTop = '0';
                            };
                        }
                        
                        const closeBtn = document.getElementById('telegram-return-close-error-account');
                        if (closeBtn) {
                            closeBtn.onclick = function() {
                                modal.classList.add('hidden');
                                const newUrl = window.location.pathname + window.location.search.replace(/[?&]return=true/, '').replace(/^\?/, '');
                                window.history.replaceState({}, '', newUrl || window.location.pathname);
                            };
                        }
                    }
                }
                
                // Запускаем проверку статуса
                checkVerificationStatus();
            }
            return;
        }
        
        // Для обычных браузеров - стандартная логика
        if (savedUrl) {
            // Используем BroadcastChannel для связи между вкладками
            const channel = new BroadcastChannel('verification_channel');
            
            // Отправляем сообщение существующим вкладкам о завершении верификации
            channel.postMessage({ 
                type: 'verification_completed', 
                url: savedUrl 
            });
            
            // Проверяем, есть ли уже открытая вкладка с ожиданием верификации
            const verificationInProgress = localStorage.getItem('verificationInProgress');
            if (verificationInProgress === 'true') {
                // Пытаемся закрыть текущую вкладку, если она была открыта из Telegram
                // Используем небольшую задержку, чтобы дать время существующей вкладке обновиться
                setTimeout(() => {
                    // Если вкладка была открыта не пользователем напрямую, пытаемся закрыть
                    try {
                        if (window.history.length <= 1) {
                            // Если в истории только одна страница, значит это новая вкладка
                            window.close();
                        } else {
                            // Иначе просто переходим на сохраненный URL
                            localStorage.removeItem('verificationReturnUrl');
                            window.location.replace(savedUrl);
                        }
                    } catch (e) {
                        // Если не удалось закрыть, просто переходим на сохраненный URL
                        localStorage.removeItem('verificationReturnUrl');
                        window.location.replace(savedUrl);
                    }
                }, 100);
                return;
            }
            // Если верификация не в процессе, просто переходим на сохраненный URL
            localStorage.removeItem('verificationReturnUrl');
            window.location.replace(savedUrl);
            return;
        } else {
            // Если сохраненного URL нет, просто убираем параметр return
            const newUrl = window.location.pathname + window.location.search.replace(/[?&]return=true/, '').replace(/^\?/, '');
            if (newUrl !== window.location.pathname + window.location.search) {
                window.location.replace(newUrl || window.location.pathname);
            }
        }
    }
    
    // Слушаем сообщения от других вкладок о завершении верификации
    const channel = new BroadcastChannel('verification_channel');
    channel.addEventListener('message', function(event) {
        if (event.data.type === 'verification_completed') {
            // Если это существующая вкладка с ожиданием верификации, обновляем её
            const verificationInProgress = localStorage.getItem('verificationInProgress');
            if (verificationInProgress === 'true' && event.data.url) {
                window.location.replace(event.data.url);
            }
        }
    });
})();

document.addEventListener('DOMContentLoaded', function() {
    const telegramBotLink = document.getElementById('telegram-bot-link-account');
    if (telegramBotLink) {
        telegramBotLink.addEventListener('click', async function(e) {
            e.preventDefault();
            const errorDiv = document.getElementById('verification-error-1');
            const waitingDiv = document.getElementById('waiting-for-code-account');
            
            if (!currentOrderId) {
                errorDiv.textContent = 'Ошибка: данные заказа не найдены';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            errorDiv.classList.add('hidden');
            telegramBotLink.classList.add('opacity-50', 'pointer-events-none');
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('{{ route("api.phone.verification.start") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        order_id: currentOrderId,
                    }),
                });
                
                const data = await response.json();
                
                if (data.success && data.bot_url) {
                    // Универсальная функция для открытия Telegram бота с поддержкой всех платформ
                    function openTelegramBot(botUrl) {
                        const userAgent = navigator.userAgent;
                        const isIOS = /iPhone|iPad|iPod/i.test(userAgent);
                        const isAndroid = /Android/i.test(userAgent);
                        const isMac = /Macintosh|Mac OS X/i.test(userAgent);
                        const isMobile = isIOS || isAndroid;
                        const isDesktop = !isMobile;
                        
                        const botName = botUrl.match(/t\.me\/([^?]+)/)?.[1];
                        const token = botUrl.match(/start=([^&]+)/)?.[1];
                        
                        if (!botName) {
                            // Если не удалось извлечь имя бота, используем текущую вкладку
                            window.location.href = botUrl;
                            return;
                        }
                        
                        const tgUrl = token 
                            ? `tg://resolve?domain=${botName}&start=${token}`
                            : `tg://resolve?domain=${botName}`;
                        
                        // Для всех платформ используем window.location.href для перехода на приложение
                        // Страница останется открытой в фоне, но переключится на Telegram
                        if (isAndroid || isIOS) {
                            // Для мобильных устройств используем прямой переход
                            window.location.href = tgUrl;
                            return;
                        }
                        
                        // Для десктопов и macOS используем скрытую ссылку без target="_blank"
                        try {
                            const link = document.createElement('a');
                            link.href = tgUrl;
                            // Убираем target="_blank" чтобы не открывать новую вкладку
                            link.style.display = 'none';
                            document.body.appendChild(link);
                            link.click();
                            
                            // Удаляем ссылку после небольшой задержки
                            setTimeout(() => {
                                if (document.body.contains(link)) {
                                    document.body.removeChild(link);
                                }
                            }, 100);
                            
                            // Fallback для десктопов и macOS - используем текущую вкладку
                            if (isDesktop || isMac) {
                                setTimeout(() => {
                                    // Проверяем, осталась ли страница в фокусе (значит приложение не открылось)
                                    if (document.hasFocus()) {
                                        // Используем текущую вкладку вместо новой
                                        window.location.href = botUrl;
                                    }
                                }, 500);
                            }
                        } catch (error) {
                            console.error('Ошибка при открытии Telegram через tg://:', error);
                            // В случае ошибки используем текущую вкладку
                            window.location.href = botUrl;
                        }
                    }
                    
                    // Сохраняем текущий URL для возврата на ту же страницу
                    const currentUrl = window.location.href;
                    localStorage.setItem('verificationReturnUrl', currentUrl);
                    
                    // Используем BroadcastChannel для связи между вкладками
                    const channel = new BroadcastChannel('verification_channel');
                    channel.postMessage({ type: 'verification_started', url: currentUrl });
                    
                    // Открываем бота используя универсальную функцию
                    openTelegramBot(data.bot_url);
                    
                    // Показываем индикатор ожидания
                    waitingDiv.classList.remove('hidden');
                    
                    // Сохраняем order_id для проверки при возврате на страницу
                    if (currentOrderId) {
                        localStorage.setItem('currentVerificationOrderId', currentOrderId);
                    }
                    
                    // Простая проверка статуса только при возврате на страницу
                    const checkVerificationStatus = async () => {
                        const orderId = currentOrderId || localStorage.getItem('currentVerificationOrderId');
                        if (!orderId) {
                            return;
                        }
                        
                        try {
                            const checkResponse = await fetch(`/api/phone/verification/check-status?order_id=${orderId}`, {
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });
                            
                            if (!checkResponse.ok) {
                                console.error('Ошибка проверки статуса:', checkResponse.status, checkResponse.statusText);
                                if (checkResponse.status === 404) {
                                    console.error('Маршрут не найден. Проверьте конфигурацию маршрутов на сервере.');
                                }
                                return;
                            }
                            
                            const statusData = await checkResponse.json();
                            
                            if (statusData.success && (statusData.is_verified || statusData.order_status !== 'pending_verification')) {
                                // Переходим ко второму шагу (ввод кода)
                                document.getElementById('verification-step-1').classList.add('hidden');
                                document.getElementById('verification-step-2-account').classList.remove('hidden');
                                waitingDiv.classList.add('hidden');
                                telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                                
                                // Удаляем обработчики
                                if (window.verificationVisibilityHandlerAccount) {
                                    document.removeEventListener('visibilitychange', window.verificationVisibilityHandlerAccount);
                                    window.removeEventListener('focus', window.verificationVisibilityHandlerAccount);
                                }
                            }
                        } catch (error) {
                            console.error('Ошибка проверки статуса:', error);
                        }
                    };
                    
                    // Сохраняем обработчик для возможности его удаления позже
                    window.verificationVisibilityHandlerAccount = checkVerificationStatus;
                    
                    // Проверяем статус только при возврате на страницу (без постоянного polling)
                    document.addEventListener('visibilitychange', function() {
                        if (!document.hidden) {
                            checkVerificationStatus();
                        }
                    });
                    window.addEventListener('focus', checkVerificationStatus);
                } else {
                    errorDiv.textContent = data.message || 'Ошибка при запуске верификации';
                    errorDiv.classList.remove('hidden');
                    telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                }
            } catch (error) {
                console.error('Ошибка при запуске верификации:', error);
                errorDiv.textContent = 'Ошибка при запуске верификации. Попробуйте позже.';
                errorDiv.classList.remove('hidden');
                telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
            }
        });
    }
    
    // Обработка Enter для ввода кода
    const codeInput = document.getElementById('verification_code_account');
    if (codeInput) {
        codeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                verifyCodeAccount();
            }
        });
    }
});

function showNotification(message, type = 'success') {
    // Простая реализация уведомления
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
@endsection
