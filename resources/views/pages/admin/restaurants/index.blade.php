@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Рестораны">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Рестораны</span>
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
                Список ресторанов
            </h3>
            @can('restaurant.create')
                <a href="{{ route('admin.restaurants.create') }}" class="inline-flex w-full sm:w-auto">
                    <x-ui.button variant="primary" className="w-full sm:w-auto">
                        Добавить ресторан
                    </x-ui.button>
                </a>
            @endcan
        </div>

        {{-- Мобильный вид: карточки --}}
        <div class="space-y-4 md:hidden">
            @forelse($restaurants as $restaurant)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                {{ $restaurant->name }}
                            </h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $restaurant->address }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $restaurant->city->name ?? '—' }}</span>
                                <span>•</span>
                                <span>{{ $restaurant->owner->name ?? '—' }}</span>
                                <span>•</span>
                                <span><a href="mailto:{{ $restaurant->owner->email ?? '' }}">{{ $restaurant->owner->email ?? '—' }}</a></span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <x-common.table-dropdown>
                                <x-slot:button>
                                    <button
                                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20"
                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M10.0003 10.8333C10.4606 10.8333 10.8337 10.4602 10.8337 9.99996C10.8337 9.53972 10.4606 9.16663 10.0003 9.16663C9.54009 9.16663 9.16699 9.53972 9.16699 9.99996C9.16699 10.4602 9.54009 10.8333 10.0003 10.8333Z"
                                                stroke="" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M10.0003 5.00004C10.4606 5.00004 10.8337 4.62694 10.8337 4.16671C10.8337 3.70647 10.4606 3.33337 10.0003 3.33337C9.54009 3.33337 9.16699 3.70647 9.16699 4.16671C9.16699 4.62694 9.54009 5.00004 10.0003 5.00004Z"
                                                stroke="" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M10.0003 16.6667C10.4606 16.6667 10.8337 16.2936 10.8337 15.8334C10.8337 15.3731 10.4606 15 10.0003 15C9.54009 15 9.16699 15.3731 9.16699 15.8334C9.16699 16.2936 9.54009 16.6667 10.0003 16.6667Z"
                                                stroke="" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </x-slot:button>
                                <x-slot:content>
                                    @can('restaurant.view')
                                        <a href="{{ route('admin.restaurants.show', $restaurant) }}"
                                            class="block rounded-lg px-3 py-2 text-center text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                            Просмотр
                                        </a>
                                    @endcan
                                    @can('restaurant.update')
                                        <a href="{{ route('admin.restaurants.edit', $restaurant) }}"
                                            class="block rounded-lg px-3 py-2 text-center text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                            Редактировать
                                        </a>
                                    @endcan
                                    @can('restaurant.delete')
                                        <form method="POST"
                                            action="{{ route('admin.restaurants.destroy', $restaurant) }}"
                                            onsubmit="return confirm('Вы уверены, что хотите удалить этот ресторан?');"
                                            class="block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full rounded-lg px-3 py-2 text-center text-sm text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-700">
                                                Удалить
                                            </button>
                                        </form>
                                    @endcan
                                </x-slot:content>
                            </x-common.table-dropdown>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-dark">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Рестораны не найдены
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
                            Адрес
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Город
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Владелец
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">
                            Email владельца
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90 text-right">
                            Действия
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($restaurants as $restaurant)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-4 text-sm text-gray-800 dark:text-white/90">
                                {{ $restaurant->name }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $restaurant->address }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="bg-orange-300/50 rounded-lg px-2 py-1">{{ $restaurant->city->name ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $restaurant->owner->name ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <a href="mailto:{{ $restaurant->owner->email ?? '' }}">{{ $restaurant->owner->email ?? '—' }}</a>
                            </td>
                            <td class="relative px-4 py-4 text-right">
                                <x-common.table-dropdown>
                                    <x-slot:button>
                                        <button
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M10.0003 10.8333C10.4606 10.8333 10.8337 10.4602 10.8337 9.99996C10.8337 9.53972 10.4606 9.16663 10.0003 9.16663C9.54009 9.16663 9.16699 9.53972 9.16699 9.99996C9.16699 10.4602 9.54009 10.8333 10.0003 10.8333Z"
                                                    stroke="" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path
                                                    d="M10.0003 5.00004C10.4606 5.00004 10.8337 4.62694 10.8337 4.16671C10.8337 3.70647 10.4606 3.33337 10.0003 3.33337C9.54009 3.33337 9.16699 3.70647 9.16699 4.16671C9.16699 4.62694 9.54009 5.00004 10.0003 5.00004Z"
                                                    stroke="" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path
                                                    d="M10.0003 16.6667C10.4606 16.6667 10.8337 16.2936 10.8337 15.8334C10.8337 15.3731 10.4606 15 10.0003 15C9.54009 15 9.16699 15.3731 9.16699 15.8334C9.16699 16.2936 9.54009 16.6667 10.0003 16.6667Z"
                                                    stroke="" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </x-slot:button>
                                    <x-slot:content>
                                        @can('restaurant.view')
                                            <a href="{{ route('admin.restaurants.show', $restaurant) }}"
                                                class="block rounded-lg px-3 py-2 text-center text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                                Просмотр
                                            </a>
                                        @endcan
                                        @can('restaurant.update')
                                            <a href="{{ route('admin.restaurants.edit', $restaurant) }}"
                                                class="block rounded-lg px-3 py-2 text-center text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                                Редактировать
                                            </a>
                                        @endcan
                                        @can('restaurant.delete')
                                            <form method="POST"
                                                action="{{ route('admin.restaurants.destroy', $restaurant) }}"
                                                onsubmit="return confirm('Вы уверены, что хотите удалить этот ресторан?');"
                                                class="block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-full rounded-lg px-3 py-2 text-center text-sm text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-700">
                                                    Удалить
                                                </button>
                                            </form>
                                        @endcan
                                    </x-slot:content>
                                </x-common.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Рестораны не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($restaurants->hasPages())
            <div class="mt-6 flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-center text-sm text-gray-600 dark:text-gray-400 sm:text-left">
                    Показано {{ $restaurants->firstItem() }} - {{ $restaurants->lastItem() }} из
                    {{ $restaurants->total() }} результатов
                </div>
                <div class="flex justify-center gap-2 sm:justify-end">
                    @if ($restaurants->onFirstPage())
                        <span
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500 sm:px-4">
                            <span class="hidden sm:inline">Предыдущая</span>
                            <span class="sm:hidden">←</span>
                        </span>
                    @else
                        <a href="{{ $restaurants->previousPageUrl() }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 sm:px-4">
                            <span class="hidden sm:inline">Предыдущая</span>
                            <span class="sm:hidden">←</span>
                        </a>
                    @endif

                    @if ($restaurants->hasMorePages())
                        <a href="{{ $restaurants->nextPageUrl() }}"
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

