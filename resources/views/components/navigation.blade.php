@php
// Navigation link base classes
$linkBaseClasses = 'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2';
$linkActiveClasses = 'bg-primary-50 text-primary-700';
$linkInactiveClasses = 'text-gray-700 hover:bg-gray-100 hover:text-gray-900';

// Submenu link classes
$submenuLinkBaseClasses = 'block px-3 py-2 text-sm rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2';
$submenuLinkActiveClasses = 'bg-primary-50 text-primary-700 font-medium';
$submenuLinkInactiveClasses = 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';

// Dropdown button classes
$dropdownButtonBaseClasses = 'w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2';
@endphp

{{-- Dashboard --}}
<a href="{{ route('dashboard') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('dashboard') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
    <x-ui.icon name="home" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Dashboard</span>
</a>

{{-- Attendance --}}
<div x-data="{ open: {{ request()->routeIs('attendance.*') ? 'true' : 'false' }} }">
    <button @click="open = !open" 
            type="button"
            class="{{ $dropdownButtonBaseClasses }} {{ request()->routeIs('attendance.*') ? $linkActiveClasses : $linkInactiveClasses }}"
            aria-expanded="{{ request()->routeIs('attendance.*') ? 'true' : 'false' }}"
            aria-controls="attendance-submenu">
        <div class="flex items-center min-w-0">
            <x-ui.icon name="clipboard-list" class="w-5 h-5 mr-3 flex-shrink-0" />
            <span>Absensi</span>
        </div>
        <x-ui.icon name="chevron-down" class="w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200" ::class="{ 'rotate-180': open }" />
    </button>
    <div x-show="open" 
         x-collapse 
         id="attendance-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="{{ route('attendance.check-in-out') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('attendance.check-in-out') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('attendance.check-in-out') ? 'page' : 'false' }}">
            Check In/Out
        </a>
        <a href="{{ route('attendance.index') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('attendance.index') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('attendance.index') ? 'page' : 'false' }}">
            Daftar Absensi
        </a>
        <a href="{{ route('attendance.history') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('attendance.history') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('attendance.history') ? 'page' : 'false' }}">
            Riwayat
        </a>
    </div>
</div>

{{-- Schedule --}}
<div x-data="{ open: {{ request()->routeIs('schedule.*') ? 'true' : 'false' }} }">
    <button @click="open = !open" 
            type="button"
            class="{{ $dropdownButtonBaseClasses }} {{ request()->routeIs('schedule.*') ? $linkActiveClasses : $linkInactiveClasses }}"
            aria-expanded="{{ request()->routeIs('schedule.*') ? 'true' : 'false' }}"
            aria-controls="schedule-submenu">
        <div class="flex items-center min-w-0">
            <x-ui.icon name="calendar" class="w-5 h-5 mr-3 flex-shrink-0" />
            <span>Jadwal</span>
        </div>
        <x-ui.icon name="chevron-down" class="w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200" ::class="{ 'rotate-180': open }" />
    </button>
    <div x-show="open" 
         x-collapse 
         id="schedule-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="{{ route('schedule.index') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('schedule.index') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('schedule.index') ? 'page' : 'false' }}">
            Kalender Jadwal
        </a>
        <a href="{{ route('schedule.my-schedule') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('schedule.my-schedule') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('schedule.my-schedule') ? 'page' : 'false' }}">
            Jadwal Saya
        </a>
        <a href="{{ route('schedule.availability') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('schedule.availability') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('schedule.availability') ? 'page' : 'false' }}">
            Ketersediaan
        </a>
        <a href="{{ route('schedule.create') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('schedule.create') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('schedule.create') ? 'page' : 'false' }}">
            Tambah Jadwal
        </a>
    </div>
</div>

{{-- Cashier / POS --}}
<a href="{{ route('cashier.pos') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('cashier.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('cashier.*') ? 'page' : 'false' }}">
    <x-ui.icon name="currency-dollar" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Kasir / POS</span>
</a>

{{-- Products --}}
<a href="{{ route('products.index') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('products.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('products.*') ? 'page' : 'false' }}">
    <x-ui.icon name="shopping-cart" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Produk</span>
</a>

{{-- Stock --}}
<a href="{{ route('stock.index') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('stock.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('stock.*') ? 'page' : 'false' }}">
    <x-ui.icon name="inbox" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Stok</span>
</a>

