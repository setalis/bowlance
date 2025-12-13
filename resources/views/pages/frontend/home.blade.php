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
        document.addEventListener('DOMContentLoaded', function() {
            const categoryButtons = document.querySelectorAll('[data-category-id]');
            const modal = document.getElementById('category-modal');
            const modalCategoryName = document.getElementById('modal-category-name');
            const modalLoading = document.getElementById('modal-loading');
            const modalContent = document.getElementById('modal-content');
            const modalDishes = document.getElementById('modal-dishes');
            const modalEmpty = document.getElementById('modal-empty');

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
                    
                    ${dish.price ? `<div class="mt-3 text-lg font-bold text-orange-600">${parseFloat(dish.price).toFixed(2)} ₽</div>` : ''}
                `;

                return card;
            }
        });
    </script>
    @endpush
@endsection

