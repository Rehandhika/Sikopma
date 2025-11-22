@props([
    'href' => null,
    'icon' => null,
])

@if($href)
<a 
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors']) }}
>
    @if($icon)
    <x-ui.icon :name="$icon" class="w-5 h-5 mr-3 text-gray-400" />
    @endif
    {{ $slot }}
</a>
@else
<button
    {{ $attributes->merge(['class' => 'w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors text-left']) }}
>
    @if($icon)
    <x-ui.icon :name="$icon" class="w-5 h-5 mr-3 text-gray-400" />
    @endif
    {{ $slot }}
</button>
@endif
