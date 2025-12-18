<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-lg rounded-lg">
        <div class="p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                            @if(auth()->check())
                                <span class="text-white font-bold text-2xl">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            @else
                                <span class="text-white font-bold text-2xl">?</span>
                            @endif
                        </div>
                    </div>
                    <div class="ml-5">
                        @if(auth()->check())
                            <h2 class="text-2xl font-bold">
                                Selamat datang, {{ auth()->user()->name }}!
                            </h2>
                            <p class="text-indigo-100 mt-1">
                                NIM: {{ auth()->user()->nim }} • 
                                @foreach(auth()->user()->roles as $role)
                                    <span class="capitalize">{{ $role->name }}</span>{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </p>
                        @else
                            <h2 class="text-2xl font-bold">
                                Selamat datang di SIKOPMA
                            </h2>
                            <p class="text-indigo-100 mt-1">
                                Silakan login untuk melihat statistik pribadi.
                            </p>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-indigo-100">{{ now()->isoFormat('dddd') }}</p>
                    <p class="text-lg font-semibold">{{ now()->isoFormat('D MMMM Y') }}</p>
                    <p class="text-2xl font-bold mt-1" id="current-time">{{ now()->format('H:i:s') }}</p>
                    <p class="text-xs text-indigo-100">Waktu Portugal (WET/WEST)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Stats Cards -->
    <x-layout.grid cols="4" class="gap-5">
        <x-layout.stat-card
            label="Kehadiran Bulan Ini"
            value="{{ $userStats['monthlyAttendance']['present'] }}/{{ $userStats['monthlyAttendance']['total'] }}"
            icon="check-circle"
            iconColor="bg-green-100"
            iconTextColor="text-green-600"
        />

        <x-layout.stat-card
            label="Terlambat"
            value="{{ $userStats['monthlyAttendance']['late'] }}"
            icon="clock"
            iconColor="bg-yellow-100"
            iconTextColor="text-yellow-600"
        />

        <x-layout.stat-card
            label="Penalti Aktif"
            value="{{ $userStats['penalties']['count'] }}"
            subtitle="{{ $userStats['penalties']['points'] }} poin"
            icon="exclamation-triangle"
            iconColor="bg-red-100"
            iconTextColor="text-red-600"
        />

        <x-layout.stat-card
            label="Notifikasi"
            value="{{ $userStats['notifications']->count() }}"
            icon="bell"
            iconColor="bg-blue-100"
            iconTextColor="text-blue-600"
        />
    </x-layout.grid>

    @if($isAdmin)
    <!-- Admin Stats -->
    <x-ui.card title="Statistik Hari Ini (Admin)">
        <x-layout.grid cols="4" class="gap-4">
            <div class="border-l-4 border-green-500 pl-4">
                <p class="text-sm text-gray-600">Kehadiran</p>
                <p class="text-2xl font-bold text-gray-900">{{ $adminStats['todayAttendance']['present'] }}/{{ $adminStats['todayAttendance']['total'] }}</p>
            </div>
            <div class="border-l-4 border-blue-500 pl-4">
                <p class="text-sm text-gray-600">Penjualan</p>
                <p class="text-2xl font-bold text-gray-900">{{ format_currency($adminStats['todaySales']) }}</p>
                <p class="text-xs text-gray-500">{{ $adminStats['todayTransactions'] }} transaksi</p>
            </div>
            <div class="border-l-4 border-yellow-500 pl-4">
                <p class="text-sm text-gray-600">Stok Rendah</p>
                <p class="text-2xl font-bold text-gray-900">{{ $adminStats['lowStockProducts'] }}</p>
            </div>
            <div class="border-l-4 border-red-500 pl-4">
                <p class="text-sm text-gray-600">Persetujuan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $adminStats['pendingLeaves'] + $adminStats['pendingSwaps'] }}</p>
                <p class="text-xs text-gray-500">{{ $adminStats['pendingLeaves'] }} cuti, {{ $adminStats['pendingSwaps'] }} swap</p>
            </div>
        </x-layout.grid>
    </x-ui.card>
    @endif

    <!-- Main Content Grid -->
    <x-layout.grid cols="2" class="gap-6">
        <!-- Today's Schedule Detail -->
        <x-ui.card title="Jadwal Hari Ini">
            @if($userStats['todaySchedule'])
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <x-ui.icon name="clock" class="h-5 w-5 text-blue-600" />
                        </div>
                        <div class="ml-3 flex-1">
                            <h4 class="text-sm font-medium text-blue-900">Sesi {{ $userStats['todaySchedule']->session }}</h4>
                            <p class="text-sm text-blue-700 mt-1">{{ $userStats['todaySchedule']->date->format('d M Y') }}</p>
                            <a href="{{ route('admin.attendance.check-in-out') }}" class="inline-flex items-center mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Check-in Sekarang
                                <x-ui.icon name="chevron-right" class="ml-1 w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <x-layout.empty-state
                    icon="calendar"
                    title="Tidak ada jadwal hari ini"
                />
            @endif
        </x-ui.card>

        <!-- Recent Notifications -->
        <x-ui.card title="Notifikasi Terbaru">
            @if($userStats['notifications']->count() > 0)
                <div class="space-y-3">
                    @foreach($userStats['notifications'] as $notification)
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($notification->message, 80) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                    <a href="{{ route('notifications') }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium mt-4">
                        Lihat Semua Notifikasi →
                    </a>
                </div>
            @else
                <x-layout.empty-state
                    icon="bell"
                    title="Tidak ada notifikasi baru"
                />
            @endif
        </x-ui.card>
    </x-layout.grid>

    <!-- Upcoming Schedules -->
    @if($userStats['upcomingSchedules']->count() > 0)
        <x-ui.card title="Jadwal Mendatang (7 Hari)">
            <div class="space-y-2">
                @foreach($userStats['upcomingSchedules'] as $schedule)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-12 text-center">
                                <p class="text-xs text-gray-500">{{ $schedule->date->format('D') }}</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->date->format('d') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Sesi {{ $schedule->session }}</p>
                                <p class="text-xs text-gray-500">{{ $schedule->date->format('F Y') }}</p>
                            </div>
                        </div>
                        <x-ui.badge variant="secondary">{{ $schedule->status }}</x-ui.badge>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endif
</div>

@push('scripts')
<script>
    // Update clock every second with Portugal timezone
    function updateClock() {
        const now = new Date();
        
        // Convert to Portugal timezone (Europe/Lisbon)
        const portugalTime = new Date(now.toLocaleString('en-US', { timeZone: 'Europe/Lisbon' }));
        
        const hours = String(portugalTime.getHours()).padStart(2, '0');
        const minutes = String(portugalTime.getMinutes()).padStart(2, '0');
        const seconds = String(portugalTime.getSeconds()).padStart(2, '0');
        
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }
    
    // Update immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endpush
