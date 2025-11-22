@props([
    'icon' => 'inbox',
    'title' => 'Tidak ada data',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12']) }}>
    <x-ui.icon :name="$icon" class="mx-auto h-12 w-12 text-gray-400" />
    <h3 class="mt-4 text-sm font-medium text-gray-900">{{ $title }}</h3>
    @if($description)
    <p class="mt-2 text-sm text-gray-500">{{ $description }}</p>
    @endif
    @isset($action)
    <div class="mt-6">
        {{ $action }}
    </div>
    @endisset
</div>
