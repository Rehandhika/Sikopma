@props([
    'headers' => [],
    'striped' => false,         // Alternating row colors
    'hoverable' => true,        // Row hover effect
    'compact' => false,         // Reduced padding
    'responsive' => true,       // Horizontal scroll on mobile
])

<div {{ $attributes->merge(['class' => $responsive ? 'overflow-x-auto' : '']) }}>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        @if(count($headers) > 0)
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                @foreach($headers as $header)
                <th scope="col" @class([
                    'text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider',
                    'px-6 py-3' => !$compact,
                    'px-4 py-2' => $compact,
                ])>
                    {{ $header }}
                </th>
                @endforeach
            </tr>
        </thead>
        @endif
        
        <tbody @class([
            'bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700',
            'divide-y-0' => $striped,
        ])>
            {{ $slot }}
        </tbody>
    </table>
</div>

@if($striped)
<style>
    tbody tr:nth-child(odd) {
        @apply bg-gray-50 dark:bg-gray-800/50;
    }
</style>
@endif

@if($hoverable)
<style>
    tbody tr {
        @apply transition-colors duration-150;
    }
    tbody tr:hover {
        @apply bg-gray-100 dark:bg-gray-800;
    }
</style>
@endif
