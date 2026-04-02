<div class="max-w-2xl mx-auto">
    <x-ui.card title="Absensi Hari Ini">
        @if($currentSchedule)
            {{-- Schedule Info with Status --}}
            <div class="mb-6">
                @php
                    $statusConfig = [
                        'active' => [
                            'bg' => 'bg-success-50',
                            'border' => 'border-success-500',
                            'text' => 'text-success-800',
                            'icon' => 'text-success-400',
                            'badge' => 'bg-success-100 text-success-800',
                            'label' => 'Sedang Berlangsung'
                        ],
                        'upcoming' => [
                            'bg' => 'bg-info-50',
                            'border' => 'border-info-500',
                            'text' => 'text-info-800',
                            'icon' => 'text-info-400',
                            'badge' => 'bg-info-100 text-info-800',
                            'label' => 'Akan Datang'
                        ],
                        'past' => [
                            'bg' => 'bg-warning-50',
                            'border' => 'border-warning-500',
                            'text' => 'text-warning-800',
                            'icon' => 'text-warning-400',
                            'badge' => 'bg-warning-100 text-warning-800',
                            'label' => 'Sudah Lewat'
                        ]
                    ];
                    $status = $statusConfig[$scheduleStatus] ?? $statusConfig['active'];
                @endphp
                
                <div class="{{ $status['bg'] }} border-l-4 {{ $status['border'] }} p-4 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start flex-1">
                            <div class="flex-shrink-0">
                                <x-ui.icon name="clock" class="h-5 w-5 {{ $status['icon'] }}" />
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-sm font-medium {{ $status['text'] }}">
                                        Jadwal: {{ $currentSchedule->day_label }}, {{ $currentSchedule->date->format('d M Y') }}
                                    </h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $status['badge'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </div>
                                <div class="mt-2 text-sm {{ $status['text'] }}">
                                    <p>Sesi {{ $currentSchedule->session }}: {{ $currentSchedule->session_label }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Message for Upcoming --}}
            @if($scheduleStatus === 'upcoming')
                <div class="mb-6">
                    <x-ui.alert variant="info">
                        <div class="flex items-center">
                            <x-ui.icon name="info-circle" class="h-5 w-5 mr-2" />
                            <span>Check-in tersedia {{ $timeUntilCheckIn }}.</span>
                        </div>
                    </x-ui.alert>
                </div>
            @endif
        @elseif($isOverrideActive)
            <div class="mb-6">
                <x-ui.alert variant="info">
                    <div class="flex items-center">
                        <x-ui.icon name="info-circle" class="h-5 w-5 mr-2" />
                        <span>Mode check-in bebas aktif.</span>
                    </div>
                </x-ui.alert>
            </div>
        @endif

        @if($currentSchedule || $isOverrideActive)
            {{-- Check-in/Check-out Section --}}
            <x-layout.grid cols="2" gap="6" class="mb-6">
                {{-- Check-in Card --}}
                <x-ui.card padding="true" shadow="sm">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Check-in</h4>
                    @if($checkInTime)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-success-600 mb-2">{{ $checkInTime }}</div>
                            <p class="text-sm text-gray-500 mb-3">Sudah check-in</p>
                        </div>
                    @else
                        <div>
                            @if($canCheckIn)
                                <x-ui.button 
                                    variant="success" 
                                    wire:click="checkIn" 
                                    class="w-full"
                                >
                                    <x-ui.icon name="check-circle" class="w-5 h-5 mr-2" />
                                    <span wire:loading.remove wire:target="checkIn">Check-in</span>
                                    <span wire:loading wire:target="checkIn">
                                        <x-ui.icon name="arrow-path" class="w-4 h-4 inline animate-spin mr-2" />
                                        Memproses...
                                    </span>
                                </x-ui.button>
                            @else
                                <div class="text-center text-gray-500">
                                    <x-ui.icon name="clock" class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                                    <p class="text-sm font-medium">Belum waktunya check-in</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </x-ui.card>

                {{-- Check-out Card --}}
                <x-ui.card padding="true" shadow="sm">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Check-out</h4>
                    @if($checkOutTime)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-info-600">{{ $checkOutTime }}</div>
                            <p class="text-sm text-gray-500">Sudah check-out</p>
                        </div>
                    @elseif($checkInTime)
                        <div class="text-center">
                            <x-ui.button variant="info" wire:click="checkOut" class="w-full">
                                <x-ui.icon name="logout" class="h-5 w-5 mr-2" />
                                <span>Check-out</span>
                            </x-ui.button>
                        </div>
                    @else
                        <div class="text-center text-gray-500">
                            <x-ui.icon name="lock-closed" class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                            <p class="text-sm">Check-in dahulu</p>
                        </div>
                    @endif
                </x-ui.card>
            </x-layout.grid>
        @else
            <x-layout.empty-state icon="calendar" title="Tidak ada jadwal" description="Tidak ada jadwal kerja untuk Anda saat ini." />
        @endif
    </x-ui.card>
</div>


