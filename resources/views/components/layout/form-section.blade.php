@props([
    'title' => '',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @if($title)
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
        @if($description)
        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @endif
    
    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>