{{-- Leave Requests --}}
<div x-data="{ open: {{ request()->routeIs('leave.*') ? 'true' : 'false' }} }">
    <button @click="open = !open" 
            type="button"
            class="{{ $dropdownButtonBaseClasses }} {{ request()->routeIs('leave.*') ? $linkActiveClasses : $linkInactiveClasses }}"
            aria-expanded="{{ request()->routeIs('leave.*') ? 'true' : 'false' }}"
            aria-controls="leave-submenu">
        <div class="flex items-center min-w-0">
            <x-ui.icon name="document-text" class="w-5 h-5 mr-3 flex-shrink-0" />
            <span>Izin/Cuti</span>
        </div>
        <x-ui.icon name="chevron-down" class="w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200" ::class="{ 'rotate-180': open }" />
    </button>
    <div x-show="open" 
         x-collapse 
         id="leave-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="{{ route('leave.my-requests') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('leave.my-requests') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('leave.my-requests') ? 'page' : 'false' }}">
            Pengajuan Saya
        </a>
        <a href="{{ route('leave.create') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('leave.create') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('leave.create') ? 'page' : 'false' }}">
            Ajukan Izin
        </a>
        <a href="{{ route('leave.approvals') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('leave.approvals') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('leave.approvals') ? 'page' : 'false' }}">
            Persetujuan
        </a>
    </div>
</div>

{{-- Swap Requests --}}
<a href="{{ route('swap.index') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('swap.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('swap.*') ? 'page' : 'false' }}">
    <x-ui.icon name="arrow-right" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Tukar Jadwal</span>
</a>

{{-- Penalties --}}
<a href="{{ route('penalties.index') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('penalties.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('penalties.*') ? 'page' : 'false' }}">
    <x-ui.icon name="exclamation-triangle" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Sanksi</span>
</a>

{{-- Reports --}}
<div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
    <button @click="open = !open" 
            type="button"
            class="{{ $dropdownButtonBaseClasses }} {{ request()->routeIs('reports.*') ? $linkActiveClasses : $linkInactiveClasses }}"
            aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}"
            aria-controls="reports-submenu">
        <div class="flex items-center min-w-0">
            <x-ui.icon name="document" class="w-5 h-5 mr-3 flex-shrink-0" />
            <span>Laporan</span>
        </div>
        <x-ui.icon name="chevron-down" class="w-4 h-4 ml-2 flex-shrink-0 transition-transform duration-200" ::class="{ 'rotate-180': open }" />
    </button>
    <div x-show="open" 
         x-collapse 
         id="reports-submenu"
         class="ml-8 mt-1 space-y-1"
         role="menu">
        <a href="{{ route('reports.attendance') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('reports.attendance') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('reports.attendance') ? 'page' : 'false' }}">
            Laporan Absensi
        </a>
        <a href="{{ route('reports.sales') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('reports.sales') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('reports.sales') ? 'page' : 'false' }}">
            Laporan Penjualan
        </a>
        <a href="{{ route('reports.penalties') }}" 
           class="{{ $submenuLinkBaseClasses }} {{ request()->routeIs('reports.penalties') ? $submenuLinkActiveClasses : $submenuLinkInactiveClasses }}"
           role="menuitem"
           aria-current="{{ request()->routeIs('reports.penalties') ? 'page' : 'false' }}">
            Laporan Sanksi
        </a>
    </div>
</div>

{{-- Analytics --}}
<a href="{{ route('analytics.dashboard') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('analytics.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('analytics.*') ? 'page' : 'false' }}">
    <x-ui.icon name="chart-bar" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Analytics</span>
</a>

{{-- Divider --}}
<div class="border-t border-gray-200 my-2" role="separator"></div>

{{-- Users Management --}}
<a href="{{ route('users.index') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('users.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('users.*') ? 'page' : 'false' }}">
    <x-ui.icon name="user-group" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Manajemen User</span>
</a>

{{-- Roles & Permissions --}}
<a href="{{ route('roles.index') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('roles.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('roles.*') ? 'page' : 'false' }}">
    <x-ui.icon name="check-circle" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Role & Permission</span>
</a>

{{-- Settings --}}
<a href="{{ route('settings.general') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('settings.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('settings.*') ? 'page' : 'false' }}">
    <x-ui.icon name="cog" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Pengaturan</span>
</a>

{{-- Profile --}}
<a href="{{ route('profile.edit') }}" 
   class="{{ $linkBaseClasses }} {{ request()->routeIs('profile.*') ? $linkActiveClasses : $linkInactiveClasses }}"
   aria-current="{{ request()->routeIs('profile.*') ? 'page' : 'false' }}">
    <x-ui.icon name="user" class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>Profil Saya</span>
</a>
