<?php

/**
 * Menu Configuration for Sidebar Navigation
 *
 * Each menu item supports:
 * - key: Unique identifier for the menu item
 * - label: Display label in Indonesian
 * - icon: Heroicon name (used with x-ui.icon component)
 * - route: Laravel route name (null for parent-only menus)
 * - permissions: Array of required permissions (empty = accessible to all authenticated users)
 * - permission_logic: 'any' (OR) or 'all' (AND) - default is 'any'
 * - children: Nested menu items for dropdown menus
 * - type: 'divider' for separator items
 * - roles: Additional role-based restriction (optional)
 */

return [
    'items' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'home',
            'route' => 'admin.dashboard',
            'permissions' => [],
        ],
        [
            'key' => 'attendance',
            'label' => 'Absensi',
            'icon' => 'clipboard-list',
            'route' => null,
            'permissions' => ['view.attendance.all', 'view.attendance.own', 'checkin.attendance'],
            'permission_logic' => 'any',
            'children' => [
                ['key' => 'attendance.checkin', 'label' => 'Check In/Out', 'route' => 'admin.attendance.check-in-out', 'permissions' => ['checkin.attendance']],
                ['key' => 'attendance.list', 'label' => 'Daftar Absensi', 'route' => 'admin.attendance.index', 'permissions' => ['view.attendance.all']],
                ['key' => 'attendance.history', 'label' => 'Riwayat', 'route' => 'admin.attendance.history', 'permissions' => ['view.attendance.all', 'view.attendance.own'], 'permission_logic' => 'any'],
            ],
        ],
        [
            'key' => 'schedule',
            'label' => 'Jadwal',
            'icon' => 'calendar',
            'route' => null,
            'permissions' => ['view.schedule.all', 'view.schedule.own', 'create.schedule', 'input.availability'],
            'permission_logic' => 'any',
            'children' => [
                ['key' => 'schedule.manage', 'label' => 'Kelola Jadwal', 'route' => 'admin.schedule.index', 'active_routes' => ['admin.schedule.index', 'admin.schedule.create', 'admin.schedule.edit', 'admin.schedule.history'], 'permissions' => ['view.schedule.all', 'create.schedule', 'edit.schedule'], 'permission_logic' => 'any'],
                ['key' => 'schedule.my', 'label' => 'Jadwal Saya', 'route' => 'admin.schedule.my-schedule', 'permissions' => ['view.schedule.own', 'view.schedule.all'], 'permission_logic' => 'any'],
                ['key' => 'schedule.availability', 'label' => 'Ketersediaan', 'route' => 'admin.schedule.availability', 'permissions' => ['input.availability']],
                ['key' => 'schedule.leave', 'label' => 'Izin/Cuti', 'route' => 'admin.leave.index', 'active_routes' => ['admin.leave.*'], 'permissions' => ['view.leave.all', 'view.leave.own', 'create.leave.request'], 'permission_logic' => 'any'],
                ['key' => 'schedule.swap', 'label' => 'Perubahan Jadwal', 'route' => 'admin.swap.index', 'active_routes' => ['admin.swap.*'], 'permissions' => ['view.swap.all', 'view.swap.own', 'create.swap.request'], 'permission_logic' => 'any'],
            ],
        ],
        [
            'key' => 'cashier',
            'label' => 'Kasir / POS',
            'icon' => 'currency-dollar',
            'route' => null,
            'permissions' => ['view.sales.all', 'view.sales.own', 'create.sales'],
            'permission_logic' => 'any',
            'children' => [
                ['key' => 'cashier.pos', 'label' => 'POS Kasir', 'route' => 'admin.cashier.pos', 'permissions' => ['create.sales', 'view.sales.own'], 'permission_logic' => 'any'],
                ['key' => 'cashier.entry', 'label' => 'Entry Transaksi', 'route' => 'admin.cashier.pos-entry', 'permissions' => ['edit.sales', 'view.sales.all'], 'permission_logic' => 'all', 'roles' => ['Super Admin', 'Ketua', 'Wakil Ketua']],
            ],
        ],
        [
            'key' => 'inventory',
            'label' => 'Inventaris',
            'icon' => 'cube',
            'route' => null,
            'permissions' => ['view.products', 'manage.stock'],
            'permission_logic' => 'any',
            'children' => [
                ['key' => 'inventory.products', 'label' => 'Daftar Produk', 'route' => 'admin.products.index', 'active_routes' => ['admin.products.*'], 'permissions' => ['view.products']],
                ['key' => 'inventory.stock', 'label' => 'Manajemen Stok', 'route' => 'admin.stock.index', 'active_routes' => ['admin.stock.*'], 'permissions' => ['manage.stock', 'view.purchases'], 'permission_logic' => 'any'],
            ],
        ],
        [
            'key' => 'penalties',
            'label' => 'Penalti',
            'icon' => 'exclamation-triangle',
            'route' => 'admin.penalties.index',
            'active_routes' => ['admin.penalties.*'],
            'permissions' => ['view.penalty.all', 'view.penalty.own'],
            'permission_logic' => 'any',
        ],
        [
            'key' => 'reports',
            'label' => 'Laporan',
            'icon' => 'document',
            'route' => null,
            'permissions' => ['view.reports', 'view.reports.attendance', 'view.reports.sales', 'view.reports.finance'],
            'permission_logic' => 'any',
            'children' => [
                ['key' => 'reports.attendance', 'label' => 'Laporan Absensi', 'route' => 'admin.reports.attendance', 'permissions' => ['view.reports.attendance', 'view.reports'], 'permission_logic' => 'any'],
                ['key' => 'reports.sales', 'label' => 'Laporan Penjualan', 'route' => 'admin.reports.sales', 'permissions' => ['view.reports.sales', 'view.reports'], 'permission_logic' => 'any'],
                ['key' => 'reports.penalties', 'label' => 'Laporan Penalti', 'route' => 'admin.reports.penalties', 'permissions' => ['view.reports', 'view.penalty.all'], 'permission_logic' => 'any'],
            ],
        ],
        ['key' => 'divider.admin', 'type' => 'divider'],
        [
            'key' => 'activity-log',
            'label' => 'Log Aktivitas',
            'icon' => 'clipboard-document-list',
            'route' => 'admin.activity-log',
            'permissions' => [],
            'roles' => ['Super Admin', 'Ketua'],
        ],
        [
            'key' => 'users',
            'label' => 'Manajemen User',
            'icon' => 'user-group',
            'route' => 'admin.users.index',
            'active_routes' => ['admin.users.*'],
            'permissions' => ['view.users'],
        ],
        [
            'key' => 'roles',
            'label' => 'Role & Permission',
            'icon' => 'check-circle',
            'route' => 'admin.roles.index',
            'active_routes' => ['admin.roles.*'],
            'permissions' => ['view.roles'],
        ],
        [
            'key' => 'settings',
            'label' => 'Pengaturan',
            'icon' => 'cog',
            'route' => null,
            'permissions' => ['manage.settings', 'manage.system'],
            'permission_logic' => 'any',
            'children' => [
                ['key' => 'settings.system', 'label' => 'Pengaturan Sistem', 'route' => 'admin.settings.system', 'permissions' => ['manage.settings', 'manage.system'], 'permission_logic' => 'any'],
                ['key' => 'settings.store', 'label' => 'Pengaturan Toko', 'route' => 'admin.settings.store', 'permissions' => ['manage.settings', 'manage.toko'], 'permission_logic' => 'any', 'roles' => ['Super Admin', 'Ketua', 'Wakil Ketua']],
                ['key' => 'settings.banners', 'label' => 'Banner & Berita', 'route' => 'admin.settings.banners', 'permissions' => ['manage.settings'], 'roles' => ['Super Admin', 'Ketua']],
                ['key' => 'settings.payment', 'label' => 'Pengaturan Pembayaran', 'route' => 'admin.settings.payment', 'permissions' => ['manage.settings'], 'roles' => ['Super Admin', 'Ketua', 'Wakil Ketua']],
            ],
        ],
        [
            'key' => 'profile',
            'label' => 'Profil Saya',
            'icon' => 'user',
            'route' => 'admin.profile.edit',
            'active_routes' => ['admin.profile.*'],
            'permissions' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('MENU_CACHE_ENABLED', true),
        'ttl' => env('MENU_CACHE_TTL', 3600),
        'prefix' => 'menu_access',
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    */
    'super_admin_role' => 'Super Admin',
];
