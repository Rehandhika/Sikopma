@props([
    'showDate' => true,
    'showTime' => true,
    'showTimezone' => false,
    'format' => 'human', // 'human', 'short', 'full'
    'class' => '',
])

@php
    $dateTimeService = app(\App\Services\DateTimeSettingsService::class);
    $now = $dateTimeService->now();
    $timezone = $dateTimeService->getTimezone();
    
    $displayDate = match($format) {
        'human' => $dateTimeService->formatDateHuman($now),
        'short' => $dateTimeService->formatDate($now),
        'full' => $dateTimeService->formatDateTimeHuman($now),
        default => $dateTimeService->formatDate($now),
    };
    
    $displayTime = $dateTimeService->formatTime($now);
    $timezoneLabel = match($timezone) {
        'Asia/Jakarta' => 'WIB',
        'Asia/Makassar' => 'WITA',
        'Asia/Jayapura' => 'WIT',
        default => $timezone,
    };
@endphp

<div 
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 text-sm ' . $class]) }}
    x-data="{ 
        time: '{{ $displayTime }}',
        init() {
            setInterval(() => {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                this.time = hours + ':' + minutes;
            }, 1000);
        }
    }"
>
    @if($showDate)
        <span class="text-gray-600 dark:text-gray-400">{{ $displayDate }}</span>
    @endif
    
    @if($showTime)
        <span class="font-medium text-gray-900 dark:text-white" x-text="time"></span>
    @endif
    
    @if($showTimezone)
        <span class="text-xs text-gray-500 dark:text-gray-500">({{ $timezoneLabel }})</span>
    @endif
</div>
