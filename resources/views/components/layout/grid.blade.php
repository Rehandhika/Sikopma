@props([
    'cols' => '1',
    'gap' => '6',
])

@php
// Responsive column configurations
$colsConfig = [
    '1' => 'grid-cols-1',
    '2' => 'grid-cols-1 md:grid-cols-2',
    '3' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
];

// Gap configurations
$gapConfig = [
    '2' => 'gap-2',
    '3' => 'gap-3',
    '4' => 'gap-4',
    '5' => 'gap-5',
    '6' => 'gap-6',
    '8' => 'gap-8',
];

$gridClasses = $colsConfig[$cols] ?? $colsConfig['1'];
$gapClasses = $gapConfig[$gap] ?? $gapConfig['6'];
@endphp

<div {{ $attributes->merge(['class' => 'grid ' . $gridClasses . ' ' . $gapClasses]) }}>
    {{ $slot }}
</div>
