<div x-data="{ isOpen: false }" @click.away="isOpen = false" class="relative z-50">
    <div @click="isOpen = !isOpen" class="cursor-pointer">
        {{ $button }}
    </div>

    <div 
        x-show="isOpen" 
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 z-[100] w-40 p-2 bg-white border border-gray-200 rounded-2xl shadow-lg dark:border-gray-800 dark:bg-gray-dark"
        style="display: none;"
        @click.stop
    >
        <div class="space-y-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
            {{ $content }}
        </div>
    </div>
</div>
