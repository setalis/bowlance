@extends('layouts.frontend')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-orange-500 to-orange-600 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                    Закажите вкусную еду с доставкой
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-orange-50">
                    Более 100 ресторанов на выбор. Быстрая доставка за 30 минут.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#restaurants" class="bg-white text-orange-600 px-8 py-3 rounded-lg font-semibold hover:bg-orange-50 transition-colors shadow-lg">
                        Выбрать ресторан
                    </a>
                    <a href="#about" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-orange-600 transition-colors">
                        Узнать больше
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    @if(isset($categories) && $categories->count() > 0)
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                @foreach($categories as $category)
                <button 
                    type="button" 
                    data-modal-target="category-modal" 
                    data-modal-toggle="category-modal"
                    data-category-id="{{ $category->id }}"
                    class="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow cursor-pointer text-left w-full"
                >
                    <div class="aspect-square bg-gray-100 overflow-hidden">
                        @if($category->image)
                            <img 
                                src="{{ Storage::url($category->image) }}" 
                                alt="{{ $category->name }}" 
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-100 to-orange-200">
                                <svg class="w-16 h-16 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-4 text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1 group-hover:text-orange-600 transition-colors">
                            {{ $category->name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $category->dishes_count }} {{ $category->dishes_count === 1 ? 'блюдо' : ($category->dishes_count < 5 ? 'блюда' : 'блюд') }}
                        </p>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Modal для отображения блюд категории -->
    <div id="category-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80" data-modal-hide="category-modal"></div>
        
        <div class="relative p-4 w-full max-w-4xl max-h-full z-50">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 id="modal-category-name" class="text-xl font-semibold text-gray-900 dark:text-white">
                        Блюда категории
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="category-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <div id="modal-loading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                        <p class="mt-2 text-gray-600">Загрузка блюд...</p>
                    </div>
                    <div id="modal-content" class="hidden">
                        <div id="modal-dishes" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Блюда будут загружены через JavaScript -->
                        </div>
                    </div>
                    <div id="modal-empty" class="hidden text-center py-8">
                        <p class="text-gray-600">В этой категории пока нет блюд</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Drawer корзины -->
    <!-- Backdrop -->
    <div id="cart-drawer-backdrop" class="hidden fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 z-40" data-drawer-hide="cart-drawer"></div>
    
    <div id="cart-drawer" class="fixed top-0 right-0 z-50 h-screen w-full overflow-y-auto transition-transform -translate-x-full bg-white dark:bg-gray-800" tabindex="-1" aria-labelledby="cart-drawer-label" aria-hidden="true">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-5 flex items-center mt-4 relative">
                <h5 id="cart-drawer-label" class="inline-flex items-center text-xl font-semibold text-gray-900 dark:text-white">
                    <svg class="w-6 h-6 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10V6a3 3 0 0 1 3-3v0a3 3 0 0 1 3 3v4m3-2 .917 11.923A1 1 0 0 1 17.92 21H6.08a1 1 0 0 1-.997-1.077L6 8h12Z"/>
                    </svg>
                    Корзина
                </h5>
                <button type="button" data-drawer-hide="cart-drawer" aria-controls="cart-drawer" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg w-10 h-10 absolute top-0 end-0 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                    </svg>
                    <span class="sr-only">Close menu</span>
                </button>
            </div>
            
            <div id="cart-content" class="pb-40">
            <div id="cart-empty" class="text-center py-8">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10V6a3 3 0 0 1 3-3v0a3 3 0 0 1 3 3v4m3-2 .917 11.923A1 1 0 0 1 17.92 21H6.08a1 1 0 0 1-.997-1.077L6 8h12Z"/>
                </svg>
                <p class="text-gray-600 dark:text-gray-400">Корзина пуста</p>
            </div>
                <div id="cart-items" class="hidden space-y-4">
                    <!-- Товары будут добавлены через JavaScript -->
                </div>
            </div>
        </div>
        
        <div id="cart-footer" class="hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-lg">
            <div class="max-w-4xl mx-auto">
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Итого:</span>
                    <span id="cart-total" class="text-xl font-bold text-orange-600">0 ₾</span>
                </div>
            </div>
            <div class="flex flex-col gap-3">
                <button 
                    type="button" 
                    id="checkout-button"
                    data-modal-target="checkout-modal"
                    data-modal-toggle="checkout-modal"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold px-4 py-3 rounded-lg transition-colors"
                >
                    Оформить заказ
                </button>
                <button 
                    type="button" 
                    id="clear-cart-button"
                    class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-3 rounded-lg transition-colors"
                >
                    Очистить корзину
                </button>
                <button 
                    type="button" 
                    data-drawer-hide="cart-drawer"
                    class="w-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold px-4 py-3 rounded-lg transition-colors"
                >
                    Продолжить покупки
                </button>
            </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Modal оформления заказа -->
    <div id="checkout-modal" tabindex="-1" aria-hidden="true" aria-labelledby="checkout-modal-title" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80" data-modal-hide="checkout-modal"></div>
        
        <div class="relative p-4 w-full max-w-2xl max-h-full z-50">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 id="checkout-modal-title" class="text-xl font-semibold text-gray-900 dark:text-white">
                        Оформление заказа
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="checkout-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form id="checkout-form" class="p-4 md:p-5 space-y-4">
                    <div>
                        <label for="customer_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Имя <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="customer_name" 
                            name="customer_name" 
                            required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-orange-500 dark:focus:border-orange-500"
                            placeholder="Введите ваше имя"
                        >
                    </div>
                    <div>
                        <label for="customer_phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Телефон <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="customer_phone" 
                            name="customer_phone" 
                            required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-orange-500 dark:focus:border-orange-500"
                            placeholder="+7 (999) 123-45-67"
                        >
                    </div>
                    <div>
                        <label for="customer_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Адрес доставки
                        </label>
                        <textarea 
                            id="customer_address" 
                            name="customer_address" 
                            rows="3"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-orange-500 dark:focus:border-orange-500"
                            placeholder="Введите адрес доставки (необязательно)"
                        ></textarea>
                    </div>
                    <div id="checkout-error" class="hidden text-red-600 dark:text-red-400 text-sm"></div>
                    <!-- Modal footer -->
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button 
                            type="button" 
                            data-modal-hide="checkout-modal"
                            class="flex-1 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit" 
                            id="checkout-submit"
                            class="flex-1 text-white bg-orange-500 hover:bg-orange-600 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-orange-500 dark:hover:bg-orange-600 dark:focus:ring-orange-800"
                        >
                            Подтвердить заказ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal верификации телефона -->
    <div id="verification-modal" tabindex="-1" aria-hidden="true" aria-labelledby="verification-modal-title" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <!-- Backdrop -->
        <div id="verification-modal-backdrop" class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80"></div>
        
        <div class="relative p-4 w-full max-w-md max-h-full z-50">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 id="verification-modal-title" class="text-xl font-semibold text-gray-900 dark:text-white">
                        Подтверждение телефона
                    </h3>
                    <button type="button" id="verification-modal-close" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <div id="verification-step-1">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Для подтверждения заказа необходимо подтвердить номер телефона через Telegram. 
                            Нажмите кнопку ниже, чтобы открыть Telegram бота и получить код подтверждения.
                        </p>
                        <div id="verification-error-1" class="hidden text-red-600 dark:text-red-400 text-sm mb-4"></div>
                        <div class="flex flex-col gap-3">
                            <a 
                                id="telegram-bot-link"
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
                        <div id="waiting-for-code" class="hidden mt-4">
                            <div class="flex items-center justify-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Ожидание кода...
                            </div>
                        </div>
                    </div>
                    <div id="verification-step-2" class="hidden">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Код подтверждения отправлен в Telegram. Введите код для подтверждения заказа.
                        </p>
                        <div class="mb-4">
                            <label for="verification_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Код подтверждения <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="verification_code" 
                                name="verification_code" 
                                required
                                maxlength="6"
                                pattern="[0-9]{6}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-orange-500 dark:focus:border-orange-500 text-center text-2xl tracking-widest"
                                placeholder="000000"
                            >
                        </div>
                        <div id="verification-error-2" class="hidden text-red-600 dark:text-red-400 text-sm mb-4"></div>
                        <div class="flex gap-3">
                            <button 
                                type="button" 
                                id="back-button"
                                class="flex-1 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                            >
                                Назад
                            </button>
                            <button 
                                type="button" 
                                id="verify-code-button"
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

    <!-- Search Section -->
    <section class="py-8 bg-white shadow-md -mt-8 relative z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <form class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search" class="sr-only">Поиск ресторана или блюда</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search"
                            placeholder="Поиск ресторана или блюда..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                        >
                    </div>
                    <button 
                        type="submit"
                        class="bg-orange-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-orange-600 transition-colors whitespace-nowrap"
                    >
                        Найти
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Почему выбирают нас</h2>
                <p class="text-gray-600 text-lg">Быстро, удобно и вкусно</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Быстрая доставка</h3>
                    <p class="text-gray-600">Доставка за 30 минут или бесплатно</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Безопасная оплата</h3>
                    <p class="text-gray-600">Оплата картой или наличными при получении</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Лучшие рестораны</h3>
                    <p class="text-gray-600">Только проверенные рестораны с высоким рейтингом</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Restaurants Section -->
    <section id="restaurants" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Популярные рестораны</h2>
                <p class="text-gray-600 text-lg">Выберите ресторан и закажите любимые блюда</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Restaurant Card 1 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="h-48 bg-gradient-to-r from-orange-400 to-orange-500"></div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Итальянская кухня</h3>
                        <p class="text-gray-600 mb-4">Пицца, паста, ризотто и многое другое</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <span class="ml-1 text-gray-700 font-medium">4.8</span>
                            </div>
                            <span class="text-gray-500 text-sm">30-40 мин</span>
                        </div>
                    </div>
                </div>

                <!-- Restaurant Card 2 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="h-48 bg-gradient-to-r from-red-400 to-red-500"></div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Азиатская кухня</h3>
                        <p class="text-gray-600 mb-4">Суши, роллы, вок и традиционные блюда</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <span class="ml-1 text-gray-700 font-medium">4.9</span>
                            </div>
                            <span class="text-gray-500 text-sm">25-35 мин</span>
                        </div>
                    </div>
                </div>

                <!-- Restaurant Card 3 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="h-48 bg-gradient-to-r from-green-400 to-green-500"></div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Фастфуд</h3>
                        <p class="text-gray-600 mb-4">Бургеры, картофель фри и напитки</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <span class="ml-1 text-gray-700 font-medium">4.7</span>
                            </div>
                            <span class="text-gray-500 text-sm">20-30 мин</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">О нас</h2>
                    <p class="text-gray-600 mb-4 text-lg">
                        FoodDelivery - это сервис доставки еды, который объединяет лучшие рестораны города в одном месте. 
                        Мы работаем с проверенными партнерами, чтобы гарантировать качество и свежесть каждого блюда.
                    </p>
                    <p class="text-gray-600 mb-4 text-lg">
                        Наша миссия - сделать заказ еды максимально простым и удобным. Выбирайте из сотен ресторанов, 
                        заказывайте любимые блюда и получайте их быстро и безопасно.
                    </p>
                    <div class="flex gap-4 mt-8">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-500">100+</div>
                            <div class="text-gray-600">Ресторанов</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-500">50K+</div>
                            <div class="text-gray-600">Довольных клиентов</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-500">30</div>
                            <div class="text-gray-600">Минут доставка</div>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-orange-400 to-orange-500 rounded-lg h-96 flex items-center justify-center">
                    <svg class="w-32 h-32 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Свяжитесь с нами</h2>
                <p class="text-gray-600 text-lg">Есть вопросы? Мы всегда готовы помочь!</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-8">
                <form class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ваше имя</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                            placeholder="Введите ваше имя"
                        >
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                            placeholder="your@email.com"
                        >
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Сообщение</label>
                        <textarea 
                            id="message" 
                            name="message"
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                            placeholder="Ваше сообщение..."
                        ></textarea>
                    </div>
                    <button 
                        type="submit"
                        class="w-full bg-orange-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-orange-600 transition-colors"
                    >
                        Отправить сообщение
                    </button>
                </form>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        // Управление корзиной
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }

        function addToCart(dish) {
            const existingItem = cart.find(item => item.id === dish.id);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: dish.id,
                    name: dish.name,
                    price: parseFloat(dish.price) || 0,
                    image: dish.image || '',
                    quantity: 1
                });
            }
            saveCart();
            // Открываем drawer корзины
            window.openCartDrawer();
        }

        function updateQuantity(dishId, change) {
            const item = cart.find(item => item.id === dishId);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    cart = cart.filter(item => item.id !== dishId);
                }
                saveCart();
            }
        }

        function removeFromCart(dishId) {
            cart = cart.filter(item => item.id !== dishId);
            saveCart();
        }

        function clearCart() {
            if (cart.length === 0) {
                return;
            }
            
            if (confirm('Вы уверены, что хотите очистить корзину?')) {
                cart = [];
                saveCart();
                
                // Закрываем drawer корзины
                window.closeCartDrawer();
                
                // Перенаправляем на главную страницу
                window.location.href = '/';
            }
        }

        function getCartTotal() {
            return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        }

        // Получаем экземпляр Drawer из Flowbite
        function getCartDrawerInstance() {
            const drawerElement = document.getElementById('cart-drawer');
            if (!drawerElement) return null;
            
            // Flowbite хранит экземпляры в data-атрибуте
            if (drawerElement._flowbiteDrawer) {
                return drawerElement._flowbiteDrawer;
            }
            
            // Альтернативный способ - через Flowbite API
            if (window.Flowbite && window.Flowbite.getInstance) {
                return window.Flowbite.getInstance('drawer', 'cart-drawer');
            }
            
            return null;
        }

        window.openCartDrawer = function() {
            const drawerInstance = getCartDrawerInstance();
            const drawer = document.getElementById('cart-drawer');
            
            if (drawerInstance) {
                drawerInstance.show();
                // Убеждаемся, что aria-hidden установлен правильно
                if (drawer) {
                    drawer.setAttribute('aria-hidden', 'false');
                }
            } else {
                // Fallback - используем data-атрибут для триггера
                const triggerButton = document.querySelector('[data-drawer-toggle="cart-drawer"]');
                if (triggerButton) {
                    triggerButton.click();
                    if (drawer) {
                        drawer.setAttribute('aria-hidden', 'false');
                    }
                } else {
                    // Последний fallback - прямое управление классами
                    const backdrop = document.getElementById('cart-drawer-backdrop');
                    if (drawer) {
                        drawer.classList.remove('-translate-x-full');
                        drawer.classList.add('translate-x-0');
                        drawer.setAttribute('aria-hidden', 'false');
                        if (backdrop) {
                            backdrop.classList.remove('hidden');
                        }
                    }
                }
            }
        };

        window.closeCartDrawer = function() {
            const drawerInstance = getCartDrawerInstance();
            const drawer = document.getElementById('cart-drawer');
            
            // Убираем фокус с элементов внутри drawer перед закрытием
            if (drawer) {
                const focusedElement = drawer.querySelector(':focus');
                if (focusedElement) {
                    focusedElement.blur();
                }
            }
            
            if (drawerInstance) {
                drawerInstance.hide();
                // Устанавливаем aria-hidden после закрытия
                if (drawer) {
                    setTimeout(() => {
                        drawer.setAttribute('aria-hidden', 'true');
                    }, 100);
                }
            } else {
                // Fallback - используем data-атрибут для закрытия
                const closeButton = document.querySelector('[data-drawer-hide="cart-drawer"]');
                if (closeButton) {
                    closeButton.click();
                    if (drawer) {
                        setTimeout(() => {
                            drawer.setAttribute('aria-hidden', 'true');
                        }, 100);
                    }
                } else {
                    // Последний fallback - прямое управление классами
                    const backdrop = document.getElementById('cart-drawer-backdrop');
                    if (drawer) {
                        drawer.classList.remove('translate-x-0');
                        drawer.classList.add('-translate-x-full');
                        drawer.setAttribute('aria-hidden', 'true');
                        if (backdrop) {
                            backdrop.classList.add('hidden');
                        }
                    }
                }
            }
        };

        function updateCartDisplay() {
            const cartItems = document.getElementById('cart-items');
            const cartEmpty = document.getElementById('cart-empty');
            const cartFooter = document.getElementById('cart-footer');
            const cartTotal = document.getElementById('cart-total');
            const cartBadge = document.getElementById('cart-badge');
            const cartBadgeMobile = document.getElementById('cart-badge-mobile');
            const clearCartButton = document.getElementById('clear-cart-button');
            
            // Обновляем бейдж корзины
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            if (cartBadge) {
                if (totalItems > 0) {
                    cartBadge.textContent = totalItems;
                    cartBadge.classList.remove('hidden');
                } else {
                    cartBadge.classList.add('hidden');
                }
            }
            if (cartBadgeMobile) {
                if (totalItems > 0) {
                    cartBadgeMobile.textContent = totalItems;
                    cartBadgeMobile.classList.remove('hidden');
                } else {
                    cartBadgeMobile.classList.add('hidden');
                }
            }

            if (cart.length === 0) {
                cartItems.classList.add('hidden');
                cartEmpty.classList.remove('hidden');
                cartFooter.classList.add('hidden');
                if (clearCartButton) {
                    clearCartButton.classList.add('hidden');
                }
            } else {
                cartItems.classList.remove('hidden');
                cartEmpty.classList.add('hidden');
                cartFooter.classList.remove('hidden');
                if (clearCartButton) {
                    clearCartButton.classList.remove('hidden');
                }

                cartItems.innerHTML = cart.map(item => `
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        ${item.image ? 
                            `<img src="${item.image}" alt="${item.name}" class="w-24 h-24 md:w-32 md:h-32 object-cover rounded-lg flex-shrink-0">` :
                            `<div class="w-24 h-24 md:w-32 md:h-32 bg-gradient-to-br from-orange-100 to-orange-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-12 h-12 md:w-16 md:h-16 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>`
                        }
                        <div class="flex-1 min-w-0">
                            <h6 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white mb-2">${item.name}</h6>
                            <p class="text-sm md:text-base text-gray-600 dark:text-gray-400 mb-3">${item.price.toFixed(2)} ₾ за шт.</p>
                            <div class="flex items-center gap-3">
                                <button 
                                    type="button" 
                                    class="decrease-quantity w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded-lg text-gray-700 dark:text-gray-300"
                                    data-dish-id="${item.id}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <span class="w-12 text-center text-base font-medium text-gray-900 dark:text-white">${item.quantity}</span>
                                <button 
                                    type="button" 
                                    class="increase-quantity w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded-lg text-gray-700 dark:text-gray-300"
                                    data-dish-id="${item.id}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-2">${(item.price * item.quantity).toFixed(2)} ₾</p>
                            <button 
                                type="button" 
                                class="remove-from-cart text-red-600 hover:text-red-700 text-sm font-medium"
                                data-dish-id="${item.id}"
                            >
                                Удалить
                            </button>
                        </div>
                    </div>
                `).join('');

                cartTotal.textContent = `${getCartTotal().toFixed(2)} ₾`;

                // Добавляем обработчики событий
                document.querySelectorAll('.increase-quantity').forEach(btn => {
                    btn.addEventListener('click', function() {
                        updateQuantity(parseInt(this.getAttribute('data-dish-id')), 1);
                    });
                });

                document.querySelectorAll('.decrease-quantity').forEach(btn => {
                    btn.addEventListener('click', function() {
                        updateQuantity(parseInt(this.getAttribute('data-dish-id')), -1);
                    });
                });

                document.querySelectorAll('.remove-from-cart').forEach(btn => {
                    btn.addEventListener('click', function() {
                        removeFromCart(parseInt(this.getAttribute('data-dish-id')));
                    });
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const categoryButtons = document.querySelectorAll('[data-category-id]');
            const modal = document.getElementById('category-modal');
            const modalCategoryName = document.getElementById('modal-category-name');
            const modalLoading = document.getElementById('modal-loading');
            const modalContent = document.getElementById('modal-content');
            const modalDishes = document.getElementById('modal-dishes');
            const modalEmpty = document.getElementById('modal-empty');

            // Инициализируем отображение корзины
            updateCartDisplay();
            
            // Flowbite автоматически инициализирует drawer через data-атрибуты
            // Не нужно дополнительной инициализации

            // Обработчики открытия корзины
            const cartButton = document.getElementById('cart-button');
            const cartButtonMobile = document.getElementById('cart-button-mobile');
            if (cartButton) {
                cartButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.openCartDrawer();
                });
            }
            if (cartButtonMobile) {
                cartButtonMobile.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.openCartDrawer();
                });
            }
            
            // Также обрабатываем клики через data-атрибуты Flowbite
            document.querySelectorAll('[data-drawer-toggle="cart-drawer"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    // Flowbite обработает это автоматически, но убедимся что drawer открывается
                    setTimeout(function() {
                        const drawer = document.getElementById('cart-drawer');
                        if (drawer && drawer.classList.contains('-translate-x-full')) {
                            window.openCartDrawer();
                        }
                    }, 100);
                });
            });

            // Обработчик закрытия drawer при клике на backdrop
            const backdrop = document.getElementById('cart-drawer-backdrop');
            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    window.closeCartDrawer();
                });
            }

            // Обработчик закрытия drawer через кнопку
            document.querySelectorAll('[data-drawer-hide="cart-drawer"]').forEach(btn => {
                btn.addEventListener('click', function() {
                    window.closeCartDrawer();
                });
            });

            // Управление aria-hidden для drawer при изменениях класса
            const cartDrawer = document.getElementById('cart-drawer');
            if (cartDrawer) {
                const drawerObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const isHidden = cartDrawer.classList.contains('-translate-x-full') || 
                                           cartDrawer.classList.contains('hidden');
                            cartDrawer.setAttribute('aria-hidden', isHidden ? 'true' : 'false');
                            
                            // Если drawer закрывается, убираем фокус
                            if (isHidden) {
                                const focusedElement = cartDrawer.querySelector(':focus');
                                if (focusedElement) {
                                    focusedElement.blur();
                                }
                            }
                        }
                    });
                });
                
                drawerObserver.observe(cartDrawer, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }

            // Обработчик кнопки "Оформить заказ"
            const checkoutButton = document.getElementById('checkout-button');
            if (checkoutButton) {
                checkoutButton.addEventListener('click', function(e) {
                    if (cart.length === 0) {
                        e.preventDefault();
                        e.stopPropagation();
                        alert('Корзина пуста');
                        return false;
                    }
                    // Flowbite автоматически откроет модальное окно через data-атрибуты
                });
            }

            // Обработчик кнопки "Очистить корзину"
            const clearCartButton = document.getElementById('clear-cart-button');
            if (clearCartButton) {
                clearCartButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    clearCart();
                });
            }

            // Инициализация модального окна через Flowbite API
            if (window.Flowbite && window.Flowbite.Modal) {
                const modalElement = document.getElementById('checkout-modal');
                if (modalElement) {
                    try {
                        const modalOptions = {
                            placement: 'center',
                            backdrop: 'dynamic',
                            backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                            closable: true,
                            onShow: () => {
                                modalElement.setAttribute('aria-hidden', 'false');
                            },
                            onHide: () => {
                                modalElement.setAttribute('aria-hidden', 'true');
                                // Убираем фокус с элементов внутри модального окна
                                const focusedElement = modalElement.querySelector(':focus');
                                if (focusedElement) {
                                    focusedElement.blur();
                                }
                            },
                        };
                        new window.Flowbite.Modal(modalElement, modalOptions);
                    } catch (e) {
                        console.log('Modal already initialized or error:', e);
                    }
                }
            }

            // Управление aria-hidden при ручном открытии/закрытии (fallback)
            const checkoutModal = document.getElementById('checkout-modal');
            if (checkoutModal) {
                // Наблюдатель за изменениями класса hidden
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const isHidden = checkoutModal.classList.contains('hidden');
                            checkoutModal.setAttribute('aria-hidden', isHidden ? 'true' : 'false');
                            
                            // Если модальное окно открывается, убираем фокус с элементов, которые могут быть скрыты
                            if (!isHidden) {
                                // Убеждаемся, что фокус находится на первом интерактивном элементе
                                setTimeout(() => {
                                    const firstInput = checkoutModal.querySelector('input, textarea, button');
                                    if (firstInput && !firstInput.disabled) {
                                        firstInput.focus();
                                    }
                                }, 100);
                            }
                        }
                    });
                });
                
                observer.observe(checkoutModal, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }

            // Обработчик формы оформления заказа
            const checkoutForm = document.getElementById('checkout-form');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const submitButton = document.getElementById('checkout-submit');
                    const errorDiv = document.getElementById('checkout-error');
                    const customerName = document.getElementById('customer_name').value;
                    const customerPhone = document.getElementById('customer_phone').value;
                    const customerAddress = document.getElementById('customer_address').value;

                    if (cart.length === 0) {
                        errorDiv.textContent = 'Корзина пуста';
                        errorDiv.classList.remove('hidden');
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.textContent = 'Отправка...';
                    errorDiv.classList.add('hidden');

                    try {
                        const orderData = {
                            customer_name: customerName,
                            customer_phone: customerPhone,
                            customer_address: customerAddress || null,
                            items: cart.map(item => ({
                                dish_id: item.id,
                                dish_name: item.name,
                                price: item.price,
                                quantity: item.quantity,
                            })),
                        };

                        const response = await fetch('{{ route("api.orders.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(orderData),
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Проверяем, требуется ли верификация
                            if (data.requires_verification) {
                                // Сохраняем данные заказа для верификации
                                window.pendingOrderId = data.order_id;
                                window.pendingOrderPhone = data.phone;
                                
                                // Закрываем модальное окно оформления заказа
                                window.closeCheckoutModal();
                                
                                // Показываем модальное окно верификации
                                window.openVerificationModal();
                            } else {
                                // Очищаем корзину
                                cart = [];
                                saveCart();
                                updateCartDisplay();
                                
                                // Закрываем модальное окно и drawer
                                window.closeCheckoutModal();
                                window.closeCartDrawer();
                                
                                // Показываем сообщение об успехе
                                alert('Заказ успешно оформлен!');
                                
                                // Очищаем форму
                                checkoutForm.reset();
                            }
                        } else {
                            if (data.errors) {
                                const errorMessages = Object.values(data.errors).flat().join(', ');
                                errorDiv.textContent = errorMessages;
                            } else {
                                errorDiv.textContent = data.message || 'Ошибка при оформлении заказа';
                            }
                            errorDiv.classList.remove('hidden');
                        }
                    } catch (error) {
                        errorDiv.textContent = 'Произошла ошибка при отправке заказа';
                        errorDiv.classList.remove('hidden');
                        console.error('Ошибка:', error);
                    } finally {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Подтвердить заказ';
                    }
                });
            }

            // Функция для закрытия модального окна
            window.closeCheckoutModal = function() {
                const modal = document.getElementById('checkout-modal');
                if (modal) {
                    if (window.Flowbite && window.Flowbite.getInstance) {
                        const modalInstance = window.Flowbite.getInstance('modal', 'checkout-modal');
                        if (modalInstance) {
                            modalInstance.hide();
                        } else {
                            modal.classList.add('hidden');
                            modal.setAttribute('aria-hidden', 'true');
                            // Убираем фокус с элементов внутри модального окна
                            const focusedElement = modal.querySelector(':focus');
                            if (focusedElement) {
                                focusedElement.blur();
                            }
                        }
                    } else {
                        modal.classList.add('hidden');
                        modal.setAttribute('aria-hidden', 'true');
                        // Убираем фокус с элементов внутри модального окна
                        const focusedElement = modal.querySelector(':focus');
                        if (focusedElement) {
                            focusedElement.blur();
                        }
                    }
                }
            };

            // Функции для работы с модальным окном верификации
            window.openVerificationModal = function() {
                const modal = document.getElementById('verification-modal');
                if (modal) {
                    // Сбрасываем форму верификации
                    document.getElementById('verification-step-1').classList.remove('hidden');
                    document.getElementById('verification-step-2').classList.add('hidden');
                    document.getElementById('verification_code').value = '';
                    document.getElementById('verification-error-1').classList.add('hidden');
                    document.getElementById('verification-error-2').classList.add('hidden');
                    document.getElementById('waiting-for-code').classList.add('hidden');
                    const telegramBotLink = document.getElementById('telegram-bot-link');
                    if (telegramBotLink) {
                        telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                    }
                    
                    // Открываем модальное окно
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');
                    
                    // Устанавливаем фокус на первый интерактивный элемент
                    setTimeout(() => {
                        const firstButton = modal.querySelector('button, a');
                        if (firstButton) {
                            firstButton.focus();
                        }
                    }, 100);
                }
            };

            window.closeVerificationModal = function() {
                const modal = document.getElementById('verification-modal');
                if (modal) {
                    // Закрываем модальное окно вручную
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    
                    // Убираем фокус с элементов внутри модального окна
                    const focusedElement = modal.querySelector(':focus');
                    if (focusedElement) {
                        focusedElement.blur();
                    }
                    
                    // Сбрасываем состояние формы
                    document.getElementById('verification-step-1').classList.remove('hidden');
                    document.getElementById('verification-step-2').classList.add('hidden');
                    document.getElementById('verification_code').value = '';
                    document.getElementById('verification-error-1').classList.add('hidden');
                    document.getElementById('verification-error-2').classList.add('hidden');
                    document.getElementById('waiting-for-code').classList.add('hidden');
                    const telegramBotLink = document.getElementById('telegram-bot-link');
                    if (telegramBotLink) {
                        telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                    }
                }
            };

            // Обработчик начала верификации через Telegram
            const telegramBotLink = document.getElementById('telegram-bot-link');
            if (telegramBotLink) {
                telegramBotLink.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const errorDiv = document.getElementById('verification-error-1');
                    const waitingDiv = document.getElementById('waiting-for-code');
                    
                    if (!window.pendingOrderId) {
                        errorDiv.textContent = 'Ошибка: данные заказа не найдены';
                        errorDiv.classList.remove('hidden');
                        return;
                    }

                    errorDiv.classList.add('hidden');
                    telegramBotLink.classList.add('opacity-50', 'pointer-events-none');

                    try {
                        const response = await fetch('{{ route("api.phone.verification.start") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                order_id: window.pendingOrderId,
                            }),
                        });

                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            errorDiv.textContent = 'Ошибка сервера. Проверьте настройки Telegram бота в .env файле.';
                            errorDiv.classList.remove('hidden');
                            telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                            console.error('Ошибка парсинга JSON:', e);
                            return;
                        }

                        if (response.ok && data.success) {
                            // Сохраняем токен для проверки статуса
                            window.verificationToken = data.verification_token;
                            
                            // Открываем Telegram бота
                            window.open(data.bot_url, '_blank');
                            
                            // Показываем индикатор ожидания
                            waitingDiv.classList.remove('hidden');
                            
                            // Начинаем проверку статуса верификации (polling)
                            const checkInterval = setInterval(async function() {
                                try {
                                    // Проверяем, был ли отправлен код (проверяем наличие telegram_chat_id через проверку заказа)
                                    const checkResponse = await fetch(`/api/phone/verification/check-status?order_id=${window.pendingOrderId}`, {
                                        headers: {
                                            'Accept': 'application/json',
                                        },
                                    });
                                    
                                    // Если код отправлен, переходим ко второму шагу
                                    // Для простоты, просто ждем 3 секунды после открытия бота
                                    setTimeout(function() {
                                        clearInterval(checkInterval);
                                        waitingDiv.classList.add('hidden');
                                        document.getElementById('verification-step-1').classList.add('hidden');
                                        document.getElementById('verification-step-2').classList.remove('hidden');
                                        document.getElementById('verification_code').focus();
                                    }, 3000);
                                } catch (error) {
                                    console.error('Ошибка проверки статуса:', error);
                                }
                            }, 2000);
                            
                            // Останавливаем проверку через 60 секунд
                            setTimeout(function() {
                                clearInterval(checkInterval);
                                waitingDiv.classList.add('hidden');
                                telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                            }, 60000);
                        } else {
                            const errorMessage = data.message || 'Ошибка при создании верификации';
                            errorDiv.textContent = errorMessage;
                            errorDiv.classList.remove('hidden');
                            telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                            console.error('Ошибка API:', data);
                        }
                    } catch (error) {
                        errorDiv.textContent = 'Произошла ошибка при подключении к серверу. Проверьте настройки Telegram бота в .env файле (TELEGRAM_BOT_TOKEN и TELEGRAM_BOT_USERNAME).';
                        errorDiv.classList.remove('hidden');
                        telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                        console.error('Ошибка сети:', error);
                    }
                });
            }

            // Обработчик кнопки "Назад"
            const backButton = document.getElementById('back-button');
            if (backButton) {
                backButton.addEventListener('click', function() {
                    document.getElementById('verification-step-2').classList.add('hidden');
                    document.getElementById('verification-step-1').classList.remove('hidden');
                    document.getElementById('verification_code').value = '';
                    document.getElementById('verification-error-2').classList.add('hidden');
                });
            }

            // Обработчик проверки кода верификации
            const verifyCodeButton = document.getElementById('verify-code-button');
            if (verifyCodeButton) {
                verifyCodeButton.addEventListener('click', async function() {
                    const code = document.getElementById('verification_code').value;
                    const errorDiv = document.getElementById('verification-error-2');
                    
                    if (!code || code.length !== 6) {
                        errorDiv.textContent = 'Введите 6-значный код';
                        errorDiv.classList.remove('hidden');
                        return;
                    }

                    if (!window.pendingOrderId) {
                        errorDiv.textContent = 'Ошибка: данные заказа не найдены';
                        errorDiv.classList.remove('hidden');
                        return;
                    }

                    verifyCodeButton.disabled = true;
                    verifyCodeButton.textContent = 'Проверка...';
                    errorDiv.classList.add('hidden');

                    try {
                        const response = await fetch('{{ route("api.phone.verification.verify") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                order_id: window.pendingOrderId,
                                code: code,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            // Очищаем корзину
                            cart = [];
                            saveCart();
                            updateCartDisplay();
                            
                            // Закрываем модальное окно верификации и drawer
                            window.closeVerificationModal();
                            window.closeCartDrawer();
                            
                            // Показываем сообщение об успехе
                            alert('Заказ успешно подтвержден и принят!');
                            
                            // Очищаем форму
                            checkoutForm.reset();
                            
                            // Очищаем данные заказа
                            window.pendingOrderId = null;
                            window.pendingOrderPhone = null;
                        } else {
                            errorDiv.textContent = data.message || 'Неверный код. Попробуйте еще раз.';
                            errorDiv.classList.remove('hidden');
                        }
                    } catch (error) {
                        errorDiv.textContent = 'Произошла ошибка при проверке кода';
                        errorDiv.classList.remove('hidden');
                        console.error('Ошибка:', error);
                    } finally {
                        verifyCodeButton.disabled = false;
                        verifyCodeButton.textContent = 'Подтвердить';
                    }
                });
            }

            // Обработчики закрытия модального окна верификации
            function setupVerificationModalHandlers() {
                // Обработчик клика на кнопку закрытия
                const closeButton = document.getElementById('verification-modal-close');
                if (closeButton) {
                    closeButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        window.closeVerificationModal();
                    });
                }

                // Обработчик клика на backdrop
                const backdrop = document.getElementById('verification-modal-backdrop');
                if (backdrop) {
                    backdrop.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        window.closeVerificationModal();
                    });
                }

                // Обработчик клавиши Escape (добавляем один раз)
                if (!window.verificationModalEscapeHandler) {
                    window.verificationModalEscapeHandler = function(e) {
                        const modal = document.getElementById('verification-modal');
                        if (modal && e.key === 'Escape' && !modal.classList.contains('hidden')) {
                            window.closeVerificationModal();
                        }
                    };
                    document.addEventListener('keydown', window.verificationModalEscapeHandler);
                }
            }

            // Настраиваем обработчики после загрузки DOM
            setupVerificationModalHandlers();

            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category-id');
                    loadCategoryDishes(categoryId);
                });
            });

            function loadCategoryDishes(categoryId) {
                // Показываем загрузку
                modalLoading.classList.remove('hidden');
                modalContent.classList.add('hidden');
                modalEmpty.classList.add('hidden');
                modalDishes.innerHTML = '';

                fetch(`/api/categories/${categoryId}/dishes`)
                    .then(response => response.json())
                    .then(data => {
                        modalCategoryName.textContent = data.category.name;
                        
                        if (data.dishes.length === 0) {
                            modalLoading.classList.add('hidden');
                            modalEmpty.classList.remove('hidden');
                            return;
                        }

                        // Очищаем контейнер
                        modalDishes.innerHTML = '';

                        // Добавляем блюда
                        data.dishes.forEach(dish => {
                            const dishCard = createDishCard(dish);
                            modalDishes.appendChild(dishCard);
                        });

                        // Добавляем обработчики для кнопок "Заказать"
                        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const dish = {
                                    id: parseInt(this.getAttribute('data-dish-id')),
                                    name: this.getAttribute('data-dish-name'),
                                    price: parseFloat(this.getAttribute('data-dish-price')),
                                    image: this.getAttribute('data-dish-image')
                                };
                                addToCart(dish);
                            });
                        });

                        modalLoading.classList.add('hidden');
                        modalContent.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Ошибка загрузки блюд:', error);
                        modalLoading.classList.add('hidden');
                        modalEmpty.classList.remove('hidden');
                        modalEmpty.innerHTML = '<p class="text-red-600">Ошибка загрузки блюд</p>';
                    });
            }

            function createDishCard(dish) {
                const card = document.createElement('div');
                card.className = 'bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow';
                
                let imageHtml = '';
                if (dish.image) {
                    imageHtml = `<img src="${dish.image}" alt="${dish.name}" class="w-full h-48 object-cover rounded-lg mb-3">`;
                } else {
                    imageHtml = `<div class="w-full h-48 bg-gradient-to-br from-orange-100 to-orange-200 rounded-lg mb-3 flex items-center justify-center">
                        <svg class="w-16 h-16 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>`;
                }

                let nutritionHtml = '';
                if (dish.calories || dish.proteins || dish.fats || dish.carbohydrates || dish.fiber) {
                    nutritionHtml = '<div class="flex flex-row gap-2 mt-2 text-xs text-gray-500 space-y-1">';
                    if (dish.calories) nutritionHtml += `<div>К: ${dish.calories} ккал</div>`;
                    if (dish.proteins) nutritionHtml += `<div>Б: ${parseFloat(dish.proteins).toFixed(1)} г</div>`;
                    if (dish.fats) nutritionHtml += `<div>Ж: ${parseFloat(dish.fats).toFixed(1)} г</div>`;
                    if (dish.carbohydrates) nutritionHtml += `<div>У: ${parseFloat(dish.carbohydrates).toFixed(1)} г</div>`;
                    if (dish.fiber) nutritionHtml += `<div>Кл: ${parseFloat(dish.fiber).toFixed(1)} г</div>`;
                    nutritionHtml += '</div>';
                }

                card.innerHTML = `
                    ${imageHtml}
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">${dish.name}</h4>
                    ${dish.description ? `<p class="text-sm text-gray-600 mb-2">${dish.description}</p>` : ''}
                    <div class="flex flex-row gap-4 mb-2">
                        ${dish.weight_volume ? `<p class="text-sm text-gray-700"><span class="font-medium">Вес:</span> ${dish.weight_volume}</p>` : ''}
                        ${dish.calories ? `<p class="text-sm text-gray-700"><span class="font-medium">Калории:</span> ${dish.calories} ккал</p>` : ''}
                    </div>
                    ${nutritionHtml}
                    
                    <div class="mt-3 flex items-center justify-between">
                        ${dish.price ? `<div class="text-lg font-bold text-orange-600">${parseFloat(dish.price).toFixed(2)} ₾</div>` : '<div></div>'}
                        <button 
                            type="button" 
                            class="add-to-cart-btn bg-orange-500 hover:bg-orange-600 text-white font-semibold px-4 py-2 rounded-lg transition-colors"
                            data-dish-id="${dish.id}"
                            data-dish-name="${dish.name}"
                            data-dish-price="${dish.price || 0}"
                            data-dish-image="${dish.image || ''}"
                        >
                            Заказать
                        </button>
                    </div>
                `;

                return card;
            }
        });
    </script>
    @endpush
@endsection

