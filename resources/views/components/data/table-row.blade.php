@props([
    'compact' => false,
])

<tr {{ $attributes->merge(['class' => '']) }}>
    {{ $slot }}
</tr>
