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
                </div>
            </div>
        </div>
    </div>

    <!-- User Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">Kehadiran Bulan Ini</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $userStats['monthlyAttendance']['present'] }}/{{ $userStats['monthlyAttendance']['total'] }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">Terlambat</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $userStats['monthlyAttendance']['late'] }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">Penalti Aktif</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $userStats['penalties']['count'] }}</dd>
                        <dd class="text-xs text-gray-500">{{ $userStats['penalties']['points'] }} poin</dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">Notifikasi</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $userStats['notifications']->count() }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
    <!-- Admin Stats -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Hari Ini (Admin)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Schedule Detail -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Jadwal Hari Ini
                </h3>
                @if($userStats['todaySchedule'])
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h4 class="text-sm font-medium text-blue-900">Sesi {{ $userStats['todaySchedule']->session }}</h4>
                                <p class="text-sm text-blue-700 mt-1">{{ $userStats['todaySchedule']->date->format('d M Y') }}</p>
                                <a href="{{ route('attendance.check-in-out') }}" class="inline-flex items-center mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                    Check-in Sekarang
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Tidak ada jadwal hari ini</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Notifikasi Terbaru
                </h3>
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
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Tidak ada notifikasi baru</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Schedules -->
    @if($userStats['upcomingSchedules']->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Jadwal Mendatang (7 Hari)</h3>
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
                            <span class="badge badge-secondary">{{ $schedule->status }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
