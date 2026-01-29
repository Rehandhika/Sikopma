@props([
    'title' => '',
    'subtitle' => null,
    'backUrl' => null,
    'breadcrumbs' => [],
])

<div class="mb-6">
    {{-- Breadcrumbs --}}
    @if(count($breadcrumbs) > 0)
    <nav class="mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm">
            @foreach($breadcrumbs as $index => $crumb)
                <li class="flex items-center">
                    @if($index > 0)
                    <x-ui.icon name="chevron-right" class="w-4 h-4 text-gray-400 dark:text-gray-500 mx-2" />
                    @endif
                    @if(isset($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors">
                        {{ $crumb['label'] }}
                    </a>
                    @else
                    <span class="text-gray-900 dark:text-gray-100 font-medium" aria-current="page">{{ $crumb['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
    @endif

    {{-- Header with back button, title, and actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3 flex-1 min-w-0">
            {{-- Back button --}}
            @if($backUrl)
            <a href="{{ $backUrl }}" class="flex-shrink-0 mt-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                <x-ui.icon name="arrow-left" class="w-6 h-6" />
            </a>
            @endif

            {{-- Title and subtitle --}}
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl truncate">
                    {{ $title }}
                </h1>
                @if($subtitle)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ $subtitle }}
                </p>
                @endif
            </div>
        </div>

        {{-- Action buttons --}}
        @isset($actions)
        <div class="flex items-center space-x-3 flex-shrink-0">
            {{ $actions }}
        </div>
        @endisset
    </div>
</div>
