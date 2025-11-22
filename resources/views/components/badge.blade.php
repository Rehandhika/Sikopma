{{-- 
    DEPRECATED: This component is deprecated. 
    Please use <x-ui.badge> instead for the new design system.
    This file is kept for backward compatibility only.
    
    Migration:
    Old: <x-badge variant="primary">Text</x-badge>
    New: <x-ui.badge variant="primary">Text</x-ui.badge>
--}}

@props([
    'variant' => 'primary',
    'size' => 'md',
    'rounded' => false,
])

@php
$variants = [
    'primary' => 'bg-primary-100 text-primary-800 border-primary-200',
    'secondary' => 'bg-secondary-100 text-secondary-800 border-secondary-200',
    'success' => 'bg-success-50 text-success-700 border-success-200',
    'danger' => 'bg-danger-50 text-danger-700 border-danger-200',
    'warning' => 'bg-warning-50 text-warning-700 border-warning-200',
    'info' => 'bg-info-50 text-info-700 border-info-200',
    'gray' => 'bg-gray-100 text-gray-700 border-gray-200',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-base',
];
@endphp

<span {{ $attributes->merge([
    'class' => implode(' ', [
        'inline-flex items-center font-medium border',
        $rounded ? 'rounded-full' : 'rounded-md',
        $variants[$variant],
        $sizes[$size],
    ])
]) }}>
    {{ $slot }}
</span>
