@props([
    'searchPlaceholder' => 'Cari...',
    'searchModel' => 'search',
    'showClear' => false,
])

<x-ui.card :padding="true" class="mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        {{-- Search input with icon --}}
        <div class="flex-1">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-ui.icon name="magnifying-glass" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="{{ $searchModel }}"
                    placeholder="{{ $searchPlaceholder }}"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent sm:text-sm"
                />
            </div>
        </div>

        {{-- Filter dropdowns slot --}}
        @isset($filters)
        <div class="flex flex-wrap gap-3">
            {{ $filters }}
        </div>
        @endisset

        {{-- Clear filters button --}}
        @if($showClear)
        <div class="flex items-center">
            <x-ui.button 
                variant="outline" 
                size="md"
                wire:click="clearFilters"
                icon="x-mark"
            >
                Clear Filters
            </x-ui.button>
        </div>
        @endif
    </div>

    {{-- Active filter indicators slot --}}
    @isset($activeFilters)
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm text-gray-600 dark:text-gray-400">Active filters:</span>
            {{ $activeFilters }}
        </div>
    </div>
    @endisset
</x-ui.card>
