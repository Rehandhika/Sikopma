@props([
    'defaultTab' => 0,
])

<div 
    x-data="{ activeTab: {{ $defaultTab }} }"
    {{ $attributes->merge(['class' => 'w-full']) }}
>
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            {{ $tabs }}
        </nav>
    </div>

    <!-- Tab Panels -->
    <div class="mt-4">
        {{ $slot }}
    </div>
</div>
