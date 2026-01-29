@props([
    'name' => 'modal',
    'title' => '',
    'subtitle' => null,
    'maxWidth' => 'lg',
    'closeable' => true,
    'closeOnEscape' => true,
    'closeOnClickOutside' => true,
    'persistent' => false,
])

@php
$maxWidths = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
    '4xl' => 'max-w-4xl',
    'full' => 'max-w-full',
];
@endphp

<div
    x-data="{ show: false, processing: false }"
    x-on:open-modal-{{ $name }}.window="show = true; processing = false"
    x-on:close-modal-{{ $name }}.window="show = false; processing = false"
    @if($closeable && $closeOnEscape) x-on:keydown.escape.window="if (!processing || !{{ $persistent ? 'true' : 'false' }}) { show = false }" @endif
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    x-init="$watch('show', value => {
        if (value) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    })"
>
    <!-- Backdrop -->
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80"
        @if($closeable && $closeOnClickOutside) @click="if (!processing || !{{ $persistent ? 'true' : 'false' }}) { show = false }" @endif
    ></div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 transform translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 transform translate-y-4 sm:translate-y-0 sm:scale-95"
            {{ $attributes->merge(['class' => 'inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full ' . $maxWidths[$maxWidth]]) }}
        >
            <!-- Header -->
            @if($title)
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                    @if($subtitle)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
                    @endif
                </div>
                @if($closeable)
                <button 
                    @click="if (!processing || !{{ $persistent ? 'true' : 'false' }}) { show = false }"
                    type="button"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-1"
                    :disabled="processing && {{ $persistent ? 'true' : 'false' }}"
                    :class="{ 'opacity-50 cursor-not-allowed': processing && {{ $persistent ? 'true' : 'false' }} }"
                >
                    <x-ui.icon name="x" class="w-5 h-5" />
                </button>
                @endif
            </div>
            @endif

            <!-- Body -->
            <div class="px-6 py-4 dark:text-gray-200">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @isset($footer)
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex items-center justify-end space-x-3">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </div>
</div>
