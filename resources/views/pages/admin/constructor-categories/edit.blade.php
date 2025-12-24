@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Редактировать категорию конструктора">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('admin.constructor-categories.index') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Категории конструктора</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Редактировать категорию</span>
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
                Редактирование категории конструктора
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Обновите информацию о категории
            </p>
        </div>

        <form method="POST" action="{{ route('admin.constructor-categories.update', $category) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-1">
                <!-- Название категории -->
                <div>
                    <x-forms.input
                        name="name"
                        label="Название категории"
                        type="text"
                        placeholder="Введите название категории"
                        :value="old('name', $category->name)"
                        required
                        autofocus
                    />
                </div>

                <!-- Порядок сортировки -->
                <div>
                    <x-forms.input
                        name="sort_order"
                        label="Порядок сортировки"
                        type="number"
                        placeholder="0"
                        :value="old('sort_order', $category->sort_order ?? 0)"
                        min="0"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Чем меньше число, тем выше категория в списке
                    </p>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end pt-2">
                <a href="{{ route('admin.constructor-categories.index') }}" class="inline-flex w-full sm:w-auto">
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


