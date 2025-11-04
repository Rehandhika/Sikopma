<div class="p-6">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Jadwal Saya</h1>
        <p class="mt-1 text-sm text-gray-600">Lihat jadwal shift Anda minggu ini</p>
    </div>

    {{-- Week Navigation --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex items-center justify-between">
            <button wire:click="previousWeek" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Minggu Lalu
            </button>

            <div class="text-center">
                <div class="text-lg font-semibold text-gray-900">
                    {{ $currentWeekStart->locale('id')->format('d M') }} - {{ $currentWeekEnd->locale('id')->format('d M Y') }}
                </div>
                @if($weekOffset === 0)
                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full mt-1">
                    Minggu Ini
                </span>
                @endif
            </div>

            <button wire:click="nextWeek" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                Minggu Depan
                <svg class="w-5 h-5 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        @if($weekOffset !== 0)
        <div class="mt-3 text-center">
            <button wire:click="currentWeek" class="text-sm text-blue-600 hover:text-blue-800">
                Kembali ke Minggu Ini
            </button>
        </div>
        @endif
    </div>

    {{-- Weekly Schedule Grid --}}
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <div class="min-w-full">
                @foreach($weekDays as $day)
                <div @class([
                    'p-4 border-b border-gray-200',
                    'bg-blue-50' => $day['isToday'],
                    'bg-gray-50' => $day['isPast'],
                ])>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">
                                {{ $day['dayName'] }}
                            </h3>
                            <p class="text-xs text-gray-600">
                                {{ $day['date']->locale('id')->format('d M Y') }}
                            </p>
                        </div>
                        @if($day['isToday'])
                        <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                            Hari Ini
                        </span>
                        @endif
                    </div>

                    @php
                        $dateKey = $day['date']->format('Y-m-d');
                        $assignments = $mySchedules[$dateKey] ?? collect();
                    @endphp

                    @if($assignments->count() > 0)
                        <div class="space-y-2">
                            @foreach($assignments as $assignment)
                            <div @class([
                                'p-3 rounded-lg border-l-4',
                                'bg-green-50 border-green-500' => $assignment->shift === 'pagi',
                                'bg-yellow-50 border-yellow-500' => $assignment->shift === 'siang',
                                'bg-purple-50 border-purple-500' => $assignment->shift === 'sore',
                                'bg-gray-100 border-gray-400' => !in_array($assignment->shift, ['pagi', 'siang', 'sore']),
                            ])>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900 capitalize">
                                            Shift {{ ucfirst($assignment->shift) }}
                                        </div>
                                        @if($assignment->time_start && $assignment->time_end)
                                        <div class="text-sm text-gray-600 mt-1">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ Carbon\Carbon::parse($assignment->time_start)->format('H:i') }} - 
                                            {{ Carbon\Carbon::parse($assignment->time_end)->format('H:i') }}
                                        </div>
                                        @endif
                                    </div>
                                    @if(!$day['isPast'])
                                    <a href="{{ route('swap.create') }}" class="text-xs text-blue-600 hover:text-blue-800">
                                        Tukar
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-500 text-sm">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Tidak ada jadwal
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Upcoming Schedules Summary --}}
    @if($upcomingSchedules->count() > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Mendatang (7 Hari)</h3>
        <div class="space-y-3">
            @foreach($upcomingSchedules as $schedule)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div @class([
                        'w-12 h-12 rounded-lg flex items-center justify-center text-white font-semibold',
                        'bg-green-500' => $schedule->shift === 'pagi',
                        'bg-yellow-500' => $schedule->shift === 'siang',
                        'bg-purple-500' => $schedule->shift === 'sore',
                        'bg-gray-500' => !in_array($schedule->shift, ['pagi', 'siang', 'sore']),
                    ])>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">
                            {{ $schedule->date->locale('id')->format('l, d M Y') }}
                        </div>
                        <div class="text-sm text-gray-600">
                            Shift {{ ucfirst($schedule->shift) }}
                            @if($schedule->time_start && $schedule->time_end)
                                • {{ Carbon\Carbon::parse($schedule->time_start)->format('H:i') }} - 
                                {{ Carbon\Carbon::parse($schedule->time_end)->format('H:i') }}
                            @endif
                        </div>
                    </div>
                </div>
                @if($schedule->date->isFuture())
                <span class="text-xs text-gray-500">
                    {{ $schedule->date->diffForHumans() }}
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Loading State --}}
    <div wire:loading class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-700 font-medium">Memuat jadwal...</p>
        </div>
    </div>
</div>
