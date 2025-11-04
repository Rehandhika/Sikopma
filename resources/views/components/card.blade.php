@props([
    'title' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title)
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
    </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if($footer)
    <div class="card-footer">
        {{ $footer }}
    </div>
    @endif
</div>
