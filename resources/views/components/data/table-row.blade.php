@props([
    'striped' => true,
    'hoverable' => true,
])

<tr {{ $attributes->merge([
    'class' => implode(' ', array_filter([
        $striped ? 'odd:bg-white even:bg-gray-50' : '',
        $hoverable ? 'hover:bg-gray-100 transition-colors' : '',
    ]))
]) }}>
    {{ $slot }}
</tr>
