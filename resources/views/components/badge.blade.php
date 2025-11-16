@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
$variantClasses = [
    'primary' => 'bg-indigo-100 text-indigo-800',
    'secondary' => 'bg-emerald-100 text-emerald-800',
    'danger' => 'bg-red-100 text-red-800',
    'warning' => 'bg-amber-100 text-amber-800',
    'info' => 'bg-cyan-100 text-cyan-800',
    'gray' => 'bg-gray-100 text-gray-800',
][$variant] ?? 'bg-indigo-100 text-indigo-800';

$sizeClasses = [
    'sm' => 'text-xs px-2 py-0.5',
    'md' => 'text-sm px-2.5 py-0.5',
    'lg' => 'text-base px-3 py-1',
][$size] ?? 'text-sm px-2.5 py-0.5';

$baseClasses = 'inline-flex items-center rounded-full font-medium';
@endphp

<span {{ $attributes->merge(['class' => "{$baseClasses} {$variantClasses} {$sizeClasses}"]) }}>
    {{ $slot }}
</span>
