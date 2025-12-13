@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Просмотр иконок">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Иконки</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-dark">
        <h3 class="mb-6 text-lg font-semibold text-gray-800 dark:text-white/90">
            Доступные иконки
        </h3>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach($icons as $iconName)
                <div class="flex flex-col items-center rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="mb-2 flex h-16 w-16 items-center justify-center text-gray-700 dark:text-gray-300">
                        {!! \App\Helpers\MenuHelper::getIconSvg($iconName) !!}
                    </div>
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $iconName }}</span>
                    <code class="mt-1 text-[10px] text-gray-500 dark:text-gray-500">'{{ $iconName }}'</code>
                </div>
            @endforeach
        </div>

        <div class="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <h4 class="mb-2 text-sm font-semibold text-blue-900 dark:text-blue-300">Как добавить свою иконку:</h4>
            <ol class="list-decimal list-inside space-y-1 text-sm text-blue-800 dark:text-blue-200">
                <li>Найдите SVG иконку на <a href="https://heroicons.com" target="_blank" class="underline">Heroicons</a>, <a href="https://feathericons.com" target="_blank" class="underline">Feather Icons</a> или создайте свою</li>
                <li>Откройте файл <code class="bg-blue-100 px-1 rounded dark:bg-blue-800">app/Helpers/MenuHelper.php</code></li>
                <li>Добавьте новую запись в массив <code class="bg-blue-100 px-1 rounded dark:bg-blue-800">$icons</code> в методе <code class="bg-blue-100 px-1 rounded dark:bg-blue-800">getIconSvg()</code></li>
                <li>Используйте название иконки в пункте меню</li>
            </ol>
        </div>
    </div>
@endsection

