@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
    <x-common.page-breadcrumb pageTitle="Редактировать блюдо">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('admin.dishes.index') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Блюда</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Редактировать блюдо</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    @if (session('status'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('status')" />
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark md:p-6"
        x-data="{
            loading: false,
            errors: {},
            successMessage: null,
            async submitForm(event) {
                event.preventDefault();
                this.loading = true;
                this.errors = {};
                this.successMessage = null;
                
                const form = event.target;
                const formData = new FormData(form);
                
                // Laravel требует _method для PUT запросов
                formData.append('_method', 'PUT');
                
                try {
                    const response = await fetch('{{ route('admin.dishes.update', $dish) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.successMessage = data.message;
                        setTimeout(() => {
                            window.location.href = '{{ route('admin.dishes.index') }}';
                        }, 1500);
                    } else {
                        if (data.errors) {
                            this.errors = data.errors;
                        } else if (data.message) {
                            this.errors = { form: [data.message] };
                        }
                    }
                } catch (error) {
                    this.errors = { form: ['Произошла ошибка при отправке формы.'] };
                } finally {
                    this.loading = false;
                }
            }
        }"
        x-init="$watch('errors', () => {
            if (Object.keys(errors).length > 0) {
                const firstError = document.querySelector('[x-error-field]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        })">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Редактирование блюда
            </h3>
        </div>

        <!-- Сообщение об успехе -->
        <div x-show="successMessage" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="mb-4">
            <div class="rounded-xl border border-green-500 bg-green-50 p-4 dark:border-green-500/30 dark:bg-green-500/15">
                <div class="flex items-start gap-3">
                    <div class="-mt-0.5 text-green-500">
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.70186 12.0001C3.70186 7.41711 7.41711 3.70186 12.0001 3.70186C16.5831 3.70186 20.2984 7.41711 20.2984 12.0001C20.2984 16.5831 16.5831 20.2984 12.0001 20.2984C7.41711 20.2984 3.70186 16.5831 3.70186 12.0001ZM12.0001 1.90186C6.423 1.90186 1.90186 6.423 1.90186 12.0001C1.90186 17.5772 6.423 22.0984 12.0001 22.0984C17.5772 22.0984 22.0984 17.5772 22.0984 12.0001C22.0984 6.423 17.5772 1.90186 12.0001 1.90186ZM15.6197 10.7395C15.9712 10.388 15.9712 9.81819 15.6197 9.46672C15.2683 9.11525 14.6984 9.11525 14.347 9.46672L11.1894 12.6243L9.6533 11.0883C9.30183 10.7368 8.73198 10.7368 8.38051 11.0883C8.02904 11.4397 8.02904 12.0096 8.38051 12.3611L10.553 14.5335C10.7217 14.7023 10.9507 14.7971 11.1894 14.7971C11.428 14.7971 11.657 14.7023 11.8257 14.5335L15.6197 10.7395Z" fill=""></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="successMessage"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Общая ошибка формы -->
        <div x-show="errors.form && errors.form.length > 0" 
             x-transition
             class="mb-4">
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <p class="text-sm text-red-600 dark:text-red-400" x-text="errors.form && errors.form[0] ? errors.form[0] : ''"></p>
            </div>
        </div>

        <form @submit="submitForm" method="POST" action="{{ route('admin.dishes.update', $dish) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Название блюда -->
                <div x-error-field>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Название блюда <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Введите название блюда"
                        value="{{ old('name', $dish->name) }}"
                        required
                        autofocus
                        x-bind:class="errors.name ? 'border-red-500' : ''"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    />
                    <template x-if="errors.name && errors.name.length > 0">
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400" x-text="errors.name[0]"></p>
                    </template>
                    @error('name')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Цена -->
                <div x-error-field>
                    <label for="price" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Цена (₽) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        value="{{ old('price', $dish->price) }}"
                        required
                        x-bind:class="errors.price ? 'border-red-500' : ''"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    />
                    <template x-if="errors.price && errors.price.length > 0">
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400" x-text="errors.price[0]"></p>
                    </template>
                    @error('price')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Категория -->
                <div x-error-field>
                    <label for="dish_category_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Категория <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="dish_category_id"
                        name="dish_category_id"
                        required
                        x-bind:class="errors.dish_category_id ? 'border-red-500' : ''"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    >
                        <option value="">Выберите категорию</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('dish_category_id', $dish->dish_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <template x-if="errors.dish_category_id && errors.dish_category_id.length > 0">
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400" x-text="errors.dish_category_id[0]"></p>
                    </template>
                    @error('dish_category_id')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Порядок сортировки -->
                <div>
                    <x-forms.input
                        name="sort_order"
                        label="Порядок сортировки"
                        type="number"
                        placeholder="0"
                        min="0"
                        :value="old('sort_order', $dish->sort_order ?? 0)"
                    />
                </div>

                <!-- Изображение -->
                <div class="md:col-span-2" x-error-field>
                    <label for="image" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Изображение
                    </label>
                    @if($dish->image)
                        <div class="mb-3">
                            <img src="{{ Storage::url($dish->image) }}" alt="{{ $dish->name }}" class="h-32 w-32 rounded-lg object-cover">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Текущее изображение</p>
                        </div>
                    @endif
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                        x-bind:class="errors.image ? 'border-red-500' : ''"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    >
                    <template x-if="errors.image && errors.image.length > 0">
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400" x-text="errors.image[0]"></p>
                    </template>
                    @error('image')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Форматы: JPEG, PNG, JPG, GIF, WEBP. Максимальный размер: 2 МБ
                    </p>
                </div>

                <!-- Вес/объем -->
                <div>
                    <x-forms.input
                        name="weight_volume"
                        label="Вес/объем"
                        type="text"
                        placeholder="Например: 300 г или 500 мл"
                        :value="old('weight_volume', $dish->weight_volume)"
                    />
                </div>

                <!-- Калории -->
                <div>
                    <x-forms.input
                        name="calories"
                        label="Калории (ккал)"
                        type="number"
                        placeholder="0"
                        min="0"
                        :value="old('calories', $dish->calories)"
                    />
                </div>

                <!-- Пищевая ценность: Белки, Жиры, Углеводы, Клетчатка -->
                <div class="md:col-span-2">
                    <div class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-400">Пищевая ценность</div>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <x-forms.input
                                name="proteins"
                                label="Белки (г)"
                                type="number"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                                :value="old('proteins', $dish->proteins)"
                            />
                        </div>
                        <div>
                            <x-forms.input
                                name="fats"
                                label="Жиры (г)"
                                type="number"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                                :value="old('fats', $dish->fats)"
                            />
                        </div>
                        <div>
                            <x-forms.input
                                name="carbohydrates"
                                label="Углеводы (г)"
                                type="number"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                                :value="old('carbohydrates', $dish->carbohydrates)"
                            />
                        </div>
                        <div>
                            <x-forms.input
                                name="fiber"
                                label="Клетчатка (г)"
                                type="number"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                                :value="old('fiber', $dish->fiber)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Описание -->
                <div class="md:col-span-2" x-error-field>
                    <label for="description" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Описание
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="Введите описание блюда"
                        x-bind:class="errors.description ? 'border-red-500' : ''"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >{{ old('description', $dish->description) }}</textarea>
                    <template x-if="errors.description && errors.description.length > 0">
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400" x-text="errors.description[0]"></p>
                    </template>
                    @error('description')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end pt-2">
                <a href="{{ route('admin.dishes.index') }}" class="inline-flex w-full sm:w-auto">
                    <x-ui.button variant="outline" className="w-full sm:w-auto">
                        Отмена
                    </x-ui.button>
                </a>
                <button type="submit" 
                        x-bind:disabled="loading"
                        class="inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-5 py-3.5 text-sm bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300 disabled:cursor-not-allowed disabled:opacity-50 w-full sm:w-auto">
                    <span x-show="!loading">Сохранить изменения</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Сохранение...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

