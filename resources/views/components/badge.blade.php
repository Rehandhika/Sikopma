@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
$classes = [
    'primary' => 'badge-primary',
    'secondary' => 'badge-secondary',
    'danger' => 'badge-danger',
    'warning' => 'badge-warning',
    'info' => 'badge-info',
    'gray' => 'badge-gray',
][$variant] ?? 'badge-primary';

$sizeClasses = [
    'sm' => 'text-xs px-2 py-0.5',
    'md' => 'text-sm px-2.5 py-0.5',
    'lg' => 'text-base px-3 py-1',
][$size] ?? 'text-sm px-2.5 py-0.5';
@endphp

<span {{ $attributes->merge(['class' => "badge {$classes} {$sizeClasses}"]) }}>
    {{ $slot }}
</span>
