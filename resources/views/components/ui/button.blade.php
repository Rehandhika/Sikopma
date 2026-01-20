@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'href' => null,
])

@php
$variants = [
    'primary' => 'bg-primary-600 hover:bg-primary-700 text-white focus:ring-primary-500',
    'secondary' => 'bg-secondary-600 hover:bg-secondary-700 text-white focus:ring-secondary-500',
    'success' => 'bg-success-500 hover:bg-success-700 text-white focus:ring-success-500',
    'danger' => 'bg-danger-500 hover:bg-danger-700 text-white focus:ring-danger-500',
    'warning' => 'bg-warning-500 hover:bg-warning-600 text-white focus:ring-warning-500',
    'info' => 'bg-info-500 hover:bg-info-700 text-white focus:ring-info-500',
    'white' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-primary-500',
    'outline' => 'bg-transparent border-2 border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500',
    'ghost' => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
];

$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$isDisabled = $loading || $disabled;
@endphp

@if($href && !$isDisabled)
<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => implode(' ', [$baseClasses, $variants[$variant], $sizes[$size]])]) }}
>
    @if($loading)
        <x-ui.spinner class="mr-2" size="sm" color="white" />
    @elseif($icon)
        <x-ui.icon :name="$icon" class="mr-2 w-5 h-5" />
    @endif
    {{ $slot }}
</a>
@else
<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => implode(' ', [$baseClasses, $variants[$variant], $sizes[$size]])]) }}
    @if($isDisabled) disabled @endif
>
    @if($loading)
        <x-ui.spinner class="mr-2" size="sm" color="white" />
    @elseif($icon)
        <x-ui.icon :name="$icon" class="mr-2 w-5 h-5" />
    @endif
    {{ $slot }}
</button>
@endif
