@props([
    'header' => false,
    'compact' => false,
])

@if($header)
<th {{ $attributes->merge(['class' => implode(' ', array_filter([
    'text-left text-sm font-medium text-gray-900 dark:text-gray-100',
    $compact ? 'px-4 py-2' : 'px-6 py-4',
]))]) }}>
    {{ $slot }}
</th>
@else
<td {{ $attributes->merge(['class' => implode(' ', array_filter([
    'text-sm text-gray-700 dark:text-gray-300',
    $compact ? 'px-4 py-2' : 'px-6 py-4',
]))]) }}>
    {{ $slot }}
</td>
@endif
