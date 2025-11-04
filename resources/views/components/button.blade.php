@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'loading' => false,
])

@php
$classes = [
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'danger' => 'btn-danger',
    'warning' => 'btn-warning',
    'info' => 'btn-info',
    'white' => 'btn-white',
    'outline' => 'btn-outline',
    'ghost' => 'btn-ghost',
][$variant] ?? 'btn-primary';

$sizeClasses = [
    'sm' => 'btn-sm',
    'md' => '',
    'lg' => 'btn-lg',
][$size] ?? '';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "btn {$classes} {$sizeClasses}"]) }}
    @if($loading) disabled @endif
>
    @if($loading)
        <span class="spinner mr-2"></span>
    @elseif($icon)
        <x-icon :name="$icon" class="w-5 h-5 mr-2" />
    @endif

    {{ $slot }}
</button>
