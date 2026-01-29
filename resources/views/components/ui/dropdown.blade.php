@props([
    'align' => 'right',
    'width' => '48',
])

@php
// Alignment configuration - controls positioning and transform origin
$alignmentClasses = [
    'left' => 'origin-top-left left-0',
    'right' => 'origin-top-right right-0',
];

// Width configuration - supports 48, 56, and 64 unit widths
$widthClasses = [
    '48' => 'w-48',
    '56' => 'w-56',
    '64' => 'w-64',
];

// Validate alignment and width, fallback to defaults if invalid
$align = array_key_exists($align, $alignmentClasses) ? $align : 'right';
$width = array_key_exists($width, $widthClasses) ? $width : '48';
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false">
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
        class="absolute z-50 mt-2 {{ $widthClasses[$width] }} {{ $alignmentClasses[$align] }} rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-700"
        style="display: none;"
        @click.away="open = false"
    >
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
