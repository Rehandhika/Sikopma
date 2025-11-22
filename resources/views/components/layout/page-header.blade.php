@props([
    'title' => '',
    'description' => null,
    'breadcrumbs' => [],
])

<div class="mb-6">
    @if(count($breadcrumbs) > 0)
    <nav class="mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm">
            @foreach($breadcrumbs as $index => $crumb)
                <li class="flex items-center">
                    @if($index > 0)
                    <x-ui.icon name="chevron-right" class="w-4 h-4 text-gray-400 mx-2" />
                    @endif
                    @if(isset($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                        {{ $crumb['label'] }}
                    </a>
                    @else
                    <span class="text-gray-900 font-medium" aria-current="page">{{ $crumb['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl truncate">
                {{ $title }}
            </h1>
            @if($description)
            <p class="mt-2 text-sm text-gray-600">
                {{ $description }}
            </p>
            @endif
        </div>

        @isset($actions)
        <div class="flex items-center space-x-3 flex-shrink-0">
            {{ $actions }}
        </div>
        @endisset
    </div>
</div>
