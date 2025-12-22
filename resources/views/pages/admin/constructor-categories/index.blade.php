@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Категории конструктора">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Категории конструктора</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    @if (session('status'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('status')" />
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark md:p-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Список категорий конструктора
            </h3>
            <a href="{{ route('admin.constructor-categories.create') }}" class="inline-flex w-full sm:w-auto">
                <x-ui.button variant="primary" className="w-full sm:w-auto">
                    Добавить категорию
                </x-ui.button>
            </a>
        </div>

        {{-- Мобильный вид: карточки --}}
        <div class="space-y-4 md:hidden">
            @forelse($categories as $category)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                {{ $category->name }}
                            </h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Продуктов в категории: {{ $category->products_count }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Порядок сортировки: {{ $category->sort_order ?? 0 }}
                            </p>
                        </div>
                        <div class="ml-4 flex gap-2">
                            <a href="{{ route('admin.constructor-categories.edit', $category) }}"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                title="Редактировать">
                                <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('edit') !!}</span>
                            </a>
                            <form method="POST"
                                action="{{ route('admin.constructor-categories.destroy', $category) }}"
                                onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию?');"
                                class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-red-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-red-400"
                                    title="Удалить">
                                    <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('delete') !!}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-dark">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Категории не найдены
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Десктопный вид: таблица --}}
        <div class="relative hidden overflow-x-auto md:block min-h-[300px]">
            <table class="w-full text-left">
                <thead class="bg-cyan-300 dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Название
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Количество продуктов
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Порядок сортировки
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90 text-right">
                            Действия
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">
                                {{ $category->name }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="bg-orange-300/50 rounded-lg px-2 py-1">{{ $category->products_count }}</span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">
                                {{ $category->sort_order ?? 0 }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.constructor-categories.edit', $category) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                        title="Редактировать">
                                        <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('edit') !!}</span>
                                    </a>
                                    <form method="POST"
                                        action="{{ route('admin.constructor-categories.destroy', $category) }}"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию?');"
                                        class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-red-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-red-400"
                                            title="Удалить">
                                            <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('delete') !!}</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Категории не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="mt-6 flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-center text-sm text-gray-600 dark:text-gray-400 sm:text-left">
                    Показано {{ $categories->firstItem() }} - {{ $categories->lastItem() }} из
                    {{ $categories->total() }} результатов
                </div>
                <div class="flex justify-center gap-2 sm:justify-end">
                    @if ($categories->onFirstPage())
                        <span
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500 sm:px-4">
                            <span class="hidden sm:inline">Предыдущая</span>
                            <span class="sm:hidden">←</span>
                        </span>
                    @else
                        <a href="{{ $categories->previousPageUrl() }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 sm:px-4">
                            <span class="hidden sm:inline">Предыдущая</span>
                            <span class="sm:hidden">←</span>
                        </a>
                    @endif

                    @if ($categories->hasMorePages())
                        <a href="{{ $categories->nextPageUrl() }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 sm:px-4">
                            <span class="hidden sm:inline">Следующая</span>
                            <span class="sm:hidden">→</span>
                        </a>
                    @else
                        <span
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500 sm:px-4">
                            <span class="hidden sm:inline">Следующая</span>
                            <span class="sm:hidden">→</span>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

