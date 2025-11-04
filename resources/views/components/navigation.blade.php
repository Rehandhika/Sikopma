@php
    $user = auth()->user();
    $hasRole = function($roles) use ($user) {
        if (is_string($roles)) {
            $roles = explode('|', $roles);
        }
        return $user->hasAnyRole($roles);
    };
@endphp

<!-- Kopma -->
<a href="{{ route('kopma.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('kopma.index') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
    <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('kopma.index') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
    </svg>
    Kopma
</a>

<!-- Dashboard -->
<a href="{{ route('dashboard') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
    <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
    </svg>
    Dashboard
</a>

<!-- Attendance -->
<a href="{{ route('attendance.check-in-out') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendance.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
    <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('attendance.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    Absensi
</a>

<!-- Schedule -->
<div class="space-y-1">
    <button type="button" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md w-full text-left {{ request()->routeIs('schedule.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" onclick="toggleScheduleMenu()">
        <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('schedule.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        Jadwal
        <svg class="ml-auto h-5 w-5 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="schedule-menu" class="hidden space-y-1 ml-6">
        <a href="{{ route('schedule.my-schedule') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('schedule.my-schedule') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Jadwal Saya
        </a>
        <a href="{{ route('schedule.calendar') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('schedule.calendar') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Kalender Jadwal
        </a>
        <a href="{{ route('schedule.availability') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('schedule.availability') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Input Ketersediaan
        </a>
        @if($hasRole(['Super Admin', 'Ketua', 'Wakil Ketua']))
            <a href="{{ route('schedule.generator') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('schedule.generator') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                Generate Jadwal
            </a>
        @endif
    </div>
</div>

<!-- Shift Swaps -->
<div class="space-y-1">
    <button type="button" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md w-full text-left {{ request()->routeIs('swap.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" onclick="toggleSwapMenu()">
        <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('swap.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
        </svg>
        Tukar Shift
        <svg class="ml-auto h-5 w-5 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="swap-menu" class="hidden space-y-1 ml-6">
        <a href="{{ route('swap.create') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('swap.create') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Buat Permintaan
        </a>
        <a href="{{ route('swap.my-requests') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('swap.my-requests') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Permintaan Saya
        </a>
        @if($hasRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']))
            <a href="{{ route('swap.pending') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('swap.pending') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                Menunggu Approval
            </a>
        @endif
    </div>
</div>

<!-- Leave Management -->
<div class="space-y-1">
    <button type="button" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md w-full text-left {{ request()->routeIs('leave.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" onclick="toggleLeaveMenu()">
        <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('leave.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        Cuti
        <svg class="ml-auto h-5 w-5 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="leave-menu" class="hidden space-y-1 ml-6">
        <a href="{{ route('leave.create') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('leave.create') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Ajukan Cuti
        </a>
        <a href="{{ route('leave.my-requests') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('leave.my-requests') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Permintaan Saya
        </a>
        @if($hasRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']))
            <a href="{{ route('leave.pending') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('leave.pending') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                Menunggu Approval
            </a>
        @endif
    </div>
</div>

<!-- Penalties -->
<div class="space-y-1">
    <button type="button" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md w-full text-left {{ request()->routeIs('penalty.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" onclick="togglePenaltyMenu()">
        <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('penalty.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        Penalti
        <svg class="ml-auto h-5 w-5 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="penalty-menu" class="hidden space-y-1 ml-6">
        <a href="{{ route('penalty.my-penalties') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('penalty.my-penalties') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Penalti Saya
        </a>
        @if($hasRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']))
            <a href="{{ route('penalty.manage') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('penalty.manage') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                Kelola Penalti
            </a>
        @endif
    </div>
</div>

<!-- Notifications -->
<a href="{{ route('notifications') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('notifications') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
    <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('notifications') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 12.683A17.925 17.925 0 012 21h13.78a3 3 0 002.447-1.341l.828-1.66A5.986 5.986 0 0018.786 12A17.978 17.978 0 014.868 12.683z"></path>
    </svg>
    Notifikasi
</a>

@if($hasRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']))
<!-- Cashier/POS -->
<div class="space-y-1">
    <button type="button" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md w-full text-left {{ request()->routeIs('cashier.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" onclick="toggleCashierMenu()">
        <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('cashier.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        Kasir
        <svg class="ml-auto h-5 w-5 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="cashier-menu" class="hidden space-y-1 ml-6">
        <a href="{{ route('cashier.pos') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('cashier.pos') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            POS
        </a>
        <a href="{{ route('cashier.sales') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('cashier.sales') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Daftar Penjualan
        </a>
        <a href="{{ route('cashier.transactions') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('cashier.transactions') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Transaksi Baru
        </a>
    </div>
</div>
@endif

@if($hasRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']))
<!-- Reports -->
<div class="space-y-1">
    <button type="button" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md w-full text-left {{ request()->routeIs('reports.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" onclick="toggleReportsMenu()">
        <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('reports.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        Laporan
        <svg class="ml-auto h-5 w-5 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="reports-menu" class="hidden space-y-1 ml-6">
        <a href="{{ route('reports.attendance') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('reports.attendance') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Laporan Absensi
        </a>
        <a href="{{ route('reports.sales') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('reports.sales') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Laporan Penjualan
        </a>
        <a href="{{ route('reports.penalty') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('reports.penalty') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Laporan Penalti
        </a>
    </div>
</div>
@endif

@if($hasRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']))
<!-- Inventory Management -->
<div class="space-y-1">
    <button type="button" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md w-full text-left {{ request()->routeIs('products.*') || request()->routeIs('stock.*') || request()->routeIs('purchase.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" onclick="toggleInventoryMenu()">
        <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('products.*') || request()->routeIs('stock.*') || request()->routeIs('purchase.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        Inventori
        <svg class="ml-auto h-5 w-5 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="inventory-menu" class="hidden space-y-1 ml-6">
        <a href="{{ route('products.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('products.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Produk
        </a>
        <a href="{{ route('stock.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('stock.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Stok
        </a>
        <a href="{{ route('purchase.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('purchase.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Pembelian
        </a>
    </div>
</div>
@endif

@if($hasRole(['Super Admin', 'Ketua', 'Wakil Ketua']))
<!-- User & Role Management -->
<div class="space-y-1">
    <button type="button" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md w-full text-left {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" onclick="toggleUserMenu()">
        <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
        </svg>
        Manajemen User
        <svg class="ml-auto h-5 w-5 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="user-menu" class="hidden space-y-1 ml-6">
        <a href="{{ route('users.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            Daftar Anggota
        </a>
        @if($hasRole(['Super Admin', 'Ketua']))
            <a href="{{ route('roles.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('roles.index') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                Kelola Role
            </a>
        @endif
    </div>
</div>
@endif

@if($hasRole(['Super Admin', 'Ketua']))
<!-- Settings -->
<a href="{{ route('settings.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.*') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
    <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('settings.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
    </svg>
    Pengaturan
</a>
@endif

<!-- Profile -->
<a href="{{ route('profile.edit') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('profile.edit') ? 'bg-indigo-50 border-r-2 border-indigo-600 text-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
    <svg class="mr-3 flex-shrink-0 h-6 w-6 {{ request()->routeIs('profile.edit') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
    </svg>
    Profil
</a>

<script>
function toggleScheduleMenu() {
    const menu = document.getElementById('schedule-menu');
    menu.classList.toggle('hidden');
}

function toggleSwapMenu() {
    const menu = document.getElementById('swap-menu');
    menu.classList.toggle('hidden');
}

function toggleLeaveMenu() {
    const menu = document.getElementById('leave-menu');
    menu.classList.toggle('hidden');
}

function togglePenaltyMenu() {
    const menu = document.getElementById('penalty-menu');
    menu.classList.toggle('hidden');
}

function toggleCashierMenu() {
    const menu = document.getElementById('cashier-menu');
    menu.classList.toggle('hidden');
}

function toggleReportsMenu() {
    const menu = document.getElementById('reports-menu');
    menu.classList.toggle('hidden');
}

function toggleInventoryMenu() {
    const menu = document.getElementById('inventory-menu');
    menu.classList.toggle('hidden');
}

function toggleUserMenu() {
    const menu = document.getElementById('user-menu');
    menu.classList.toggle('hidden');
}
</script>
