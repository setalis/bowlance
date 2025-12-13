@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Добавить ресторан">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('admin.restaurants.index') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Рестораны</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Добавить ресторан</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    @if (session('status'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('status')" />
        </div>
    @endif

    @can('restaurant.create')
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark md:p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Создание нового ресторана
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Заполните форму для добавления нового ресторана в систему
                </p>
            </div>

            <form method="POST" action="{{ route('admin.restaurants.store') }}" class="space-y-6">
                @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Название ресторана -->
                <div>
                    <x-forms.input
                        name="name"
                        label="Название ресторана"
                        type="text"
                        placeholder="Введите название ресторана"
                        :value="old('name')"
                        required
                        autofocus
                    />
                </div>

                <!-- Адрес -->
                <div>
                    <x-forms.input
                        name="address"
                        label="Адрес"
                        type="text"
                        placeholder="Введите адрес ресторана"
                        :value="old('address')"
                        required
                    />
                </div>

                <!-- Город -->
                <div>
                    <label for="city_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Город <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="city_id"
                        name="city_id"
                        required
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('city_id') border-red-500 @enderror"
                    >
                        <option value="">Выберите город</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('city_id')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Владелец -->
                <div>
                    <label for="owner_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Владелец <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="owner_id"
                        name="owner_id"
                        required
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('owner_id') border-red-500 @enderror"
                    >
                        <option value="">Выберите владельца</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }} ({{ $owner->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('owner_id')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.restaurants.index') }}" class="inline-flex w-full sm:w-auto">
                    <x-ui.button variant="outline" className="w-full sm:w-auto">
                        Отмена
                    </x-ui.button>
                </a>
                <x-ui.button type="submit" variant="primary" className="w-full sm:w-auto">
                    Создать ресторан
                </x-ui.button>
            </div>
        </form>
    </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-dark md:p-6">
            <x-ui.alert variant="error" message="У вас нет прав для создания ресторанов." />
        </div>
    @endcan
@endsection

