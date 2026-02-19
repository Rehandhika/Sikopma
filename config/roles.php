<?php

/**
 * Role and Permission Configuration
 * 
 * CONCEPT:
 * - "lihat_*" = Lihat data sendiri (self-service access)
 * - "kelola_*" = Kelola semua data (management access)
 * - "akses_*" = Akses fitur untuk digunakan
 * - "ajukan_*" = Ajukan permintaan untuk diri sendiri
 * - "setujui_*" = Setujui permintaan orang lain
 * 
 * SELF-SERVICE PERMISSIONS (untuk semua authenticated users):
 * - check_in_out: Check-in/out absensi untuk diri sendiri
 * - lihat_absensi_sendiri: Lihat riwayat absensi sendiri
 * - lihat_jadwal_sendiri: Lihat jadwal sendiri
 * - akses_kasir: Akses POS untuk bertransaksi
 * - lihat_penalti_sendiri: Lihat penalti sendiri
 * - ajukan_cuti: Ajukan cuti untuk diri sendiri
 * - ajukan_tukar_jadwal: Ajukan tukar jadwal
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    */
    'super_admin_role' => env('SUPER_ADMIN_ROLE', 'Super Admin'),

    /*
    |--------------------------------------------------------------------------
    | System Roles
    |--------------------------------------------------------------------------
    */
    'system_roles' => [
        'Super Admin',
        'Admin',
        'Pengurus',
        'Anggota',
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization Roles (Wirus Angkatan 66)
    |--------------------------------------------------------------------------
    */
    'organization_roles' => [
        'Ketua' => [
            'description' => 'Pimpinan tertinggi organisasi',
            'maps_to' => 'Admin',
            'priority' => 1,
        ],
        'Wakil Ketua' => [
            'description' => 'Pendamping Ketua',
            'maps_to' => 'Admin',
            'priority' => 2,
        ],
        'Sekretaris' => [
            'description' => 'Administrasi organisasi',
            'maps_to' => 'Pengurus',
            'priority' => 3,
        ],
        'Bendahara Umum' => [
            'description' => 'Keuangan utama',
            'maps_to' => 'Pengurus',
            'priority' => 4,
        ],
        'Bendahara Kegiatan' => [
            'description' => 'Keuangan kegiatan',
            'maps_to' => 'Pengurus',
            'priority' => 5,
        ],
        'Bendahara Toko' => [
            'description' => 'Keuangan toko',
            'maps_to' => 'Pengurus',
            'priority' => 6,
        ],
        'Koordinator Toko' => [
            'description' => 'Pengelola toko',
            'maps_to' => 'Pengurus',
            'priority' => 7,
        ],
        'Koordinator PSDA' => [
            'description' => 'Pengembangan SDM',
            'maps_to' => 'Pengurus',
            'priority' => 8,
        ],
        'Koordinator Produksi' => [
            'description' => 'Produksi dan Pengadaan',
            'maps_to' => 'Pengurus',
            'priority' => 9,
        ],
        'Koordinator Desain' => [
            'description' => 'Desain dan kreativitas',
            'maps_to' => 'Pengurus',
            'priority' => 10,
        ],
        'Koordinator Humas' => [
            'description' => 'Hubungan masyarakat',
            'maps_to' => 'Pengurus',
            'priority' => 11,
        ],
        'Anggota' => [
            'description' => 'Anggota biasa',
            'maps_to' => 'Anggota',
            'priority' => 99,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        // ============================================
        // SELF-SERVICE PERMISSIONS (Untuk semua user)
        // ============================================
        
        // Absensi - Self Service
        'check_in_out' => [
            'group' => 'Absensi (Self)',
            'description' => 'Check-in dan check-out absensi untuk diri sendiri',
        ],
        'lihat_absensi_sendiri' => [
            'group' => 'Absensi (Self)',
            'description' => 'Lihat riwayat absensi sendiri',
        ],
        
        // Jadwal - Self Service
        'lihat_jadwal_sendiri' => [
            'group' => 'Jadwal (Self)',
            'description' => 'Lihat jadwal sendiri',
        ],
        'input_ketersediaan' => [
            'group' => 'Jadwal (Self)',
            'description' => 'Input ketersediaan waktu untuk penjadwalan',
        ],
        
        // Kasir - Self Service
        'akses_kasir' => [
            'group' => 'Kasir (Self)',
            'description' => 'Akses Point of Sale untuk bertransaksi',
        ],
        
        // Penalti - Self Service
        'lihat_penalti_sendiri' => [
            'group' => 'Penalti (Self)',
            'description' => 'Lihat penalti sendiri',
        ],
        
        // Cuti - Self Service
        'ajukan_cuti' => [
            'group' => 'Cuti (Self)',
            'description' => 'Ajukan permintaan cuti untuk diri sendiri',
        ],
        
        // Tukar Jadwal - Self Service
        'ajukan_tukar_jadwal' => [
            'group' => 'Tukar Jadwal (Self)',
            'description' => 'Ajukan dan setujui permintaan tukar jadwal',
        ],
        
        // Profil - Self Service
        'ubah_profil' => [
            'group' => 'Profil (Self)',
            'description' => 'Ubah profil dan password sendiri',
        ],
        
        // ============================================
        // MANAGEMENT PERMISSIONS (Untuk pengurus/admin)
        // ============================================
        
        // Absensi - Management
        'kelola_absensi' => [
            'group' => 'Absensi (Management)',
            'description' => 'Kelola semua data absensi (lihat, ubah, hapus, export)',
        ],
        
        // Jadwal - Management
        'kelola_jadwal' => [
            'group' => 'Jadwal (Management)',
            'description' => 'Kelola jadwal semua user (buat, ubah, hapus, generate)',
        ],
        'lihat_semua_jadwal' => [
            'group' => 'Jadwal (Management)',
            'description' => 'Lihat jadwal semua user',
        ],
        
        // Cuti - Management
        'setujui_cuti' => [
            'group' => 'Cuti (Management)',
            'description' => 'Setujui/tolak permintaan cuti',
        ],
        'kelola_cuti' => [
            'group' => 'Cuti (Management)',
            'description' => 'Kelola semua permintaan cuti',
        ],
        
        // Tukar Jadwal - Management
        'setujui_tukar_jadwal' => [
            'group' => 'Tukar Jadwal (Management)',
            'description' => 'Setujui/tolak permintaan tukar jadwal',
        ],
        'kelola_tukar_jadwal' => [
            'group' => 'Tukar Jadwal (Management)',
            'description' => 'Kelola semua permintaan tukar jadwal',
        ],
        
        // Penalti - Management
        'kelola_penalti' => [
            'group' => 'Penalti (Management)',
            'description' => 'Kelola penalti (buat, ubah, hapus)',
        ],
        'lihat_semua_penalti' => [
            'group' => 'Penalti (Management)',
            'description' => 'Lihat penalti semua user',
        ],
        
        // Pengguna - Management
        'kelola_pengguna' => [
            'group' => 'Pengguna (Management)',
            'description' => 'Kelola data pengguna (buat, ubah, hapus)',
        ],
        'lihat_pengguna' => [
            'group' => 'Pengguna (Management)',
            'description' => 'Lihat daftar pengguna',
        ],
        
        // Peran - Management
        'kelola_peran' => [
            'group' => 'Peran (Management)',
            'description' => 'Kelola peran dan permission',
        ],
        'lihat_peran' => [
            'group' => 'Peran (Management)',
            'description' => 'Lihat daftar peran',
        ],
        
        // Produk - Management
        'kelola_produk' => [
            'group' => 'Produk (Management)',
            'description' => 'Kelola produk (buat, ubah, hapus)',
        ],
        'lihat_produk' => [
            'group' => 'Produk (Management)',
            'description' => 'Lihat daftar produk',
        ],
        
        // Stok - Management
        'kelola_stok' => [
            'group' => 'Stok (Management)',
            'description' => 'Kelola stok dan inventori',
        ],
        'lihat_stok' => [
            'group' => 'Stok (Management)',
            'description' => 'Lihat data stok',
        ],
        
        // Pembelian - Management
        'kelola_pembelian' => [
            'group' => 'Pembelian (Management)',
            'description' => 'Kelola pembelian (buat, ubah, hapus)',
        ],
        'lihat_pembelian' => [
            'group' => 'Pembelian (Management)',
            'description' => 'Lihat daftar pembelian',
        ],
        
        // Penjualan - Management
        'kelola_penjualan' => [
            'group' => 'Penjualan (Management)',
            'description' => 'Kelola penjualan (void, edit)',
        ],
        'lihat_semua_penjualan' => [
            'group' => 'Penjualan (Management)',
            'description' => 'Lihat semua data penjualan',
        ],
        
        // Laporan - Management
        'lihat_laporan' => [
            'group' => 'Laporan (Management)',
            'description' => 'Lihat laporan-laporan',
        ],
        'ekspor_data' => [
            'group' => 'Laporan (Management)',
            'description' => 'Export data ke Excel/PDF',
        ],
        
        // Poin SHU - Management
        'kelola_poin_shu' => [
            'group' => 'Poin SHU (Management)',
            'description' => 'Kelola Poin SHU',
        ],
        'lihat_poin_shu' => [
            'group' => 'Poin SHU (Management)',
            'description' => 'Lihat data Poin SHU',
        ],
        
        // Sistem - Management
        'kelola_pengaturan' => [
            'group' => 'Sistem (Management)',
            'description' => 'Kelola pengaturan sistem',
        ],
        'lihat_log_aktivitas' => [
            'group' => 'Sistem (Management)',
            'description' => 'Lihat log aktivitas sistem',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Permission Matrix
    |--------------------------------------------------------------------------
    | 
    | SELF-SERVICE permissions diberikan ke SEMUA role (termasuk Anggota)
    | MANAGEMENT permissions diberikan sesuai tingkat akses
    |
    */
    'role_permissions' => [
        'Super Admin' => [
            // All permissions - handled by Gate::before()
        ],
        
        'Admin' => [
            // Self-service (semua user punya)
            'check_in_out', 'lihat_absensi_sendiri', 'lihat_jadwal_sendiri', 'input_ketersediaan',
            'akses_kasir', 'lihat_penalti_sendiri', 'ajukan_cuti', 'ajukan_tukar_jadwal', 'ubah_profil',
            
            // Management - Full access
            'kelola_absensi',
            'kelola_jadwal', 'lihat_semua_jadwal',
            'setujui_cuti', 'kelola_cuti',
            'setujui_tukar_jadwal', 'kelola_tukar_jadwal',
            'kelola_penalti', 'lihat_semua_penalti',
            'kelola_pengguna', 'lihat_pengguna',
            'kelola_peran', 'lihat_peran',
            'kelola_produk', 'lihat_produk',
            'kelola_stok', 'lihat_stok',
            'kelola_pembelian', 'lihat_pembelian',
            'kelola_penjualan', 'lihat_semua_penjualan',
            'lihat_laporan', 'ekspor_data',
            'kelola_poin_shu', 'lihat_poin_shu',
            'kelola_pengaturan', 'lihat_log_aktivitas',
        ],
        
        'Pengurus' => [
            // Self-service (semua user punya)
            'check_in_out', 'lihat_absensi_sendiri', 'lihat_jadwal_sendiri', 'input_ketersediaan',
            'akses_kasir', 'lihat_penalti_sendiri', 'ajukan_cuti', 'ajukan_tukar_jadwal', 'ubah_profil',
            
            // Management - Operational access
            'lihat_semua_jadwal',
            'setujui_cuti',
            'setujui_tukar_jadwal',
            'lihat_semua_penalti',
            'lihat_pengguna',
            'lihat_produk',
            'lihat_stok',
            'lihat_pembelian',
            'lihat_semua_penjualan',
            'lihat_laporan',
            'lihat_poin_shu',
        ],
        
        'Anggota' => [
            // Self-service ONLY - semua user punya hak dasar ini
            'check_in_out', 'lihat_absensi_sendiri', 'lihat_jadwal_sendiri', 'input_ketersediaan',
            'akses_kasir', 'lihat_penalti_sendiri', 'ajukan_cuti', 'ajukan_tukar_jadwal', 'ubah_profil',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Descriptions
    |--------------------------------------------------------------------------
    */
    'role_descriptions' => [
        'Super Admin' => 'Akses penuh ke semua fitur sistem. Dapat mengelola semua aspek aplikasi.',
        'Admin' => 'Administrator dengan akses manajemen penuh. Dapat mengelola pengguna, jadwal, keuangan, dan semua data.',
        'Pengurus' => 'Pengurus organisasi dengan akses operasional. Dapat melihat data dan menyetujui permintaan.',
        'Anggota' => 'Anggota biasa dengan akses self-service. Dapat check-in/out, mengakses kasir, dan mengelola data sendiri.',
    ],
];
