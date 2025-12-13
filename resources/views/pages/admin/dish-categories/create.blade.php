@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Добавить категорию блюд">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('admin.dish-categories.index') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Категории блюд</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Добавить категорию</span>
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
                Создание новой категории блюд
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Заполните форму для добавления новой категории блюд в систему
            </p>
        </div>

        <form method="POST" action="{{ route('admin.dish-categories.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid gap-4 md:grid-cols-1">
                <!-- Название категории -->
                <div>
                    <x-forms.input
                        name="name"
                        label="Название категории"
                        type="text"
                        placeholder="Введите название категории"
                        :value="old('name')"
                        required
                        autofocus
                    />
                </div>

                <!-- Изображение -->
                <div>
                    <label for="image" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Изображение
                    </label>
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('image') border-red-500 @enderror"
                    >
                    @error('image')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Форматы: JPEG, PNG, JPG, GIF, WEBP. Максимальный размер: 2 МБ
                    </p>
                </div>

                <!-- Порядок сортировки -->
                <div>
                    <x-forms.input
                        name="sort_order"
                        label="Порядок сортировки"
                        type="number"
                        placeholder="0"
                        :value="old('sort_order', 0)"
                        min="0"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Чем меньше число, тем выше категория в списке
                    </p>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end pt-2">
                <a href="{{ route('admin.dish-categories.index') }}" class="inline-flex w-full sm:w-auto">
                    <x-ui.button variant="outline" className="w-full sm:w-auto">
                        Отмена
                    </x-ui.button>
                </a>
                <x-ui.button type="submit" variant="primary" className="w-full sm:w-auto">
                    Создать категорию
                </x-ui.button>
            </div>
        </form>
    </div>
@endsection

