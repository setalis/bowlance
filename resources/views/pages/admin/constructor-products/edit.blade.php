@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
    <x-common.page-breadcrumb pageTitle="Редактировать продукт конструктора">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('admin.constructor-products.index') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Продукты конструктора</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Редактировать продукт</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    @if (session('status'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('status')" />
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark md:p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Редактирование продукта конструктора
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Обновите информацию о продукте
            </p>
        </div>

        <form method="POST" action="{{ route('admin.constructor-products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Название продукта -->
                <div>
                    <x-forms.input
                        name="name"
                        label="Название продукта"
                        type="text"
                        placeholder="Введите название продукта"
                        :value="old('name', $product->name)"
                        required
                        autofocus
                    />
                </div>

                <!-- Категория -->
                <div>
                    <label for="constructor_category_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Категория <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="constructor_category_id"
                        name="constructor_category_id"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                    >
                        <option value="">Выберите категорию</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('constructor_category_id', $product->constructor_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('constructor_category_id')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Цена -->
                <div>
                    <x-forms.input
                        name="price"
                        label="Цена (₾)"
                        type="number"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        :value="old('price', $product->price)"
                        required
                    />
                </div>

                <!-- Порядок сортировки -->
                <div>
                    <x-forms.input
                        name="sort_order"
                        label="Порядок сортировки"
                        type="number"
                        placeholder="0"
                        :value="old('sort_order', $product->sort_order ?? 0)"
                        min="0"
                    />
                </div>

                <!-- Изображение -->
                <div class="md:col-span-2">
                    <label for="image" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Изображение
                    </label>
                    @if($product->image)
                        <div class="mb-3">
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-32 w-32 rounded-lg object-cover">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Текущее изображение</p>
                        </div>
                    @endif
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 @error('image') border-red-500 @enderror"
                    >
                    @error('image')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Форматы: JPEG, PNG, JPG, GIF, WEBP. Максимальный размер: 2 МБ
                    </p>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end pt-2">
                <a href="{{ route('admin.constructor-products.index') }}" class="inline-flex w-full sm:w-auto">
                    <x-ui.button variant="outline" className="w-full sm:w-auto">
                        Отмена
                    </x-ui.button>
                </a>
                <x-ui.button type="submit" variant="primary" className="w-full sm:w-auto">
                    Сохранить изменения
                </x-ui.button>
            </div>
        </form>
    </div>
@endsection

