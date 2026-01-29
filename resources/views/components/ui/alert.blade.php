@props([
    'variant' => 'info',
    'dismissible' => false,
    'icon' => true,
    'title' => null,
])

@php
$variants = [
    'success' => [
        'container' => 'bg-success-50 dark:bg-success-900/30 border-success-200 dark:border-success-700 text-success-800 dark:text-success-400',
        'icon' => 'check-circle',
        'iconColor' => 'text-success-400',
    ],
    'danger' => [
        'container' => 'bg-danger-50 dark:bg-danger-900/30 border-danger-200 dark:border-danger-700 text-danger-800 dark:text-danger-400',
        'icon' => 'x-circle',
        'iconColor' => 'text-danger-400',
    ],
    'warning' => [
        'container' => 'bg-warning-50 dark:bg-warning-900/30 border-warning-200 dark:border-warning-700 text-warning-800 dark:text-warning-400',
        'icon' => 'exclamation-triangle',
        'iconColor' => 'text-warning-400',
    ],
    'info' => [
        'container' => 'bg-info-50 dark:bg-info-900/30 border-info-200 dark:border-info-700 text-info-800 dark:text-info-400',
        'icon' => 'information-circle',
        'iconColor' => 'text-info-400',
    ],
];

$config = $variants[$variant];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    {{ $attributes->merge(['class' => 'border-l-4 p-4 rounded-lg ' . $config['container']]) }}
>
    <div class="flex items-start">
        @if($icon)
        <div class="flex-shrink-0">
            <x-ui.icon :name="$config['icon']" class="h-5 w-5 {{ $config['iconColor'] }}" />
        </div>
        @endif
        
        <div @class(['ml-3' => $icon, 'flex-1'])>
            @if($title)
            <h3 class="text-sm font-medium mb-1">
                {{ $title }}
            </h3>
            @endif
            <div class="text-sm">
                {{ $slot }}
            </div>
        </div>

        @if($dismissible)
        <button 
            @click="show = false"
            type="button" 
            class="ml-auto flex-shrink-0 inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 rounded-lg p-1 transition-colors"
        >
            <x-ui.icon name="x" class="h-5 w-5" />
        </button>
        @endif
    </div>
</div>
