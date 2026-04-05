<div class="space-y-6">
    <x-layout.page-header title="Absensi Kehadiran" subtitle="Kelola catatan kehadiran dan waktu shift kerja Anda hari ini" />

    <div class="max-w-4xl mx-auto w-full">
        @if($currentSchedule || $isOverrideActive)
            <div class="space-y-6">
                {{-- Info Section --}}
                @if($currentSchedule)
                    @php
                        $statusConfig = [
                            'active' => [
                                'bg' => 'bg-success-50',
                                'border' => 'border-success-500',
                                'text' => 'text-success-800',
                                'icon' => 'text-success-500',
                                'badge' => 'success',
                                'label' => 'Sedang Berlangsung'
                            ],
                            'upcoming' => [
                                'bg' => 'bg-info-50',
                                'border' => 'border-info-500',
                                'text' => 'text-info-800',
                                'icon' => 'text-info-500',
                                'badge' => 'info',
                                'label' => 'Akan Datang'
                            ],
                            'past' => [
                                'bg' => 'bg-warning-50',
                                'border' => 'border-warning-500',
                                'text' => 'text-warning-800',
                                'icon' => 'text-warning-500',
                                'badge' => 'warning',
                                'label' => 'Sudah Lewat'
                            ]
                        ];
                        $status = $statusConfig[$scheduleStatus] ?? $statusConfig['active'];
                    @endphp
                    
                    <div class="{{ $status['bg'] }} border-l-4 {{ $status['border'] }} p-5 sm:p-6 rounded-xl shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start sm:items-center space-x-4">
                                <div class="p-3 bg-white/60 rounded-lg shrink-0">
                                    <x-ui.icon name="calendar" class="w-8 h-8 {{ $status['icon'] }}" />
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h2 class="text-xl font-bold {{ $status['text'] }}">Sesi {{ $currentSchedule->session }}</h2>
                                        <x-ui.badge :variant="$status['badge']" size="sm">{{ $status['label'] }}</x-ui.badge>
                                    </div>
                                    <p class="font-medium {{ $status['text'] }} opacity-90 mb-2">
                                        {{ $currentSchedule->session_label }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm {{ $status['text'] }} opacity-80">
                                        <div class="flex items-center">
                                            <x-ui.icon name="calendar" class="w-4 h-4 mr-1.5" />
                                            {{ $currentSchedule->day_label }}, {{ $currentSchedule->date->format('d M Y') }}
                                        </div>
                                        <div class="flex items-center">
                                            <x-ui.icon name="clock" class="w-4 h-4 mr-1.5" />
                                            {{ \Carbon\Carbon::parse($currentSchedule->time_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($currentSchedule->time_end)->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($scheduleStatus === 'upcoming')
                                <div class="shrink-0 bg-white/60 rounded-lg p-4 text-center min-w-[130px]">
                                    <p class="text-xs font-semibold uppercase tracking-wider {{ $status['text'] }} opacity-80 mb-1">Check-in dlm</p>
                                    <p class="text-xl font-bold {{ $status['text'] }}">{{ $timeUntilCheckIn }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($isOverrideActive)
                    <div class="bg-gray-50 border-l-4 border-gray-400 p-5 sm:p-6 rounded-xl shadow-sm text-gray-800">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white rounded-lg shrink-0 border border-gray-200">
                                <x-ui.icon name="information-circle" class="w-8 h-8 text-gray-500" />
                            </div>
                            <div>
                                <h2 class="text-xl font-bold mb-1">Mode Bebas Aktif</h2>
                                <p class="text-gray-600 text-sm">Anda dapat melakukan check-in dan check-out tanpa terikat pada jadwal kerja reguler. Waktu kerja akan tetap tercatat.</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Check-in Card --}}
                    <x-ui.card padding="false" class="overflow-hidden border-t-4 border-t-success-500">
                        <div class="p-8 sm:p-10 flex flex-col items-center justify-center text-center h-full">
                            @if($checkInTime)
                                <div class="w-16 h-16 bg-success-50 text-success-500 rounded-full flex items-center justify-center mb-4">
                                    <x-ui.icon name="check" class="w-8 h-8" />
                                </div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Waktu Check-In</p>
                                <h3 class="text-5xl font-bold text-gray-900 mb-4">{{ $checkInTime }}</h3>
                                <x-ui.badge variant="success" size="lg">Berhasil Tercatat</x-ui.badge>
                            @else
                                <div class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4">
                                    <x-ui.icon name="arrow-right-on-rectangle" class="w-8 h-8" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Mulai Sesi Kerja</h3>
                                
                                @if($canCheckIn)
                                    <p class="text-sm text-gray-500 mb-6">Tekan tombol di bawah ini untuk mencatat waktu kehadiran Anda.</p>
                                    <x-ui.button variant="success" wire:click="checkIn" class="w-full justify-center py-3 text-base">
                                        <x-ui.icon name="check-circle" class="w-5 h-5 mr-2" />
                                        <span wire:loading.remove wire:target="checkIn">Check-in Sekarang</span>
                                        <span wire:loading wire:target="checkIn">Memproses...</span>
                                    </x-ui.button>
                                @else
                                    <p class="text-sm text-gray-500 mb-6">Belum memasuki waktu check-in untuk jadwal ini.</p>
                                    <div class="px-6 py-3 bg-gray-50 rounded-lg text-gray-400 font-medium text-sm flex items-center justify-center w-full">
                                        <x-ui.icon name="lock-closed" class="w-5 h-5 mr-2" />
                                        Belum Tersedia
                                    </div>
                                @endif
                            @endif
                        </div>
                    </x-ui.card>

                    {{-- Check-out Card --}}
                    <x-ui.card padding="false" class="overflow-hidden border-t-4 border-t-info-500">
                        <div class="p-8 sm:p-10 flex flex-col items-center justify-center text-center h-full">
                            @if($checkOutTime)
                                <div class="w-16 h-16 bg-info-50 text-info-500 rounded-full flex items-center justify-center mb-4">
                                    <x-ui.icon name="check-circle" class="w-8 h-8" />
                                </div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Waktu Check-Out</p>
                                <h3 class="text-5xl font-bold text-gray-900 mb-4">{{ $checkOutTime }}</h3>
                                <x-ui.badge variant="info" size="lg">Sesi Selesai</x-ui.badge>
                            @elseif($checkInTime)
                                <div class="w-16 h-16 bg-info-50 text-info-500 rounded-full flex items-center justify-center mb-4">
                                    <x-ui.icon name="logout" class="w-8 h-8 ml-1" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Akhiri Sesi Kerja</h3>
                                <p class="text-sm text-gray-500 mb-6">Pastikan semua tugas selesai sebelum melakukan check-out.</p>
                                
                                <x-ui.button variant="info" wire:click="checkOut" class="w-full justify-center py-3 text-base">
                                    <x-ui.icon name="logout" class="w-5 h-5 mr-2" />
                                    <span wire:loading.remove wire:target="checkOut">Check-out Sekarang</span>
                                    <span wire:loading wire:target="checkOut">Memproses...</span>
                                </x-ui.button>
                            @else
                                <div class="w-16 h-16 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                    <x-ui.icon name="lock-closed" class="w-8 h-8" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-400 mb-2">Akhiri Sesi Kerja</h3>
                                <p class="text-sm text-gray-400 mb-6">Anda harus melakukan check-in terlebih dahulu.</p>
                            @endif
                        </div>
                    </x-ui.card>
                </div>
            </div>
        @else
            <x-ui.card>
                <div class="py-16 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100">
                        <x-ui.icon name="calendar" class="w-10 h-10 text-gray-400" />
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Jadwal Kosong</h2>
                    <p class="text-gray-500 max-w-md mx-auto mb-8 text-base">
                        Sepertinya Anda tidak memiliki jadwal kerja atau shift untuk saat ini.
                    </p>
                    <x-ui.button href="{{ route('admin.attendance.history') }}" variant="white">
                        Lihat Riwayat Absensi
                        <x-ui.icon name="arrow-right" class="w-4 h-4 ml-2" />
                    </x-ui.button>
                </div>
            </x-ui.card>
        @endif
    </div>
</div>
