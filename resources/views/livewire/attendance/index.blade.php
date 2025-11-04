<div class="space-y-6" x-data="attendanceLocation()">
    <!-- Today Status Card -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg text-white p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Absensi Hari Ini</h2>
                <p class="text-blue-100 mt-1">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>
            <div class="text-right">
                @if($todayStatus)
                    <div class="text-3xl font-bold">{{ $todayStatus->check_in->format('H:i') }}</div>
                    <div class="text-sm text-blue-100">Check-in</div>
                    @if($todayStatus->check_out)
                        <div class="text-xl font-semibold mt-2">{{ $todayStatus->check_out->format('H:i') }}</div>
                        <div class="text-xs text-blue-100">Check-out</div>
                    @endif
                @else
                    <div class="text-lg">Belum Absen</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Check-in/Check-out Actions -->
    @if($currentSchedule)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Jadwal Aktif</h3>
                    <p class="text-sm text-gray-600">
                        Sesi {{ $currentSchedule->session }} • 
                        {{ Carbon\Carbon::parse($currentSchedule->time_start)->format('H:i') }} - 
                        {{ Carbon\Carbon::parse($currentSchedule->time_end)->format('H:i') }}
                    </p>
                </div>
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                    Aktif
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($canCheckIn)
                    <button 
                        wire:click="checkIn" 
                        @click="getLocation()"
                        class="btn btn-primary btn-lg w-full"
                        :disabled="!hasLocation"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Check-in Sekarang
                    </button>
                @elseif($canCheckOut)
                    <button wire:click="checkOut" class="btn btn-danger btn-lg w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Check-out Sekarang
                    </button>
                @else
                    <div class="col-span-2 text-center py-4 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>Absensi hari ini sudah selesai</p>
                    </div>
                @endif
            </div>

            <div class="mt-4 text-xs text-gray-500 flex items-center" x-show="hasLocation">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Lokasi terdeteksi
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-700">Tidak ada jadwal untuk hari ini</p>
        </div>
    @endif

    <!-- Monthly Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $monthlyStats['total'] }}</div>
            <div class="text-sm text-gray-600">Total Hadir</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-green-600">{{ $monthlyStats['on_time'] }}</div>
            <div class="text-sm text-gray-600">Tepat Waktu</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $monthlyStats['late'] }}</div>
            <div class="text-sm text-gray-600">Terlambat</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-red-600">{{ $monthlyStats['absent'] }}</div>
            <div class="text-sm text-gray-600">Tidak Hadir</div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Riwayat 7 Hari Terakhir</h3>
        </div>
        <div class="p-6">
            @if($recentAttendances->count() > 0)
                <div class="space-y-3">
                    @foreach($recentAttendances as $attendance)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    {{ $attendance->status === 'present' ? 'bg-green-100 text-green-600' : 
                                       ($attendance->status === 'late' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
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
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $attendance->status === 'present' ? 'bg-green-100 text-green-800' : 
                                   ($attendance->status === 'late' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>Belum ada riwayat absensi</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function attendanceLocation() {
            return {
                hasLocation: false,
                init() {
                    this.getLocation();
                },
                getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                this.hasLocation = true;
                                @this.call('updateLocation', position.coords.latitude, position.coords.longitude);
                            },
                            (error) => {
                                this.hasLocation = false;
                                console.error('Geolocation error:', error);
                            }
                        );
                    }
                }
            }
        }
    </script>
</div>
