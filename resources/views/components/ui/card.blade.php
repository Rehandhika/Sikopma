@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'shadow' => 'md',
    'hover' => false,
    'clickable' => false,
    'bordered' => true,
])

@php
$shadows = [
    'none' => '',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
];

// Build base classes
$baseClasses = 'bg-white dark:bg-gray-800 rounded-xl overflow-hidden transition-all duration-200';

// Add border classes (Requirement 8.2)
if ($bordered) {
    $baseClasses .= ' border border-gray-200 dark:border-gray-700';
}

// Add shadow
$baseClasses .= ' ' . $shadows[$shadow];

// Add hover state (Requirement 8.6)
if ($hover) {
    $baseClasses .= ' hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600';
}

// Add clickable cursor (Requirement 8.6)
if ($clickable) {
    $baseClasses .= ' cursor-pointer';
}
@endphp

<div {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{-- Header slot (Requirement 8.4) --}}
    @isset($header)
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        {{ $header }}
    </div>
    @endisset

    {{-- Legacy title/subtitle support for backward compatibility --}}
    @if($title || $subtitle)
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        @if($title)
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
        @endif
        @if($subtitle)
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>
    @endif

    {{-- Main content (Requirement 8.3) --}}
    <div @class([
        'px-6 py-4' => $padding,
        'text-gray-900 dark:text-gray-100' => true,
    ])>
        {{ $slot }}
    </div>

    {{-- Footer slot (Requirement 8.4) --}}
    @isset($footer)
    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
        {{ $footer }}
    </div>
    @endisset
</div>
