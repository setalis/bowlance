@extends('layouts.frontend')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- Контейнер для уведомлений -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 max-w-md w-full space-y-2" style="display: none;"></div>
    
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

    <!-- Constructor Section -->
    <section class="py-20 bg-gray-50" x-data="constructorData()" x-init="loadCategories()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Соберите свой боул
                </h2>
                <p class="text-lg text-gray-600">
                    Выберите продукты из категорий по желанию и создайте идеальное блюдо
                </p>
            </div>

            <!-- Categories Rows -->
            <div class="space-y-6 mb-8">
                <template x-for="category in categories" :key="category.id">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <!-- Заголовок категории -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900" x-text="category.name"></h3>
                            <button 
                                @click="openCategoryModal(category)"
                                class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Выбрать
                            </button>
                        </div>
                        
                        <!-- Список выбранных продуктов -->
                        <template x-if="hasSelectedProducts(category.id)">
                            <div class="space-y-3">
                                <template x-for="product in getSelectedProducts(category.id)" :key="product.id">
                                    <div class="flex gap-4 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <!-- Изображение -->
                                        <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden">
                                            <template x-if="product.image">
                                                <img :src="product.image" :alt="product.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!product.image">
                                                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                        
                                        <!-- Информация о продукте -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-semibold text-gray-900 mb-1" x-text="product.name"></h4>
                                                    <p class="text-sm text-gray-600 mb-2 line-clamp-2" x-text="product.description || ''"></p>
                                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                                        <template x-if="product.weight_volume">
                                                            <span x-text="product.weight_volume"></span>
                                                        </template>
                                                        <div class="flex items-center gap-2">
                                                            <template x-if="product.calories !== null && product.calories !== undefined">
                                                                <span>К: <span class="font-semibold text-gray-900" x-text="Math.round(product.calories)"></span></span>
                                                            </template>
                                                            <template x-if="product.proteins !== null && product.proteins !== undefined">
                                                                <span>Б: <span class="font-semibold text-gray-900" x-text="parseFloat(product.proteins || 0).toFixed(1) + ' г'"></span></span>
                                                            </template>
                                                            <template x-if="product.fats !== null && product.fats !== undefined">
                                                                <span>Ж: <span class="font-semibold text-gray-900" x-text="parseFloat(product.fats || 0).toFixed(1) + ' г'"></span></span>
                                                            </template>
                                                            <template x-if="product.carbohydrates !== null && product.carbohydrates !== undefined">
                                                                <span>У: <span class="font-semibold text-gray-900" x-text="parseFloat(product.carbohydrates || 0).toFixed(1) + ' г'"></span></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-lg font-bold text-orange-600 whitespace-nowrap" x-text="product.price.toFixed(2) + ' ₾'"></span>
                                                    <button 
                                                        @click="removeProduct(category.id, product.id)"
                                                        class="text-red-500 hover:text-red-700 p-1 rounded transition-colors"
                                                        title="Удалить"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <!-- Если продуктов не выбрано -->
                        <template x-if="!hasSelectedProducts(category.id)">
                            <button 
                                @click="openCategoryModal(category)"
                                class="w-full p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors text-center group"
                            >
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-white/30 backdrop-blur-sm backdrop-saturate-150 border border-white/20 rounded-lg flex items-center justify-center mb-3 group-hover:bg-orange-100/30 transition-colors">
                                        <svg class="w-8 h-8 text-gray-400 group-hover:text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                    <span class="text-gray-500 group-hover:text-orange-600 font-medium">Выберите продукты из категории</span>
                                </div>
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Итого и кнопка -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex-1 text-center md:text-left">
                        <div class="text-sm text-gray-600 mb-2">Стоимость всего блюда</div>
                        <div class="text-3xl font-bold text-orange-600 mb-4" x-text="totalPrice.toFixed(2) + ' ₾'"></div>
                        
                        <!-- КБЖУ -->
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <div class="flex items-center gap-1">
                                <span class="text-gray-600">К:</span>
                                <span class="font-semibold text-gray-900" x-text="Math.round(totalCalories)"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-gray-600">Б:</span>
                                <span class="font-semibold text-gray-900" x-text="totalProteins.toFixed(1) + ' г'"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-gray-600">Ж:</span>
                                <span class="font-semibold text-gray-900" x-text="totalFats.toFixed(1) + ' г'"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-gray-600">У:</span>
                                <span class="font-semibold text-gray-900" x-text="totalCarbohydrates.toFixed(1) + ' г'"></span>
                            </div>
                        </div>
                    </div>
                    <button
                        @click="addToCart()"
                        :disabled="!hasAnyProducts()"
                        :class="hasAnyProducts() ? 'bg-orange-500 hover:bg-orange-600' : 'bg-gray-300 cursor-not-allowed'"
                        class="w-full md:w-auto text-white font-semibold px-8 py-3 rounded-lg transition-colors"
                    >
                        Добавить в заказ
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal для выбора продуктов из категории -->
    <div id="category-products-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80" @click="document.getElementById('category-products-modal').classList.add('hidden'); document.body.style.overflow = '';"></div>
        
        <div class="relative p-4 w-full max-w-4xl h-full md:h-auto z-50">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800" 
                 x-data="{ 
                     selectedProductIds: [],
                     init() {
                         this.selectedProductIds = $store.constructor.selectedProductIds || [];
                     }
                 }"
                 x-effect="selectedProductIds = $store.constructor.selectedProductIds || []">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="$store.constructor.currentCategory?.name || 'Выберите продукты'"></h3>
                    <button type="button" @click="document.getElementById('category-products-modal').classList.add('hidden'); document.body.style.overflow = '';" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <template x-for="product in ($store.constructor.currentCategory?.products || [])" :key="product.id">
                            <button
                                @click="selectedProductIds.includes(product.id) ? selectedProductIds = selectedProductIds.filter(id => id !== product.id) : selectedProductIds.push(product.id)"
                                :class="selectedProductIds.includes(product.id) ? 'ring-2 ring-orange-500 bg-orange-50' : 'bg-white hover:bg-gray-50'"
                                class="p-4 rounded-lg border border-gray-200 text-left transition-all relative"
                            >
                                <div class="absolute top-2 right-2">
                                    <template x-if="selectedProductIds.includes(product.id)">
                                        <div class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                                <div class="aspect-square bg-gray-100 rounded-lg mb-3 overflow-hidden">
                                    <template x-if="product.image">
                                        <img :src="product.image" :alt="product.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image">
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-100 to-orange-200">
                                            <svg class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-1" x-text="product.name"></h4>
                                <p class="text-sm text-gray-600 mb-1 line-clamp-2" x-text="product.description || ''"></p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                                    <template x-if="product.weight_volume">
                                        <span x-text="product.weight_volume"></span>
                                    </template>
                                    <template x-if="product.calories">
                                        <span>К: <span class="font-semibold text-gray-900" x-text="product.calories"></span></span>
                                    </template>
                                    <template x-if="product.proteins !== null && product.proteins !== undefined">
                                        <span>Б: <span class="font-semibold text-gray-900" x-text="parseFloat(product.proteins || 0).toFixed(1) + ' г'"></span></span>
                                    </template>
                                    <template x-if="product.fats !== null && product.fats !== undefined">
                                        <span>Ж: <span class="font-semibold text-gray-900" x-text="parseFloat(product.fats || 0).toFixed(1) + ' г'"></span></span>
                                    </template>
                                    <template x-if="product.carbohydrates !== null && product.carbohydrates !== undefined">
                                        <span>У: <span class="font-semibold text-gray-900" x-text="parseFloat(product.carbohydrates || 0).toFixed(1) + ' г'"></span></span>
                                    </template>
                                </div>
                                <p class="text-lg font-bold text-orange-600" x-text="product.price + ' ₾'"></p>
                            </button>
                        </template>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center gap-3 p-4 md:p-5 border-t border-gray-200 dark:border-gray-600">
                    <button 
                        type="button" 
                        @click="document.getElementById('category-products-modal').classList.add('hidden'); document.body.style.overflow = '';"
                        class="flex-1 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                    >
                        Закрыть
                    </button>
                    <button 
                        type="button"
                        @click="if (window.constructorInstance && $store.constructor.currentCategory) { $store.constructor.selectedProductIds = selectedProductIds; window.constructorInstance.applySelectedProducts($store.constructor.currentCategory.id); document.getElementById('category-products-modal').classList.add('hidden'); document.body.style.overflow = ''; }"
                        :disabled="selectedProductIds.length === 0"
                        :class="selectedProductIds.length === 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-orange-500 hover:bg-orange-600'"
                        class="flex-1 text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors focus:ring-4 focus:outline-none focus:ring-orange-300 dark:focus:ring-orange-800"
                    >
                        Применить (<span x-text="selectedProductIds.length"></span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                    <!-- Переключатель типа доставки -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Тип получения <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="delivery_type" 
                                    value="pickup" 
                                    id="delivery_type_pickup"
                                    checked
                                    class="w-4 h-4 text-orange-500 bg-gray-100 border-gray-300 focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                >
                                <span class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Самовывоз</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="delivery_type" 
                                    value="delivery" 
                                    id="delivery_type_delivery"
                                    class="w-4 h-4 text-orange-500 bg-gray-100 border-gray-300 focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                >
                                <span class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Доставка</span>
                            </label>
                        </div>
                    </div>
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
                    <div id="address-field-container" class="hidden">
                        <label for="customer_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Адрес доставки <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="customer_address" 
                            name="customer_address" 
                            rows="3"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-orange-500 dark:focus:border-orange-500"
                            placeholder="Введите адрес доставки"
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
                            id="telegram-return-close-success"
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
                        <p id="telegram-return-error-message" class="text-sm text-gray-600 dark:text-gray-400 mb-4"></p>
                        <button 
                            type="button" 
                            id="telegram-return-close-error"
                            class="w-full text-white bg-red-500 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800"
                        >
                            Закрыть
                        </button>
                    </div>
                    <div id="telegram-return-open-browser" class="hidden mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center mb-3">
                            Для лучшего опыта откройте эту страницу в обычном браузере
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                                <input 
                                    type="text" 
                                    id="telegram-browser-url"
                                    readonly
                                    class="flex-1 text-xs bg-transparent border-none text-gray-700 dark:text-gray-300 focus:outline-none"
                                    value=""
                                />
                                <button 
                                    type="button" 
                                    id="telegram-copy-url-btn"
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
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-orange-500 dark:focus:border-orange-500 text-center tracking-widest"
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
        // Alpine.js store для конструктора
        document.addEventListener('alpine:init', () => {
            Alpine.store('constructor', {
                currentCategory: null,
                currentCategoryProduct: null,
                selectedProductIds: [],
            });
        });

        // Функция для конструктора (Alpine.js)
        function constructorData() {
            return {
                categories: [],
                selectedProducts: {}, // Массив продуктов на категорию: { categoryId: [product1, product2, ...] }
                totalPrice: 0,
                totalCalories: 0,
                totalProteins: 0,
                totalFats: 0,
                totalCarbohydrates: 0,
                
                async loadCategories() {
                    try {
                        const response = await fetch('{{ route("api.constructor.categories") }}');
                        const data = await response.json();
                        this.categories = data.categories;
                        this.calculateTotal();
                    } catch (error) {
                        console.error('Ошибка загрузки категорий:', error);
                    }
                },
                
                getSelectedProducts(categoryId) {
                    return this.selectedProducts[categoryId] || [];
                },
                
                hasSelectedProducts(categoryId) {
                    return this.getSelectedProducts(categoryId).length > 0;
                },
                
                isProductSelected(categoryId, productId) {
                    return this.getSelectedProducts(categoryId).some(p => p.id === productId);
                },
                
                removeProduct(categoryId, productId) {
                    if (!this.selectedProducts[categoryId]) {
                        return;
                    }
                    this.selectedProducts[categoryId] = this.selectedProducts[categoryId].filter(p => p.id !== productId);
                    if (this.selectedProducts[categoryId].length === 0) {
                        delete this.selectedProducts[categoryId];
                    }
                    this.calculateTotal();
                },
                
                openCategoryModal(category) {
                    Alpine.store('constructor').currentCategory = category;
                    Alpine.store('constructor').selectedProductIds = (this.selectedProducts[category.id] || []).map(p => p.id);
                    window.constructorInstance = this;
                    
                    // Открываем модальное окно напрямую
                    const modal = document.getElementById('category-products-modal');
                    if (modal) {
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                },
                
                toggleProduct(categoryId, product) {
                    if (!this.selectedProducts[categoryId]) {
                        this.selectedProducts[categoryId] = [];
                    }
                    
                    const index = this.selectedProducts[categoryId].findIndex(p => p.id === product.id);
                    
                    if (index > -1) {
                        // Удаляем продукт, если он уже выбран
                        this.selectedProducts[categoryId].splice(index, 1);
                        if (this.selectedProducts[categoryId].length === 0) {
                            delete this.selectedProducts[categoryId];
                        }
                    } else {
                        // Добавляем продукт
                        this.selectedProducts[categoryId].push({
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price),
                            image: product.image || null,
                            description: product.description || null,
                            weight_volume: product.weight_volume || null,
                            calories: product.calories !== null && product.calories !== undefined ? product.calories : 0,
                            proteins: product.proteins !== null && product.proteins !== undefined ? parseFloat(product.proteins) : 0,
                            fats: product.fats !== null && product.fats !== undefined ? parseFloat(product.fats) : 0,
                            carbohydrates: product.carbohydrates !== null && product.carbohydrates !== undefined ? parseFloat(product.carbohydrates) : 0,
                            fiber: product.fiber || 0,
                            categoryId: categoryId,
                        });
                    }
                    this.calculateTotal();
                },
                
                applySelectedProducts(categoryId) {
                    // Обновляем выбранные продукты из store (используется после закрытия модального окна)
                    const selectedIds = Alpine.store('constructor').selectedProductIds || [];
                    const category = this.categories.find(c => c.id === categoryId);
                    if (!category) return;
                    
                    this.selectedProducts[categoryId] = [];
                    selectedIds.forEach(productId => {
                        const product = category.products.find(p => p.id === productId);
                        if (product) {
                            this.selectedProducts[categoryId].push({
                                id: product.id,
                                name: product.name,
                                price: parseFloat(product.price),
                                image: product.image || null,
                                description: product.description || null,
                                weight_volume: product.weight_volume || null,
                                calories: product.calories !== null && product.calories !== undefined ? product.calories : 0,
                                proteins: product.proteins !== null && product.proteins !== undefined ? parseFloat(product.proteins) : 0,
                                fats: product.fats !== null && product.fats !== undefined ? parseFloat(product.fats) : 0,
                                carbohydrates: product.carbohydrates !== null && product.carbohydrates !== undefined ? parseFloat(product.carbohydrates) : 0,
                                fiber: product.fiber || 0,
                                categoryId: categoryId,
                            });
                        }
                    });
                    
                    if (this.selectedProducts[categoryId].length === 0) {
                        delete this.selectedProducts[categoryId];
                    }
                    this.calculateTotal();
                },
                
                calculateTotal() {
                    let price = 0;
                    let calories = 0;
                    let proteins = 0;
                    let fats = 0;
                    let carbohydrates = 0;
                    
                    Object.values(this.selectedProducts).forEach(products => {
                        products.forEach(product => {
                            price += product.price || 0;
                            calories += product.calories || 0;
                            proteins += product.proteins || 0;
                            fats += product.fats || 0;
                            carbohydrates += product.carbohydrates || 0;
                        });
                    });
                    
                    this.totalPrice = price;
                    this.totalCalories = calories;
                    this.totalProteins = proteins;
                    this.totalFats = fats;
                    this.totalCarbohydrates = carbohydrates;
                },
                
                hasAnyProducts() {
                    return Object.values(this.selectedProducts).some(products => 
                        products && products.length > 0
                    );
                },
                
                addToCart() {
                    // Проверяем, что есть хотя бы один выбранный продукт
                    if (!this.hasAnyProducts()) {
                        return;
                    }
                    
                    // Формируем данные конструктора
                    const constructorData = {
                        type: 'constructor',
                        categories: {},
                        total_price: this.totalPrice,
                    };
                    
                    this.categories.forEach(category => {
                        const products = this.selectedProducts[category.id];
                        if (products && products.length > 0) {
                            constructorData.categories[category.id] = {
                                category_name: category.name,
                                products: products.map(product => ({
                                    product_id: product.id,
                                    product_name: product.name,
                                    price: product.price,
                                    calories: product.calories,
                                    proteins: product.proteins,
                                    fats: product.fats,
                                    carbohydrates: product.carbohydrates,
                                    fiber: product.fiber,
                                    weight_volume: product.weight_volume,
                                })),
                            };
                        }
                    });
                    
                    // Создаем уникальный ID для конструктора (на основе выбранных продуктов)
                    // Если нет выбранных продуктов, используем timestamp для уникальности
                    const allProductIds = Object.values(this.selectedProducts)
                        .flat()
                        .map(p => p.id)
                        .sort();
                    const constructorId = allProductIds.length > 0 
                        ? 'constructor_' + allProductIds.join('_')
                        : 'constructor_' + Date.now();
                    
                    // Добавляем в корзину
                    const cartItem = {
                        id: constructorId,
                        name: 'Боул (конструктор)',
                        price: this.totalPrice,
                        image: '',
                        quantity: 1,
                        isConstructor: true,
                        constructorData: constructorData,
                    };
                    
                    // Проверяем, есть ли уже такой конструктор в корзине
                    const existingItem = cart.find(item => 
                        item.isConstructor && 
                        JSON.stringify(item.constructorData) === JSON.stringify(constructorData)
                    );
                    
                    if (existingItem) {
                        existingItem.quantity += 1;
                    } else {
                        cart.push(cartItem);
                    }
                    
                    saveCart();
                    window.openCartDrawer();
                    
                    // Сбрасываем выбор
                    this.selectedProducts = {};
                    this.totalPrice = 0;
                    this.totalCalories = 0;
                    this.totalProteins = 0;
                    this.totalFats = 0;
                    this.totalCarbohydrates = 0;
                }
            };
        }
        
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

        function updateQuantity(itemId, change) {
            const item = cart.find(item => item.id === itemId);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    cart = cart.filter(item => item.id !== itemId);
                }
                saveCart();
            }
        }

        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
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
            } else {
                // Fallback - используем data-атрибут для триггера
                const triggerButton = document.querySelector('[data-drawer-toggle="cart-drawer"]');
                if (triggerButton) {
                    triggerButton.click();
                } else {
                    // Последний fallback - прямое управление классами
                    const backdrop = document.getElementById('cart-drawer-backdrop');
                    if (drawer) {
                        drawer.classList.remove('-translate-x-full');
                        drawer.classList.add('translate-x-0');
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
            } else {
                // Fallback - используем data-атрибут для закрытия
                const closeButton = document.querySelector('[data-drawer-hide="cart-drawer"]');
                if (closeButton) {
                    closeButton.click();
                } else {
                    // Последний fallback - прямое управление классами
                    const backdrop = document.getElementById('cart-drawer-backdrop');
                    if (drawer) {
                        drawer.classList.remove('translate-x-0');
                        drawer.classList.add('-translate-x-full');
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

                cartItems.innerHTML = cart.map(item => {
                    let itemDetails = '';
                    
                    // Если это конструктор, показываем состав
                    if (item.isConstructor && item.constructorData && item.constructorData.categories) {
                        const categories = Object.values(item.constructorData.categories);
                        itemDetails = `
                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                ${categories.map(cat => {
                                    // Поддержка как старого формата (один продукт), так и нового (массив продуктов)
                                    if (cat.products && Array.isArray(cat.products)) {
                                        // Новый формат - массив продуктов
                                        return cat.products.map(product => `
                                            <div>• ${cat.category_name}: ${product.product_name} (${product.price.toFixed(2)} ₾)</div>
                                        `).join('');
                                    } else if (cat.product_name) {
                                        // Старый формат - один продукт (для обратной совместимости)
                                        return `<div>• ${cat.category_name}: ${cat.product_name} (${cat.price.toFixed(2)} ₾)</div>`;
                                    }
                                    return '';
                                }).join('')}
                            </div>
                        `;
                    }
                    
                    return `
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
                            ${itemDetails}
                            <p class="text-sm md:text-base text-gray-600 dark:text-gray-400 mb-3 mt-2">${item.price.toFixed(2)} ₾ за шт.</p>
                            <div class="flex items-center gap-3">
                                <button 
                                    type="button" 
                                    class="decrease-quantity w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded-lg text-gray-700 dark:text-gray-300"
                                    data-item-id="${item.id}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <span class="w-12 text-center text-base font-medium text-gray-900 dark:text-white">${item.quantity}</span>
                                <button 
                                    type="button" 
                                    class="increase-quantity w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded-lg text-gray-700 dark:text-gray-300"
                                    data-item-id="${item.id}"
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
                                data-item-id="${item.id}"
                            >
                                Удалить
                            </button>
                        </div>
                    </div>
                `;
                }).join('');

                cartTotal.textContent = `${getCartTotal().toFixed(2)} ₾`;

                // Добавляем обработчики событий
                document.querySelectorAll('.increase-quantity').forEach(btn => {
                    btn.addEventListener('click', function() {
                        updateQuantity(this.getAttribute('data-item-id'), 1);
                    });
                });

                document.querySelectorAll('.decrease-quantity').forEach(btn => {
                    btn.addEventListener('click', function() {
                        updateQuantity(this.getAttribute('data-item-id'), -1);
                    });
                });

                document.querySelectorAll('.remove-from-cart').forEach(btn => {
                    btn.addEventListener('click', function() {
                        removeFromCart(this.getAttribute('data-item-id'));
                    });
                });
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

        // Обработка возврата на ту же страницу после верификации
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const orderIdFromUrl = urlParams.get('order_id');
            
            // Если есть order_id в URL, проверяем статус заказа
            if (orderIdFromUrl) {
                console.log('Обнаружен order_id в URL:', orderIdFromUrl);
                // Сохраняем orderId в localStorage
                if (!localStorage.getItem('currentVerificationOrderId')) {
                    localStorage.setItem('currentVerificationOrderId', orderIdFromUrl);
                }
                
                // Показываем баннер и проверяем статус
                const banner = document.getElementById('telegram-status-banner');
                const bannerIcon = document.getElementById('telegram-status-icon');
                const bannerText = document.getElementById('telegram-status-text');
                const bannerClose = document.getElementById('telegram-status-close');
                
                if (banner) {
                    console.log('Показываю баннер уведомления...');
                    banner.classList.remove('hidden');
                    document.body.style.paddingTop = banner.offsetHeight + 'px';
                }
                
                // Проверяем статус заказа
                async function checkOrderStatus() {
                    try {
                        const checkResponse = await fetch(`/api/phone/verification/check-status?order_id=${orderIdFromUrl}`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });
                        
                        if (!checkResponse.ok) {
                            throw new Error('Ошибка проверки статуса');
                        }
                        
                        const statusData = await checkResponse.json();
                        console.log('Статус заказа получен:', statusData);
                        
                        if (statusData.success && (statusData.is_verified || statusData.order_status !== 'pending_verification')) {
                            // Верификация успешна
                            console.log('Верификация успешна!');
                            if (banner && bannerIcon && bannerText && bannerClose) {
                                banner.classList.remove('hidden');
                                banner.classList.remove('bg-yellow-50', 'dark:bg-yellow-900', 'border-yellow-500', 'bg-red-50', 'dark:bg-red-900', 'border-red-500');
                                banner.classList.add('bg-green-50', 'dark:bg-green-900', 'border-green-500');
                                bannerIcon.innerHTML = `
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                `;
                                bannerText.textContent = '✅ Телефон подтвержден! Ваш заказ успешно принят и будет обработан.';
                                bannerText.classList.remove('text-gray-900', 'dark:text-white', 'text-yellow-800', 'dark:text-yellow-100', 'text-red-800', 'dark:text-red-100');
                                bannerText.classList.add('text-green-800', 'dark:text-green-100');
                                bannerClose.style.display = 'block';
                                bannerClose.onclick = function() {
                                    banner.classList.add('hidden');
                                    document.body.style.paddingTop = '0';
                                    // Убираем order_id из URL
                                    const url = new URL(window.location.href);
                                    url.searchParams.delete('order_id');
                                    window.history.replaceState({}, '', url.toString());
                                };
                            }
                            
                            // Очищаем корзину и данные
                            cart = [];
                            saveCart();
                            updateCartDisplay();
                            
                            // Очищаем все флаги верификации
                            localStorage.removeItem('pendingVerificationCheck');
                            localStorage.removeItem('currentVerificationOrderId');
                            localStorage.removeItem('verificationInProgress');
                            localStorage.removeItem('verificationStartedAt');
                            localStorage.removeItem('pendingVerificationSuccess');
                            localStorage.removeItem('verificationReturnUrl');
                            
                            // Показываем уведомление
                            showNotification('✅ Телефон подтвержден! Ваш заказ успешно принят и будет обработан.', 'success');
                        } else {
                            // Верификация еще не завершена
                            console.log('Верификация еще не завершена');
                            if (banner && bannerIcon && bannerText && bannerClose) {
                                banner.classList.remove('hidden');
                                banner.classList.remove('bg-green-50', 'dark:bg-green-900', 'border-green-500', 'bg-red-50', 'dark:bg-red-900', 'border-red-500');
                                banner.classList.add('bg-yellow-50', 'dark:bg-yellow-900', 'border-yellow-500');
                                bannerIcon.innerHTML = `
                                    <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                `;
                                bannerText.textContent = '⚠️ Верификация еще не завершена. Завершите процесс подтверждения в Telegram боте.';
                                bannerText.classList.remove('text-gray-900', 'dark:text-white', 'text-green-800', 'dark:text-green-100', 'text-red-800', 'dark:text-red-100');
                                bannerText.classList.add('text-yellow-800', 'dark:text-yellow-100');
                                bannerClose.style.display = 'block';
                                bannerClose.onclick = function() {
                                    banner.classList.add('hidden');
                                    document.body.style.paddingTop = '0';
                                };
                            }
                        }
                    } catch (error) {
                        console.error('Ошибка проверки статуса:', error);
                        if (banner && bannerIcon && bannerText && bannerClose) {
                            banner.classList.remove('hidden');
                            banner.classList.remove('bg-green-50', 'dark:bg-green-900', 'border-green-500', 'bg-yellow-50', 'dark:bg-yellow-900', 'border-yellow-500');
                            banner.classList.add('bg-red-50', 'dark:bg-red-900', 'border-red-500');
                            bannerIcon.innerHTML = `
                                <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            `;
                            bannerText.textContent = '❌ Ошибка при проверке статуса заказа. Попробуйте позже.';
                            bannerText.classList.remove('text-gray-900', 'dark:text-white', 'text-green-800', 'dark:text-green-100', 'text-yellow-800', 'dark:text-yellow-100');
                            bannerText.classList.add('text-red-800', 'dark:text-red-100');
                            bannerClose.style.display = 'block';
                            bannerClose.onclick = function() {
                                banner.classList.add('hidden');
                                document.body.style.paddingTop = '0';
                            };
                        }
                    }
                }
                
                // Запускаем проверку статуса
                checkOrderStatus();
            }
            
            if (urlParams.get('return') === 'true') {
                console.log('Обнаружен параметр return=true, начинаю обработку...');
                const isTelegram = isTelegramWebView();
                const savedUrl = localStorage.getItem('verificationReturnUrl');
                // Получаем orderId из URL или localStorage
                const orderIdFromUrl = urlParams.get('order_id');
                const currentOrderId = orderIdFromUrl || localStorage.getItem('currentVerificationOrderId');
                
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
                    const openBrowserDiv = document.getElementById('telegram-return-open-browser');
                    
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
                                
                                console.log('Статус верификации получен:', statusData);
                                
                                if (statusData.success && (statusData.is_verified || statusData.order_status !== 'pending_verification')) {
                                    // Верификация успешна
                                    console.log('Верификация успешна!');
                                    successDiv.classList.remove('hidden');
                                    openBrowserDiv.classList.remove('hidden');
                                    
                                    // Обновляем баннер с успешным сообщением
                                    if (banner && bannerIcon && bannerText && bannerClose) {
                                        banner.classList.remove('hidden');
                                        banner.classList.remove('bg-yellow-50', 'dark:bg-yellow-900', 'border-yellow-500', 'bg-red-50', 'dark:bg-red-900', 'border-red-500');
                                        banner.classList.add('bg-green-50', 'dark:bg-green-900', 'border-green-500');
                                        bannerIcon.innerHTML = `
                                            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        `;
                                        bannerText.textContent = '✅ Телефон подтвержден! Ваш заказ успешно принят и будет обработан.';
                                        bannerText.classList.remove('text-gray-900', 'dark:text-white', 'text-yellow-800', 'dark:text-yellow-100', 'text-red-800', 'dark:text-red-100');
                                        bannerText.classList.add('text-green-800', 'dark:text-green-100');
                                        bannerClose.style.display = 'block';
                                        bannerClose.onclick = function() {
                                            banner.classList.add('hidden');
                                            document.body.style.paddingTop = '0';
                                        };
                                    }
                                    
                                    // Очищаем корзину и данные
                                    cart = [];
                                    saveCart();
                                    updateCartDisplay();
                                    
                                    // Очищаем все флаги верификации
                                    localStorage.removeItem('pendingVerificationCheck');
                                    localStorage.removeItem('currentVerificationOrderId');
                                    localStorage.removeItem('verificationInProgress');
                                    localStorage.removeItem('verificationStartedAt');
                                    localStorage.removeItem('pendingVerificationSuccess');
                                    localStorage.removeItem('verificationReturnUrl');
                                    
                                    // Обработчик закрытия успешного окна
                                    const closeBtn = document.getElementById('telegram-return-close-success');
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
                                } else {
                                    // Верификация не завершена или ошибка
                                    console.log('Верификация еще не завершена или ошибка');
                                    errorDiv.classList.remove('hidden');
                                    openBrowserDiv.classList.remove('hidden');
                                    const errorMessage = document.getElementById('telegram-return-error-message');
                                    if (errorMessage) {
                                        errorMessage.textContent = 'Верификация еще не завершена. Пожалуйста, завершите процесс подтверждения в Telegram боте.';
                                    }
                                    
                                    // Обновляем баннер с сообщением об ошибке
                                    if (banner && bannerIcon && bannerText && bannerClose) {
                                        banner.classList.remove('hidden');
                                        banner.classList.remove('bg-green-50', 'dark:bg-green-900', 'border-green-500', 'bg-red-50', 'dark:bg-red-900', 'border-red-500');
                                        banner.classList.add('bg-yellow-50', 'dark:bg-yellow-900', 'border-yellow-500');
                                        bannerIcon.innerHTML = `
                                            <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        `;
                                        bannerText.textContent = '⚠️ Верификация еще не завершена. Завершите процесс подтверждения в Telegram боте.';
                                        bannerText.classList.remove('text-gray-900', 'dark:text-white', 'text-green-800', 'dark:text-green-100', 'text-red-800', 'dark:text-red-100');
                                        bannerText.classList.add('text-yellow-800', 'dark:text-yellow-100');
                                        bannerClose.style.display = 'block';
                                        bannerClose.onclick = function() {
                                            banner.classList.add('hidden');
                                            document.body.style.paddingTop = '0';
                                        };
                                    }
                                    
                                    // Обработчик закрытия окна ошибки
                                    const closeBtn = document.getElementById('telegram-return-close-error');
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
                                const browserUrlInput = document.getElementById('telegram-browser-url');
                                const copyUrlBtn = document.getElementById('telegram-copy-url-btn');
                                const openBrowserDiv = document.getElementById('telegram-return-open-browser');
                                
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
                                const errorMessage = document.getElementById('telegram-return-error-message');
                                if (errorMessage) {
                                    errorMessage.textContent = 'Ошибка при проверке статуса заказа. Попробуйте позже.';
                                }
                                
                                // Обновляем баннер с сообщением об ошибке
                                if (banner && bannerIcon && bannerText && bannerClose) {
                                    banner.classList.remove('hidden');
                                    banner.classList.remove('bg-green-50', 'dark:bg-green-900', 'border-green-500', 'bg-yellow-50', 'dark:bg-yellow-900', 'border-yellow-500');
                                    banner.classList.add('bg-red-50', 'dark:bg-red-900', 'border-red-500');
                                    bannerIcon.innerHTML = `
                                        <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    `;
                                    bannerText.textContent = '❌ Ошибка при проверке статуса заказа. Попробуйте позже.';
                                    bannerText.classList.remove('text-gray-900', 'dark:text-white', 'text-green-800', 'dark:text-green-100', 'text-yellow-800', 'dark:text-yellow-100');
                                    bannerText.classList.add('text-red-800', 'dark:text-red-100');
                                    bannerClose.style.display = 'block';
                                    bannerClose.onclick = function() {
                                        banner.classList.add('hidden');
                                        document.body.style.paddingTop = '0';
                                    };
                                }
                                
                                const closeBtn = document.getElementById('telegram-return-close-error');
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
                        }
                        
                        // Запускаем проверку статуса
                        checkVerificationStatus();
                    } else {
                        console.error('Модальное окно не найдено!');
                        // Если модальное окно не найдено, показываем только баннер с сообщением
                        if (banner && bannerIcon && bannerText && bannerClose) {
                            banner.classList.remove('hidden');
                            banner.classList.add('bg-yellow-50', 'dark:bg-yellow-900', 'border-yellow-500');
                            bannerIcon.innerHTML = `
                                <svg class="animate-spin h-6 w-6 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            `;
                            bannerText.textContent = 'Проверка статуса заказа...';
                            bannerClose.style.display = 'none';
                        }
                    }
                    return;
                } else {
                    console.log('OrderId не найден, но показываю баннер с информацией');
                    // Даже если нет orderId, показываем баннер с информацией
                    if (banner && bannerIcon && bannerText && bannerClose) {
                        banner.classList.remove('hidden');
                        banner.classList.add('bg-blue-50', 'dark:bg-blue-900', 'border-blue-500');
                        bannerIcon.innerHTML = `
                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        `;
                        bannerText.textContent = 'Вы вернулись с верификации. Проверьте статус заказа в личном кабинете.';
                        bannerText.classList.remove('text-gray-900', 'dark:text-white');
                        bannerText.classList.add('text-blue-800', 'dark:text-blue-100');
                        bannerClose.style.display = 'block';
                        bannerClose.onclick = function() {
                            banner.classList.add('hidden');
                            document.body.style.paddingTop = '0';
                        };
                    }
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

            // Flowbite автоматически управляет aria-hidden для drawer

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

            // Инициализация модальных окон через Flowbite API
            // Ждем полной загрузки Flowbite
            function initializeModals() {
                if (window.Flowbite && window.Flowbite.Modal && typeof window.Flowbite.Modal === 'function') {
                    // Инициализация checkout-modal
                    const checkoutModal = document.getElementById('checkout-modal');
                    if (checkoutModal && !window.checkoutModalInstance) {
                        try {
                            window.checkoutModalInstance = new window.Flowbite.Modal(checkoutModal, {
                                placement: 'center',
                                backdrop: 'dynamic',
                                backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                                closable: true,
                                onHide: () => {
                                    const focusedElement = checkoutModal.querySelector(':focus');
                                    if (focusedElement) {
                                        focusedElement.blur();
                                    }
                                },
                            });
                        } catch (e) {
                            console.log('Checkout modal already initialized or error:', e);
                        }
                    }
                    
                    // Инициализация category-modal
                    const categoryModal = document.getElementById('category-modal');
                    if (categoryModal && !window.categoryModalInstance) {
                        try {
                            window.categoryModalInstance = new window.Flowbite.Modal(categoryModal, {
                                placement: 'center',
                                backdrop: 'dynamic',
                                backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                                closable: true,
                                onHide: () => {
                                    const focusedElement = categoryModal.querySelector(':focus');
                                    if (focusedElement) {
                                        focusedElement.blur();
                                    }
                                },
                            });
                        } catch (e) {
                            console.log('Category modal already initialized or error:', e);
                        }
                    }
                    
                    // Инициализация category-products-modal
                    const categoryProductsModal = document.getElementById('category-products-modal');
                    if (categoryProductsModal && !window.categoryProductsModalInstance) {
                        try {
                            window.categoryProductsModalInstance = new window.Flowbite.Modal(categoryProductsModal, {
                                placement: 'center',
                                backdrop: 'dynamic',
                                backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                                closable: true,
                                onHide: () => {
                                    const focusedElement = categoryProductsModal.querySelector(':focus');
                                    if (focusedElement) {
                                        focusedElement.blur();
                                    }
                                },
                            });
                        } catch (e) {
                            console.log('Category products modal already initialized or error:', e);
                        }
                    }
                    
                    // Инициализация verification-modal
                    const verificationModal = document.getElementById('verification-modal');
                    if (verificationModal && !window.verificationModalInstance) {
                        try {
                            window.verificationModalInstance = new window.Flowbite.Modal(verificationModal, {
                                placement: 'center',
                                backdrop: 'dynamic',
                                backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                                closable: true,
                                onHide: () => {
                                    const focusedElement = verificationModal.querySelector(':focus');
                                    if (focusedElement) {
                                        focusedElement.blur();
                                    }
                                },
                            });
                            // Убеждаемся, что экземпляр зарегистрирован в Flowbite
                            if (window.Flowbite.getInstance) {
                                // Проверяем, что экземпляр доступен через getInstance
                                const instance = window.Flowbite.getInstance('modal', 'verification-modal');
                                if (!instance) {
                                    console.warn('Verification modal instance not registered in Flowbite');
                                }
                            }
                        } catch (e) {
                            console.log('Verification modal already initialized or error:', e);
                        }
                    }
                } else {
                    // Если Flowbite еще не загружен, пробуем позже
                    if (window.flowbiteInitAttempts === undefined) {
                        window.flowbiteInitAttempts = 0;
                    }
                    window.flowbiteInitAttempts++;
                    if (window.flowbiteInitAttempts < 50) { // Максимум 5 секунд ожидания
                        setTimeout(initializeModals, 100);
                    } else {
                        console.error('Flowbite failed to load after 5 seconds');
                    }
                }
            }
            
            // Инициализируем модальные окна после загрузки DOM и Flowbite
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    // Даем Flowbite время на инициализацию
                    setTimeout(initializeModals, 200);
                });
            } else {
                // DOM уже загружен
                setTimeout(initializeModals, 200);
            }

            // Управление aria-hidden при ручном открытии/закрытии (fallback)

            // Обработчик переключения типа доставки
            const deliveryTypePickup = document.getElementById('delivery_type_pickup');
            const deliveryTypeDelivery = document.getElementById('delivery_type_delivery');
            const addressFieldContainer = document.getElementById('address-field-container');
            const customerAddressField = document.getElementById('customer_address');

            function updateAddressFieldVisibility() {
                if (deliveryTypeDelivery && deliveryTypeDelivery.checked) {
                    // Доставка - показываем поле адреса и делаем его обязательным
                    if (addressFieldContainer) {
                        addressFieldContainer.classList.remove('hidden');
                    }
                    if (customerAddressField) {
                        customerAddressField.setAttribute('required', 'required');
                    }
                } else {
                    // Самовывоз - скрываем поле адреса и убираем обязательность
                    if (addressFieldContainer) {
                        addressFieldContainer.classList.add('hidden');
                    }
                    if (customerAddressField) {
                        customerAddressField.removeAttribute('required');
                        customerAddressField.value = ''; // Очищаем значение при переключении на самовывоз
                    }
                }
            }

            if (deliveryTypePickup) {
                deliveryTypePickup.addEventListener('change', updateAddressFieldVisibility);
            }
            if (deliveryTypeDelivery) {
                deliveryTypeDelivery.addEventListener('change', updateAddressFieldVisibility);
            }

            // Инициализируем видимость поля адреса при загрузке
            updateAddressFieldVisibility();

            // Функция для обновления CSRF токена
            async function refreshCsrfToken() {
                try {
                    const response = await fetch('{{ route("api.csrf-token") }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                        if (csrfTokenMeta && data.token) {
                            csrfTokenMeta.setAttribute('content', data.token);
                            return data.token;
                        }
                    }
                } catch (error) {
                    console.error('Ошибка при обновлении CSRF токена:', error);
                }
                return null;
            }

            // Функция для отправки заказа с автоматическим обновлением CSRF токена
            async function submitOrder(orderData, retryCount = 0) {
                const maxRetries = 1;
                
                // Получаем CSRF токен с проверкой
                let csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfTokenMeta || !csrfTokenMeta.getAttribute('content')) {
                    // Пытаемся обновить токен
                    const newToken = await refreshCsrfToken();
                    if (!newToken) {
                        throw new Error('Не удалось получить CSRF токен');
                    }
                    csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                }
                
                const csrfToken = csrfTokenMeta.getAttribute('content');

                const response = await fetch('{{ route("api.orders.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(orderData),
                });

                // Обработка ошибки CSRF token mismatch (419)
                if (response.status === 419 && retryCount < maxRetries) {
                    // Обновляем токен и повторяем запрос
                    const newToken = await refreshCsrfToken();
                    if (newToken) {
                        return submitOrder(orderData, retryCount + 1);
                    }
                }

                return response;
            }

            // Обработчик формы оформления заказа
            const checkoutForm = document.getElementById('checkout-form');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Очищаем старые данные верификации перед началом нового процесса
                    localStorage.removeItem('pendingVerificationCheck');
                    localStorage.removeItem('currentVerificationOrderId');
                    localStorage.removeItem('verificationInProgress');
                    localStorage.removeItem('verificationStartedAt');
                    localStorage.removeItem('pendingVerificationSuccess');
                    window.verificationToken = null;
                    window.pendingOrderId = null;
                    window.pendingOrderPhone = null;
                    
                    const submitButton = document.getElementById('checkout-submit');
                    const errorDiv = document.getElementById('checkout-error');
                    const customerName = document.getElementById('customer_name').value;
                    const customerPhone = document.getElementById('customer_phone').value;
                    const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value || 'pickup';
                    const customerAddress = customerAddressField?.value || '';

                    // Валидация адреса для доставки
                    if (deliveryType === 'delivery' && !customerAddress.trim()) {
                        errorDiv.textContent = 'Адрес доставки обязателен для заполнения';
                        errorDiv.classList.remove('hidden');
                        submitButton.disabled = false;
                        submitButton.textContent = 'Подтвердить заказ';
                        return;
                    }

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
                            customer_address: deliveryType === 'delivery' ? customerAddress : null,
                            delivery_type: deliveryType,
                            items: cart.map(item => {
                                const orderItem = {
                                    dish_name: item.name,
                                    price: item.price,
                                    quantity: item.quantity,
                                };
                                
                                // Если это конструктор, добавляем constructor_data и не добавляем dish_id
                                if (item.isConstructor && item.constructorData) {
                                    orderItem.constructor_data = item.constructorData;
                                    orderItem.dish_id = null;
                                } else {
                                    orderItem.dish_id = item.id;
                                }
                                
                                return orderItem;
                            }),
                        };

                        const response = await submitOrder(orderData);

                        // Обработка ошибки CSRF token mismatch (419) после всех попыток
                        if (response.status === 419) {
                            errorDiv.textContent = 'Сессия истекла. Пожалуйста, обновите страницу и попробуйте снова.';
                            errorDiv.classList.remove('hidden');
                            return;
                        }

                        const data = await response.json();

                        if (response.ok) {
                            // Сохраняем токен входа в localStorage, если он есть
                            if (data.login_token) {
                                localStorage.setItem('login_token', data.login_token);
                                localStorage.setItem('login_phone', data.phone);
                            }

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
                    // Убираем фокус с элементов внутри модального окна
                    const focusedElement = modal.querySelector(':focus');
                    if (focusedElement) {
                        focusedElement.blur();
                    }
                    
                    // Используем сохраненный экземпляр или Flowbite API
                    if (window.checkoutModalInstance) {
                        window.checkoutModalInstance.hide();
                    } else if (window.Flowbite && window.Flowbite.getInstance) {
                        const modalInstance = window.Flowbite.getInstance('modal', 'checkout-modal');
                        if (modalInstance) {
                            modalInstance.hide();
                        } else {
                            modal.classList.add('hidden');
                        }
                    } else {
                        modal.classList.add('hidden');
                    }
                }
            };

            // Функции для работы с модальным окном верификации
            window.openVerificationModal = function() {
                const modal = document.getElementById('verification-modal');
                if (modal) {
                    // Сбрасываем форму верификации (этап с кодом больше не используется)
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
                    
                    // Используем сохраненный экземпляр или прямое открытие
                    if (window.verificationModalInstance) {
                        window.verificationModalInstance.show();
                    } else {
                        // Fallback - прямое открытие (если Flowbite еще не инициализирован)
                        modal.classList.remove('hidden');
                        // Пробуем инициализировать, если Flowbite уже загружен
                        if (window.Flowbite && window.Flowbite.Modal && !window.verificationModalInstance) {
                            try {
                                window.verificationModalInstance = new window.Flowbite.Modal(modal, {
                                    placement: 'center',
                                    backdrop: 'dynamic',
                                    backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
                                    closable: true,
                                });
                            } catch (e) {
                                console.log('Could not initialize verification modal:', e);
                            }
                        }
                    }
                }
            };

            window.closeVerificationModal = function() {
                const modal = document.getElementById('verification-modal');
                if (modal) {
                    // Используем сохраненный экземпляр или прямое закрытие
                    if (window.verificationModalInstance) {
                        window.verificationModalInstance.hide();
                    } else {
                        // Fallback - убираем фокус и закрываем
                        const focusedElement = modal.querySelector(':focus');
                        if (focusedElement) {
                            focusedElement.blur();
                        }
                        modal.classList.add('hidden');
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

            // Функция для отображения красивого уведомления
            function showNotification(message, type = 'success') {
                const container = document.getElementById('notification-container');
                if (!container) return;

                const notification = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-green-50 border-green-500 dark:bg-green-500/15 dark:border-green-500/30' : 'bg-red-50 border-red-500 dark:bg-red-500/15 dark:border-red-500/30';
                const textColor = type === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';
                
                notification.className = `rounded-xl border p-4 shadow-lg transform transition-all duration-300 ease-in-out ${bgColor}`;
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                
                const icon = type === 'success' 
                    ? `<svg class="fill-current ${iconColor}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.70186 12.0001C3.70186 7.41711 7.41711 3.70186 12.0001 3.70186C16.5831 3.70186 20.2984 7.41711 20.2984 12.0001C20.2984 16.5831 16.5831 20.2984 12.0001 20.2984C7.41711 20.2984 3.70186 16.5831 3.70186 12.0001ZM12.0001 1.90186C6.423 1.90186 1.90186 6.423 1.90186 12.0001C1.90186 17.5772 6.423 22.0984 12.0001 22.0984C17.5772 22.0984 22.0984 17.5772 22.0984 12.0001C22.0984 6.423 17.5772 1.90186 12.0001 1.90186ZM15.6197 10.7395C15.9712 10.388 15.9712 9.81819 15.6197 9.46672C15.2683 9.11525 14.6984 9.11525 14.347 9.46672L11.1894 12.6243L9.6533 11.0883C9.30183 10.7368 8.73198 10.7368 8.38051 11.0883C8.02904 11.4397 8.02904 12.0096 8.38051 12.3611L10.553 14.5335C10.7217 14.7023 10.9507 14.7971 11.1894 14.7971C11.428 14.7971 11.657 14.7023 11.8257 14.5335L15.6197 10.7395Z" fill=""></path>
                    </svg>`
                    : `<svg class="fill-current ${iconColor}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM13 8C13 7.44772 12.5523 7 12 7C11.4477 7 11 7.44772 11 8V12C11 12.5523 11.4477 13 12 13C12.5523 13 13 12.5523 13 12V8ZM12 16C11.4477 16 11 15.5523 11 15C11 14.4477 11.4477 14 12 14C12.5523 14 13 14.4477 13 15C13 15.5523 12.5523 16 12 16Z" fill=""></path>
                    </svg>`;
                
                notification.innerHTML = `
                    <div class="flex items-start gap-3">
                        <div class="-mt-0.5 ${iconColor}">
                            ${icon}
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium ${textColor}">${message}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                `;
                
                container.appendChild(notification);
                container.style.display = 'block';
                
                // Анимация появления
                setTimeout(() => {
                    notification.style.opacity = '1';
                    notification.style.transform = 'translateX(0)';
                }, 10);
                
                // Автоматическое скрытие через 5 секунд
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notification.parentElement) {
                            notification.remove();
                        }
                        if (container.children.length === 0) {
                            container.style.display = 'none';
                        }
                    }, 300);
                }, 5000);
            }

            function handleVerificationSuccess() {
                // Проверяем, видна ли страница пользователю
                const isPageVisible = !document.hidden;
                
                // Очищаем корзину
                cart = [];
                saveCart();
                updateCartDisplay();

                // Закрываем модальные окна
                window.closeVerificationModal();
                window.closeCartDrawer();

                // Если страница видна - показываем уведомление сразу
                // Если нет - сохраняем флаг и покажем при возврате
                if (isPageVisible) {
                    showNotification('✅ Телефон подтвержден! Ваш заказ успешно принят и будет обработан.', 'success');
                } else {
                    // Сохраняем флаг в localStorage для показа при возврате
                    localStorage.setItem('pendingVerificationSuccess', 'true');
                }

                // Очищаем данные заказа
                if (typeof checkoutForm !== 'undefined' && checkoutForm) {
                    checkoutForm.reset();
                }
                window.pendingOrderId = null;
                window.pendingOrderPhone = null;
            }
            
            // Проверка флага при возврате на страницу
            async function checkPendingVerificationSuccess() {
                console.log('Проверка верификации при возврате...');
                
                // Проверяем флаг успешной верификации
                const pending = localStorage.getItem('pendingVerificationSuccess');
                if (pending === 'true') {
                    console.log('Найден флаг успешной верификации');
                    localStorage.removeItem('pendingVerificationSuccess');
                    showNotification('✅ Телефон подтвержден! Ваш заказ успешно принят и будет обработан.', 'success');
                    return;
                }
                
                // Проверяем, идет ли процесс верификации
                const verificationInProgress = localStorage.getItem('verificationInProgress');
                const currentOrderId = localStorage.getItem('currentVerificationOrderId');
                const verificationStartedAt = localStorage.getItem('verificationStartedAt');
                
                // Проверяем, не истекло ли время верификации (максимум 10 минут)
                if (verificationStartedAt) {
                    const startedAt = parseInt(verificationStartedAt);
                    const elapsed = Date.now() - startedAt;
                    const maxTime = 10 * 60 * 1000; // 10 минут
                    
                    if (elapsed > maxTime) {
                        console.log('Время верификации истекло, очищаю данные');
                        localStorage.removeItem('pendingVerificationCheck');
                        localStorage.removeItem('currentVerificationOrderId');
                        localStorage.removeItem('verificationInProgress');
                        localStorage.removeItem('verificationStartedAt');
                        return;
                    }
                }
                
                console.log('Состояние верификации:', {
                    inProgress: verificationInProgress,
                    orderId: currentOrderId
                });
                
                if (verificationInProgress === 'true' && currentOrderId) {
                    try {
                        console.log('Проверяю статус заказа:', currentOrderId);
                        const checkResponse = await fetch(`/api/phone/verification/check-status?order_id=${currentOrderId}`, {
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
                        
                        console.log('Статус верификации:', statusData);
                        
                        if (statusData.success && (statusData.is_verified || statusData.order_status !== 'pending_verification')) {
                            console.log('Верификация успешна, очищаю корзину и показываю уведомление');
                            
                            // Вызываем handleVerificationSuccess для очистки корзины и всех данных
                            handleVerificationSuccess();
                            
                            // Очищаем все флаги верификации
                            localStorage.removeItem('pendingVerificationCheck');
                            localStorage.removeItem('currentVerificationOrderId');
                            localStorage.removeItem('verificationInProgress');
                            localStorage.removeItem('verificationStartedAt');
                            localStorage.removeItem('pendingVerificationSuccess');
                        }
                    } catch (error) {
                        console.error('Ошибка проверки статуса при возврате:', error);
                    }
                }
            }
            
            // Очищаем устаревшие данные верификации при загрузке страницы
            const verificationStartedAt = localStorage.getItem('verificationStartedAt');
            if (verificationStartedAt) {
                const startedAt = parseInt(verificationStartedAt);
                const elapsed = Date.now() - startedAt;
                const maxTime = 10 * 60 * 1000; // 10 минут
                
                if (elapsed > maxTime) {
                    console.log('Обнаружены устаревшие данные верификации, очищаю');
                    localStorage.removeItem('pendingVerificationCheck');
                    localStorage.removeItem('currentVerificationOrderId');
                    localStorage.removeItem('verificationInProgress');
                    localStorage.removeItem('verificationStartedAt');
                    localStorage.removeItem('pendingVerificationSuccess');
                    window.verificationToken = null;
                    window.pendingOrderId = null;
                    window.pendingOrderPhone = null;
                }
            }
            
            // Очищаем все данные верификации при загрузке страницы, если нет активного заказа
            // Это предотвращает использование старых токенов
            if (!window.pendingOrderId && !localStorage.getItem('currentVerificationOrderId')) {
                console.log('Нет активного заказа, очищаю все данные верификации');
                localStorage.removeItem('pendingVerificationCheck');
                localStorage.removeItem('currentVerificationOrderId');
                localStorage.removeItem('verificationInProgress');
                localStorage.removeItem('verificationStartedAt');
                localStorage.removeItem('pendingVerificationSuccess');
                window.verificationToken = null;
                window.pendingOrderId = null;
                window.pendingOrderPhone = null;
            }
            
            // Проверяем при загрузке страницы только один раз
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', checkPendingVerificationSuccess);
            } else {
                // DOM уже загружен, выполняем сразу
                checkPendingVerificationSuccess();
            }
            
            // Проверяем при возврате на вкладку (Page Visibility API) - только один раз
            let visibilityCheckDone = false;
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden && !visibilityCheckDone) {
                    visibilityCheckDone = true;
                    checkPendingVerificationSuccess();
                }
            });
            
            // Проверяем при фокусе на окно - только один раз
            let focusCheckDone = false;
            window.addEventListener('focus', function() {
                if (!focusCheckDone) {
                    focusCheckDone = true;
                    checkPendingVerificationSuccess();
                }
            });

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
                        // Получаем CSRF токен с проверкой
                        let csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfTokenMeta || !csrfTokenMeta.getAttribute('content')) {
                            const newToken = await refreshCsrfToken();
                            if (!newToken) {
                                errorDiv.textContent = 'Ошибка: CSRF токен не найден. Пожалуйста, обновите страницу.';
                                errorDiv.classList.remove('hidden');
                                telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                                return;
                            }
                            csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                        }
                        
                        let csrfToken = csrfTokenMeta.getAttribute('content');

                        let response = await fetch('{{ route("api.phone.verification.start") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                order_id: window.pendingOrderId,
                            }),
                        });

                        // Обработка ошибки CSRF token mismatch (419) с автоматическим обновлением
                        if (response.status === 419) {
                            console.log('CSRF токен истек, обновляем...');
                            const newToken = await refreshCsrfToken();
                            if (newToken) {
                                csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                                csrfToken = csrfTokenMeta.getAttribute('content');
                                console.log('CSRF токен обновлен, повторяем запрос...');
                                response = await fetch('{{ route("api.phone.verification.start") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({
                                        order_id: window.pendingOrderId,
                                    }),
                                });
                                
                                // Если после обновления токена все еще ошибка 419, значит сессия истекла
                                if (response.status === 419) {
                                    errorDiv.textContent = 'Сессия истекла. Пожалуйста, обновите страницу и попробуйте снова.';
                                    errorDiv.classList.remove('hidden');
                                    telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                                    console.error('CSRF token mismatch после обновления. Требуется обновление страницы.');
                                    return;
                                }
                            } else {
                                errorDiv.textContent = 'Не удалось обновить токен. Пожалуйста, обновите страницу.';
                                errorDiv.classList.remove('hidden');
                                telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                                return;
                            }
                        }

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
                            // Очищаем все старые данные перед сохранением новых
                            localStorage.removeItem('pendingVerificationCheck');
                            localStorage.removeItem('currentVerificationOrderId');
                            localStorage.removeItem('verificationInProgress');
                            localStorage.removeItem('verificationStartedAt');
                            localStorage.removeItem('pendingVerificationSuccess');
                            window.verificationToken = null;
                            
                            // Сохраняем новый токен и данные
                            window.verificationToken = data.verification_token;
                            
                            // Сохраняем данные в localStorage ДО открытия Telegram
                            if (window.pendingOrderId) {
                                localStorage.setItem('currentVerificationOrderId', window.pendingOrderId);
                                localStorage.setItem('verificationInProgress', 'true');
                                // Сохраняем timestamp для проверки
                                localStorage.setItem('verificationStartedAt', Date.now().toString());
                                console.log('Сохранено в localStorage:', {
                                    orderId: window.pendingOrderId,
                                    token: data.verification_token,
                                    token_length: data.verification_token ? data.verification_token.length : 0
                                });
                            }
                            
                            // Открываем Telegram бота
                            const botUrl = data.bot_url;
                            
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
                            openTelegramBot(botUrl);
                            
                            // Показываем индикатор ожидания
                            waitingDiv.classList.remove('hidden');
                            
                            // Сохраняем order_id для проверки при возврате на страницу
                            const orderId = window.pendingOrderId || data.order_id;
                            if (orderId) {
                                localStorage.setItem('currentVerificationOrderId', orderId);
                            }
                            
                            // Простая проверка статуса только при возврате на страницу
                            const checkVerificationStatus = async () => {
                                const currentOrderId = window.pendingOrderId || localStorage.getItem('currentVerificationOrderId');
                                if (!currentOrderId) {
                                    return;
                                }
                                
                                try {
                                    const checkResponse = await fetch(`/api/phone/verification/check-status?order_id=${currentOrderId}`, {
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
                                        // Верификация успешна
                                        handleVerificationSuccess();
                                        waitingDiv.classList.add('hidden');
                                        telegramBotLink.classList.remove('opacity-50', 'pointer-events-none');
                                        
                                        // Удаляем обработчики и очищаем localStorage
                                        if (window.verificationVisibilityHandler) {
                                            document.removeEventListener('visibilitychange', window.verificationVisibilityHandler);
                                            window.removeEventListener('focus', window.verificationVisibilityHandler);
                                        }
                                        localStorage.removeItem('currentVerificationOrderId');
                                        localStorage.removeItem('pendingVerificationCheck');
                                    }
                                } catch (error) {
                                    console.error('Ошибка проверки статуса:', error);
                                }
                            };
                            
                            // Сохраняем обработчик для возможности его удаления позже
                            window.verificationVisibilityHandler = checkVerificationStatus;
                            
                            // Проверяем статус только при возврате на страницу (без постоянного polling)
                            document.addEventListener('visibilitychange', function() {
                                if (!document.hidden) {
                                    checkVerificationStatus();
                                }
                            });
                            window.addEventListener('focus', checkVerificationStatus);
                            
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
                        // Получаем CSRF токен с проверкой
                        let csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfTokenMeta || !csrfTokenMeta.getAttribute('content')) {
                            const newToken = await refreshCsrfToken();
                            if (!newToken) {
                                errorDiv.textContent = 'Ошибка: CSRF токен не найден. Пожалуйста, обновите страницу.';
                                errorDiv.classList.remove('hidden');
                                verifyCodeButton.disabled = false;
                                verifyCodeButton.textContent = 'Подтвердить';
                                return;
                            }
                            csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                        }
                        
                        let csrfToken = csrfTokenMeta.getAttribute('content');

                        let response = await fetch('{{ route("api.phone.verification.verify") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                order_id: window.pendingOrderId,
                                code: code,
                            }),
                        });

                        // Обработка ошибки CSRF token mismatch (419) с автоматическим обновлением
                        if (response.status === 419) {
                            const newToken = await refreshCsrfToken();
                            if (newToken) {
                                csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                                csrfToken = csrfTokenMeta.getAttribute('content');
                                response = await fetch('{{ route("api.phone.verification.verify") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({
                                        order_id: window.pendingOrderId,
                                        code: code,
                                    }),
                                });
                            }
                            
                            if (response.status === 419) {
                                errorDiv.textContent = 'Сессия истекла. Пожалуйста, обновите страницу и попробуйте снова.';
                                errorDiv.classList.remove('hidden');
                                verifyCodeButton.disabled = false;
                                verifyCodeButton.textContent = 'Подтвердить';
                                return;
                            }
                        }

                        const data = await response.json();

                        if (response.ok && data.success) {
                            // Очищаем корзину
                            cart = [];
                            saveCart();
                            updateCartDisplay();
                            
                            // Закрываем модальное окно верификации и drawer
                            window.closeVerificationModal();
                            window.closeCartDrawer();
                            
                            // Показываем красивое уведомление об успехе
                            showNotification('✅ Телефон подтвержден! Ваш заказ успешно принят и будет обработан.', 'success');
                            
                            // Очищаем форму
                            checkoutForm.reset();
                            
                            // Очищаем данные заказа
                            window.pendingOrderId = null;
                            window.pendingOrderPhone = null;
                            
                                // Очищаем сохраненные данные верификации
                                localStorage.removeItem('currentVerificationOrderId');
                                localStorage.removeItem('verificationInProgress');
                                localStorage.removeItem('verificationStartedAt');
                                localStorage.removeItem('pendingVerificationCheck');
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

