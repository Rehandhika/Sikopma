<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder lengkap untuk roles dan permissions.
     * Idempotent: bisa dijalankan berkali-kali tanpa duplikat.
     * Support database kosong maupun yang sudah terisi.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            $this->createPermissions();
            $this->createRoles();
            $this->assignPermissionsToRoles();

            DB::commit();

            $this->command->info('✓ Roles and permissions seeded successfully');
            $this->command->info('✓ Total roles: ' . Role::count());
            $this->command->info('✓ Total permissions: ' . Permission::count());

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Failed to seed roles and permissions: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create all permissions based on production data.
     */
    private function createPermissions(): void
    {
        $permissions = [
            ['name' => 'check_in_out', 'guard_name' => 'web'],
            ['name' => 'lihat_absensi_sendiri', 'guard_name' => 'web'],
            ['name' => 'lihat_jadwal_sendiri', 'guard_name' => 'web'],
            ['name' => 'input_ketersediaan', 'guard_name' => 'web'],
            ['name' => 'akses_kasir', 'guard_name' => 'web'],
            ['name' => 'lihat_penalti_sendiri', 'guard_name' => 'web'],
            ['name' => 'ajukan_cuti', 'guard_name' => 'web'],
            ['name' => 'ajukan_tukar_jadwal', 'guard_name' => 'web'],
            ['name' => 'ubah_profil', 'guard_name' => 'web'],
            ['name' => 'kelola_absensi', 'guard_name' => 'web'],
            ['name' => 'kelola_jadwal', 'guard_name' => 'web'],
            ['name' => 'lihat_semua_jadwal', 'guard_name' => 'web'],
            ['name' => 'setujui_cuti', 'guard_name' => 'web'],
            ['name' => 'kelola_cuti', 'guard_name' => 'web'],
            ['name' => 'setujui_tukar_jadwal', 'guard_name' => 'web'],
            ['name' => 'kelola_tukar_jadwal', 'guard_name' => 'web'],
            ['name' => 'kelola_penalti', 'guard_name' => 'web'],
            ['name' => 'lihat_semua_penalti', 'guard_name' => 'web'],
            ['name' => 'kelola_pengguna', 'guard_name' => 'web'],
            ['name' => 'lihat_pengguna', 'guard_name' => 'web'],
            ['name' => 'kelola_peran', 'guard_name' => 'web'],
            ['name' => 'lihat_peran', 'guard_name' => 'web'],
            ['name' => 'kelola_produk', 'guard_name' => 'web'],
            ['name' => 'lihat_produk', 'guard_name' => 'web'],
            ['name' => 'kelola_stok', 'guard_name' => 'web'],
            ['name' => 'lihat_stok', 'guard_name' => 'web'],
            ['name' => 'kelola_pembelian', 'guard_name' => 'web'],
            ['name' => 'lihat_pembelian', 'guard_name' => 'web'],
            ['name' => 'kelola_penjualan', 'guard_name' => 'web'],
            ['name' => 'lihat_semua_penjualan', 'guard_name' => 'web'],
            ['name' => 'lihat_laporan', 'guard_name' => 'web'],
            ['name' => 'ekspor_data', 'guard_name' => 'web'],
            ['name' => 'kelola_poin_shu', 'guard_name' => 'web'],
            ['name' => 'lihat_poin_shu', 'guard_name' => 'web'],
            ['name' => 'kelola_pengaturan', 'guard_name' => 'web'],
            ['name' => 'lihat_log_aktivitas', 'guard_name' => 'web'],
            // Permission baru untuk schedule change
            ['name' => 'ajukan_perubahan_jadwal', 'guard_name' => 'web'],
            ['name' => 'setujui_perubahan_jadwal', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(
                ['name' => $permData['name'], 'guard_name' => $permData['guard_name']],
                $permData
            );
        }

        $this->command->info('✓ Permissions created/updated: ' . count($permissions));
    }

    /**
     * Create all roles based on production data.
     */
    private function createRoles(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['name' => 'anggota', 'guard_name' => 'web'],
            ['name' => 'ketua', 'guard_name' => 'web'],
            ['name' => 'wakil-ketua', 'guard_name' => 'web'],
            ['name' => 'sekretaris', 'guard_name' => 'web'],
            ['name' => 'bendahara', 'guard_name' => 'web'],
            ['name' => 'koordinator-toko', 'guard_name' => 'web'],
            ['name' => 'koordinator-psda', 'guard_name' => 'web'],
            ['name' => 'koordinator-produksi', 'guard_name' => 'web'],
            ['name' => 'koordinator-desain', 'guard_name' => 'web'],
            ['name' => 'koordinator-humsar', 'guard_name' => 'web'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
        }

        $this->command->info('✓ Roles created/updated: ' . count($roles));
    }

    /**
     * Assign permissions to roles based on production role_has_permissions data.
     */
    private function assignPermissionsToRoles(): void
    {
        // Mapping permission_id => permission_name
        $permissionMap = [
            1 => 'check_in_out',
            2 => 'lihat_absensi_sendiri',
            3 => 'lihat_jadwal_sendiri',
            4 => 'input_ketersediaan',
            5 => 'akses_kasir',
            6 => 'lihat_penalti_sendiri',
            7 => 'ajukan_cuti',
            8 => 'ajukan_tukar_jadwal',
            9 => 'ubah_profil',
            10 => 'kelola_absensi',
            11 => 'kelola_jadwal',
            12 => 'lihat_semua_jadwal',
            13 => 'setujui_cuti',
            14 => 'kelola_cuti',
            15 => 'setujui_tukar_jadwal',
            16 => 'kelola_tukar_jadwal',
            17 => 'kelola_penalti',
            18 => 'lihat_semua_penalti',
            19 => 'kelola_pengguna',
            20 => 'lihat_pengguna',
            21 => 'kelola_peran',
            22 => 'lihat_peran',
            23 => 'kelola_produk',
            24 => 'lihat_produk',
            25 => 'kelola_stok',
            26 => 'lihat_stok',
            27 => 'kelola_pembelian',
            28 => 'lihat_pembelian',
            29 => 'kelola_penjualan',
            30 => 'lihat_semua_penjualan',
            31 => 'lihat_laporan',
            32 => 'ekspor_data',
            33 => 'kelola_poin_shu',
            34 => 'lihat_poin_shu',
            35 => 'kelola_pengaturan',
            36 => 'lihat_log_aktivitas',
            37 => 'ajukan_perubahan_jadwal',
            38 => 'setujui_perubahan_jadwal',
        ];

        // Mapping role_id => role_name (production format)
        $roleMap = [
            1 => 'Super Admin',
            4 => 'anggota',
            5 => 'ketua',
            6 => 'wakil-ketua',
            7 => 'sekretaris',
            9 => 'bendahara',
            11 => 'koordinator-toko',
            12 => 'koordinator-psda',
            13 => 'koordinator-produksi',
            14 => 'koordinator-desain',
            15 => 'koordinator-humsar',
        ];

        // Permission assignments from production role_has_permissions.csv
        // Format: permission_id => [role_ids]
        $assignments = [
            // Basic permissions (1-9) for all authenticated users + most roles
            'check_in_out' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'lihat_absensi_sendiri' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'lihat_jadwal_sendiri' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'input_ketersediaan' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'akses_kasir' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'lihat_penalti_sendiri' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'ajukan_cuti' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'ajukan_tukar_jadwal' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'ubah_profil' => [1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15],

            // Management permissions (admin only)
            'kelola_absensi' => [1, 5, 6, 11],
            'kelola_jadwal' => [1, 5, 6, 11],
            'lihat_semua_jadwal' => [1, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'setujui_cuti' => [1, 5, 6, 7, 11, 12, 13, 14, 15],
            'kelola_cuti' => [1, 5, 6, 11],
            'setujui_tukar_jadwal' => [1, 5, 6, 7, 11, 12, 13, 14, 15],
            'kelola_tukar_jadwal' => [1, 5, 6, 11],
            'kelola_penalti' => [1, 5, 6, 11],
            'lihat_semua_penalti' => [1, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'kelola_pengguna' => [1, 5, 6, 11],
            'lihat_pengguna' => [1, 5, 6, 7, 9, 11, 12, 13, 14],
            'kelola_peran' => [1, 5, 6, 11],
            'lihat_peran' => [1, 5, 6, 11],
            'kelola_produk' => [1, 5, 6, 7, 9, 11, 13],
            'lihat_produk' => [1, 5, 6, 7, 9, 11, 12, 13, 14],
            'kelola_stok' => [1, 5, 6, 7, 9, 11, 13],
            'lihat_stok' => [1, 5, 6, 7, 9, 11, 12, 13, 14],
            'kelola_pembelian' => [1, 5, 6, 7, 9, 11, 13],
            'lihat_pembelian' => [1, 5, 6, 7, 9, 11, 12, 13, 14],
            'kelola_penjualan' => [1, 5, 6, 7, 9, 11, 13],
            'lihat_semua_penjualan' => [1, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'lihat_laporan' => [1, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'ekspor_data' => [1, 5, 6, 7, 9, 11, 12, 13, 15],
            'kelola_poin_shu' => [1, 5, 6, 7, 9, 11, 13, 15],
            'lihat_poin_shu' => [1, 5, 6, 7, 9, 11, 12, 13, 14, 15],
            'kelola_pengaturan' => [1, 5, 6],
            'lihat_log_aktivitas' => [1, 5, 6],

            // New schedule change permissions
            'ajukan_perubahan_jadwal' => [1, 4, 5, 6, 11],
            'setujui_perubahan_jadwal' => [1, 5, 6, 11],
        ];

        foreach ($assignments as $permissionName => $roleIds) {
            $permission = Permission::where('name', $permissionName)->first();
            if (!$permission) {
                $this->command->warn("Permission not found: {$permissionName}");
                continue;
            }

            foreach ($roleIds as $roleId) {
                $roleName = $roleMap[$roleId] ?? null;
                if (!$roleName) {
                    continue;
                }

                $role = Role::where('name', $roleName)->first();
                if ($role && !$role->hasPermissionTo($permissionName)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        $this->command->info('✓ Permissions assigned to roles');
    }
}
