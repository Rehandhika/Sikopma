@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'shadow' => 'md',
])

@php
$shadows = [
    'none' => '',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 overflow-hidden ' . $shadows[$shadow]]) }}>
    @if($title || $subtitle)
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        @if($title)
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        @endif
        @if($subtitle)
        <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    @endif

    <div @class(['px-6 py-4' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        {{ $footer }}
    </div>
    @endisset
</div>
