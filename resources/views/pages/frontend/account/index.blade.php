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

// Инициализация обработчика для кнопки Telegram
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
                            // Если не удалось извлечь имя бота, открываем https:// ссылку
                            window.open(botUrl, '_blank');
                            return;
                        }
                        
                        const tgUrl = token 
                            ? `tg://resolve?domain=${botName}&start=${token}`
                            : `tg://resolve?domain=${botName}`;
                        
                        // Для Android используем window.location.href (уже работает)
                        if (isAndroid) {
                            window.location.href = tgUrl;
                            return;
                        }
                        
                        // Для iOS, macOS и десктопов используем скрытую ссылку с кликом
                        try {
                            const link = document.createElement('a');
                            link.href = tgUrl;
                            link.target = '_blank';
                            link.style.display = 'none';
                            document.body.appendChild(link);
                            link.click();
                            
                            // Удаляем ссылку после небольшой задержки
                            setTimeout(() => {
                                if (document.body.contains(link)) {
                                    document.body.removeChild(link);
                                }
                            }, 100);
                            
                            // Fallback для десктопов и macOS
                            if (isDesktop || isMac) {
                                setTimeout(() => {
                                    // Проверяем, осталась ли страница в фокусе (значит приложение не открылось)
                                    if (document.hasFocus()) {
                                        window.open(botUrl, '_blank');
                                    }
                                }, 500);
                            }
                            
                            // Fallback для iOS
                            if (isIOS) {
                                setTimeout(() => {
                                    // Проверяем, осталась ли страница в фокусе (значит приложение не открылось)
                                    if (document.hasFocus()) {
                                        window.open(botUrl, '_blank');
                                    }
                                }, 1000);
                            }
                        } catch (error) {
                            console.error('Ошибка при открытии Telegram через tg://:', error);
                            // В случае ошибки открываем через https://
                            window.open(botUrl, '_blank');
                        }
                    }
                    
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
