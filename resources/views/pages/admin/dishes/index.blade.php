@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
    <x-common.page-breadcrumb pageTitle="Блюда">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Блюда</span>
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
                Список блюд
            </h3>
            <a href="{{ route('admin.dishes.create') }}" class="inline-flex w-full sm:w-auto">
                <x-ui.button variant="primary" className="w-full sm:w-auto">
                    Добавить блюдо
                </x-ui.button>
            </a>
        </div>

        {{-- Мобильный вид: карточки --}}
        <div class="space-y-4 md:hidden">
            @forelse($dishes as $dish)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark">
                    <div class="flex items-start gap-4">
                        @if($dish->image)
                            <img src="{{ Storage::url($dish->image) }}" alt="{{ $dish->name }}" class="h-20 w-20 flex-shrink-0 rounded-lg object-cover">
                        @else
                            <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700">
                                <span class="text-xs text-gray-400">Нет фото</span>
                            </div>
                        @endif
                        <div class="flex flex-1 items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                    {{ $dish->name }}
                                </h4>
                                @if($dish->weight_volume)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $dish->weight_volume }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="bg-orange-300/50 rounded-lg px-2 py-1">{{ $dish->category->name ?? '—' }}</span>
                                    <span>•</span>
                                    <span class="font-semibold">{{ number_format($dish->price, 2) }} ₽</span>
                                </div>
                                @if($dish->calories || $dish->proteins || $dish->fats || $dish->carbohydrates)
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        @if($dish->calories)К: {{ $dish->calories }}@endif
                                        @if($dish->proteins) Б: {{ number_format($dish->proteins, 1) }}@endif
                                        @if($dish->fats) Ж: {{ number_format($dish->fats, 1) }}@endif
                                        @if($dish->carbohydrates) У: {{ number_format($dish->carbohydrates, 1) }}@endif
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4 flex gap-2">
                                <a href="{{ route('admin.dishes.edit', $dish) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                    title="Редактировать">
                                    <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('edit') !!}</span>
                                </a>
                                <form method="POST"
                                    action="{{ route('admin.dishes.destroy', $dish) }}"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить это блюдо?');"
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
                </div>
            @empty
                <div class="rounded-lg border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-dark">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Блюда не найдены
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
                            Изображение
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Название
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Категория
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Цена
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            К/Б/Ж/У
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90 text-right">
                            Действия
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dishes as $dish)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-4">
                                @if($dish->image)
                                    <img src="{{ Storage::url($dish->image) }}" alt="{{ $dish->name }}" class="h-16 w-16 rounded-lg object-cover">
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700">
                                        <span class="text-xs text-gray-400">Нет фото</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">
                                <div class="font-semibold">{{ $dish->name }}</div>
                                @if($dish->weight_volume)
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $dish->weight_volume }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="bg-orange-300/50 rounded-lg px-2 py-1">{{ $dish->category->name ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-semibold">{{ number_format($dish->price, 2) }} ₽</span>
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-600 dark:text-gray-400">
                                @if($dish->calories || $dish->proteins || $dish->fats || $dish->carbohydrates)
                                    <div class="space-y-1">
                                        @if($dish->calories)
                                            <div>К: {{ $dish->calories }} ккал</div>
                                        @endif
                                        @if($dish->proteins)
                                            <div>Б: {{ number_format($dish->proteins, 1) }} г</div>
                                        @endif
                                        @if($dish->fats)
                                            <div>Ж: {{ number_format($dish->fats, 1) }} г</div>
                                        @endif
                                        @if($dish->carbohydrates)
                                            <div>У: {{ number_format($dish->carbohydrates, 1) }} г</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.dishes.edit', $dish) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                        title="Редактировать">
                                        <span class="w-5 h-5">{!! \App\Helpers\MenuHelper::getIconSvg('edit') !!}</span>
                                    </a>
                                    <form method="POST"
                                        action="{{ route('admin.dishes.destroy', $dish) }}"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить это блюдо?');"
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
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Блюда не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dishes->hasPages())
            <div class="mt-6 flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-center text-sm text-gray-600 dark:text-gray-400 sm:text-left">
                    Показано {{ $dishes->firstItem() }} - {{ $dishes->lastItem() }} из
                    {{ $dishes->total() }} результатов
                </div>
                <div class="flex justify-center gap-2 sm:justify-end">
                    @if ($dishes->onFirstPage())
                        <span
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500 sm:px-4">
                            <span class="hidden sm:inline">Предыдущая</span>
                            <span class="sm:hidden">←</span>
                        </span>
                    @else
                        <a href="{{ $dishes->previousPageUrl() }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 sm:px-4">
                            <span class="hidden sm:inline">Предыдущая</span>
                            <span class="sm:hidden">←</span>
                        </a>
                    @endif

                    @if ($dishes->hasMorePages())
                        <a href="{{ $dishes->nextPageUrl() }}"
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

