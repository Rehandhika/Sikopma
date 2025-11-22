@props([
    'type' => 'text',
    'width' => 'w-full',
    'height' => 'h-4',
])

@php
$typeClasses = [
    'text' => 'rounded',
    'circle' => 'rounded-full',
    'rectangle' => 'rounded-lg',
];
@endphp

<div {{ $attributes->merge([
    'class' => implode(' ', [
        'animate-pulse bg-gray-200',
        $typeClasses[$type],
        $width,
        $height,
    ])
]) }}></div>
