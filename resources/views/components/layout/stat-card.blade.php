@props([
    'label' => '',
    'value' => '',
    'icon' => null,
    'iconColor' => 'bg-primary-100',
    'iconTextColor' => 'text-primary-600',
    'trend' => null,
    'trendUp' => true,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow duration-200']) }}>
    <div class="flex items-center">
        @if($icon)
        <div class="flex-shrink-0 {{ $iconColor }} rounded-lg p-3">
            <x-ui.icon :name="$icon" class="w-6 h-6 {{ $iconTextColor }}" />
        </div>
        @endif
        
        <div @class(['ml-5 flex-1' => $icon, 'flex-1' => !$icon])>
            <dt class="text-sm font-medium text-gray-500 truncate">{{ $label }}</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $value }}</dd>
            
            @if($subtitle)
            <dd class="mt-1 text-xs text-gray-500">{{ $subtitle }}</dd>
            @endif
            
            @if($trend)
            <dd class="mt-2 flex items-center text-sm">
                <span @class([
                    'font-medium flex items-center',
                    'text-success-600' => $trendUp,
                    'text-danger-600' => !$trendUp,
                ])>
                    @if($trendUp)
                    <x-ui.icon name="arrow-up" class="w-4 h-4 mr-1" />
                    @else
                    <x-ui.icon name="arrow-down" class="w-4 h-4 mr-1" />
                    @endif
                    {{ $trend }}
                </span>
            </dd>
            @endif
        </div>
    </div>
</div>
