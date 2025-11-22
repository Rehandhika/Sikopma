@props([
    'align' => 'right',
    'width' => '48',
])

@php
$alignmentClasses = [
    'left' => 'origin-top-left left-0',
    'right' => 'origin-top-right right-0',
];

$widthClasses = [
    '48' => 'w-48',
    '56' => 'w-56',
    '64' => 'w-64',
];
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <!-- Trigger -->
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widthClasses[$width] }} {{ $alignmentClasses[$align] }} rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5"
        style="display: none;"
    >
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
