<div class="space-y-6">
    {{-- Today Status Card --}}
    <div class="bg-gradient-to-r from-primary-600 to-indigo-600 rounded-lg shadow-lg text-white p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Absensi Hari Ini</h2>
                <p class="text-primary-100 mt-1">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>
            <div class="text-right">
                @if($todayStatus)
                    <div class="text-3xl font-bold">{{ $todayStatus->check_in->format('H:i') }}</div>
                    <div class="text-sm text-primary-100">Check-in</div>
                    @if($todayStatus->check_out)
                        <div class="text-xl font-semibold mt-2">{{ $todayStatus->check_out->format('H:i') }}</div>
                        <div class="text-xs text-primary-100">Check-out</div>
                    @endif
                @else
                    <div class="text-lg">Belum Absen</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Check-in/Check-out Actions --}}
    @if($currentSchedule)
        <x-ui.card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Jadwal Aktif</h3>
                    <p class="text-sm text-gray-600">
                        Sesi {{ $currentSchedule->session }} • 
                        {{ Carbon\Carbon::parse($currentSchedule->time_start)->format('H:i') }} - 
                        {{ Carbon\Carbon::parse($currentSchedule->time_end)->format('H:i') }}
                    </p>
                </div>
                <x-ui.badge variant="success" size="md" rounded="true">
                    Aktif
                </x-ui.badge>
            </div>

            <x-layout.grid cols="2" gap="4">
                @if($canCheckIn)
                    <x-ui.button 
                        variant="primary" 
                        size="lg" 
                        class="w-full"
                        wire:click="checkIn"
                    >
                        <x-ui.icon name="check-circle" class="w-5 h-5 mr-2" />
                        Check-in Sekarang
                    </x-ui.button>
                @elseif($canCheckOut)
                    <x-ui.button 
                        variant="danger" 
                        size="lg" 
                        class="w-full"
                        wire:click="checkOut"
                    >
                        <x-ui.icon name="logout" class="w-5 h-5 mr-2" />
                        Check-out Sekarang
                    </x-ui.button>
                @else
                    <div class="col-span-2">
                        <x-layout.empty-state
                            icon="check-circle"
                            title="Absensi hari ini sudah selesai"
                        />
                    </div>
                @endif
            </x-layout.grid>
        </x-ui.card>
    @else
        <x-ui.alert variant="warning">
            <div class="text-center">
                <x-ui.icon name="exclamation-circle" class="w-12 h-12 mx-auto mb-3 text-warning-600" />
                <p class="text-gray-700">Tidak ada jadwal untuk hari ini</p>
            </div>
        </x-ui.alert>
    @endif

    {{-- Monthly Stats --}}
    <x-layout.grid cols="4" gap="4">
        <x-layout.stat-card
            label="Total Hadir"
            :value="$monthlyStats['total']"
            icon="users"
            iconColor="bg-gray-100"
            iconTextColor="text-gray-600"
        />
        <x-layout.stat-card
            label="Tepat Waktu"
            :value="$monthlyStats['on_time']"
            icon="check-circle"
            iconColor="bg-success-100"
            iconTextColor="text-success-600"
        />
        <x-layout.stat-card
            label="Terlambat"
            :value="$monthlyStats['late']"
            icon="clock"
            iconColor="bg-warning-100"
            iconTextColor="text-warning-600"
        />
        <x-layout.stat-card
            label="Tidak Hadir"
            :value="$monthlyStats['absent']"
            icon="x-circle"
            iconColor="bg-danger-100"
            iconTextColor="text-danger-600"
        />
    </x-layout.grid>

    {{-- Recent Attendance --}}
    <x-ui.card title="Riwayat 7 Hari Terakhir">
        @if($recentAttendances->count() > 0)
            <div class="space-y-3">
                @foreach($recentAttendances as $attendance)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                {{ $attendance->status === 'present' ? 'bg-success-100 text-success-600' : 
                                   ($attendance->status === 'late' ? 'bg-warning-100 text-warning-600' : 'bg-danger-100 text-danger-600') }}">
                                <x-ui.icon name="check-circle" class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">
                                    {{ $attendance->check_in->locale('id')->isoFormat('dddd, D MMM') }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ $attendance->check_in->format('H:i') }}
                                    @if($attendance->check_out)
                                        - {{ $attendance->check_out->format('H:i') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <x-ui.badge 
                            :variant="$attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger')"
                            size="sm"
                            rounded="true"
                        >
                            {{ ucfirst($attendance->status) }}
                        </x-ui.badge>
                    </div>
                @endforeach
            </div>
        @else
            <x-layout.empty-state
                icon="document-text"
                title="Belum ada riwayat absensi"
            />
        @endif
    </x-ui.card>
</div>
