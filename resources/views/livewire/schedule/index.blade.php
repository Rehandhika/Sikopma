<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Kalender Jadwal</h2>
        <div class="flex items-center space-x-2">
            <button wire:click="previousWeek" class="btn btn-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button wire:click="today" class="btn btn-white">Hari Ini</button>
            <button wire:click="nextWeek" class="btn btn-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Week Display -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-center text-lg font-semibold text-gray-900 mb-4">
            {{ $weekStart->locale('id')->isoFormat('D MMMM') }} - {{ $weekEnd->locale('id')->isoFormat('D MMMM YYYY') }}
        </div>

        <div class="grid grid-cols-7 gap-2">
            @foreach($days as $day)
                <div class="border rounded-lg {{ $day['date']->isToday() ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                    <div class="p-3 border-b {{ $day['date']->isToday() ? 'border-blue-500 bg-blue-100' : 'border-gray-200' }}">
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">{{ $day['date']->locale('id')->dayName }}</div>
                            <div class="text-lg font-bold {{ $day['date']->isToday() ? 'text-blue-600' : 'text-gray-900' }}">
                                {{ $day['date']->format('d') }}
                            </div>
                        </div>
                    </div>
                    <div class="p-2 space-y-1 min-h-[100px]">
                        @foreach($day['schedules'] as $schedule)
                            <div class="text-xs p-2 rounded bg-indigo-100 text-indigo-800 hover:bg-indigo-200 cursor-pointer">
                                <div class="font-medium">{{ $schedule->user->name }}</div>
                                <div class="text-indigo-600">Sesi {{ $schedule->session }}</div>
                                <div>{{ Carbon\Carbon::parse($schedule->time_start)->format('H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
