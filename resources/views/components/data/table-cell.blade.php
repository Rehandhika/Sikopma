@props([
    'header' => false,
])

@if($header)
<th {{ $attributes->merge(['class' => 'px-6 py-4 text-left text-sm font-medium text-gray-900']) }}>
    {{ $slot }}
</th>
@else
<td {{ $attributes->merge(['class' => 'px-6 py-4 text-sm text-gray-700']) }}>
    {{ $slot }}
</td>
@endif
