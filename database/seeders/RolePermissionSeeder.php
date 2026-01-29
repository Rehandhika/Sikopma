<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view.users',
            'create.users',
            'edit.users',
            'delete.users',

            // Role management
            'view.roles',
            'create.roles',
            'edit.roles',
            'delete.roles',

            // Attendance
            'view.attendance.all',
            'view.attendance.own',
            'checkin.attendance',
            'manage.attendance',

            // Schedule management
            'view.schedule.all',
            'view.schedule.own',
            'create.schedule',
            'edit.schedule',
            'delete.schedule',
            'generate.schedule',
            'publish.schedule',
            'input.availability',

            // Swap requests
            'view.swap.all',
            'view.swap.own',
            'create.swap.request',
            'approve.swap.target',
            'approve.swap.admin',

            // Leave requests
            'view.leave.all',
            'view.leave.own',
            'create.leave.request',
            'approve.leave.request',

            // Penalties
            'view.penalty.all',
            'view.penalty.own',
            'create.penalty',
            'edit.penalty',
            'delete.penalty',
            'appeal.penalty',
            'manage.penalty',

            // Sales/Cashier
            'view.sales.all',
            'view.sales.own',
            'create.sales',
            'edit.sales',
            'delete.sales',

            // Products
            'view.products',
            'create.products',
            'edit.products',
            'delete.products',

            // Purchases/Stock
            'view.purchases',
            'create.purchases',
            'edit.purchases',
            'delete.purchases',
            'manage.stock',

            // Reports
            'view.reports',
            'view.reports.finance',
            'view.reports.sales',
            'view.reports.attendance',
            'export.reports',

            // Finance
            'view.finance',
            'manage.finance',
            'approve.finance',

            // Toko (Store)
            'manage.toko',
            'view.toko.reports',

            // PSDA (Pengembangan Sumber Daya Anggota)
            'manage.psda',
            'view.psda.reports',

            // Humsar (Hubungan Masyarakat)
            'manage.humsar',
            'view.humsar.reports',

            // Produksi & Pengadaan
            'manage.produksi',
            'view.produksi.reports',

            // IT
            'manage.it',
            'manage.system',
            'view.system.logs',

            // Desain
            'manage.desain',
            'view.desain.assets',

            // System settings
            'manage.settings',

            // Audit logs
            'view.audit.logs',

            // Notifications
            'send.notifications',
            'manage.notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // =====================================================
        // ROLES DEFINITION - Wirus Angkatan 66
        // =====================================================

        // 1. Super Admin - Full access (IT Coordinator)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // 2. Ketua - Pimpinan tertinggi organisasi
        $ketuaRole = Role::firstOrCreate(['name' => 'Ketua']);
        $ketuaRole->givePermissionTo([
            // User management
            'view.users', 'create.users', 'edit.users', 'delete.users',
            'view.roles',
            // Attendance
            'view.attendance.all', 'manage.attendance',
            // Schedule
            'view.schedule.all', 'create.schedule', 'edit.schedule', 'delete.schedule',
            'generate.schedule', 'publish.schedule',
            // Swap & Leave
            'view.swap.all', 'approve.swap.admin',
            'view.leave.all', 'approve.leave.request',
            // Penalty
            'view.penalty.all', 'create.penalty', 'edit.penalty', 'manage.penalty',
            // Sales & Products
            'view.sales.all', 'view.products', 'create.products', 'edit.products',
            // Purchases
            'view.purchases',
            // Reports
            'view.reports', 'view.reports.finance', 'view.reports.sales',
            'view.reports.attendance', 'export.reports',
            // Finance
            'view.finance', 'approve.finance',
            // All divisions overview
            'view.toko.reports', 'view.psda.reports', 'view.humsar.reports',
            'view.produksi.reports',
            // Settings & Audit
            'manage.settings', 'view.audit.logs',
            // Notifications
            'send.notifications', 'manage.notifications',
        ]);

        // 3. Wakil Ketua - Pendamping Ketua
        $wakilKetuaRole = Role::firstOrCreate(['name' => 'Wakil Ketua']);
        $wakilKetuaRole->givePermissionTo([
            // User management (limited)
            'view.users', 'edit.users',
            // Attendance
            'view.attendance.all', 'manage.attendance',
            // Schedule
            'view.schedule.all', 'create.schedule', 'edit.schedule', 'generate.schedule',
            // Swap & Leave
            'view.swap.all', 'approve.swap.admin',
            'view.leave.all', 'approve.leave.request',
            // Penalty
            'view.penalty.all', 'create.penalty',
            // Sales & Products
            'view.sales.all', 'view.products', 'edit.products',
            // Purchases
            'view.purchases',
            // Reports
            'view.reports', 'view.reports.finance', 'view.reports.sales',
            'view.reports.attendance', 'export.reports',
            // Finance (view only)
            'view.finance',
            // All divisions overview
            'view.toko.reports', 'view.psda.reports', 'view.humsar.reports',
            'view.produksi.reports',
            // Notifications
            'send.notifications',
        ]);

        // 4. Sekretaris - Administrasi organisasi
        $sekretarisRole = Role::firstOrCreate(['name' => 'Sekretaris']);
        $sekretarisRole->givePermissionTo([
            // User management
            'view.users', 'create.users', 'edit.users',
            // Attendance
            'view.attendance.all', 'manage.attendance',
            // Schedule
            'view.schedule.all', 'create.schedule', 'edit.schedule',
            // Swap & Leave
            'view.swap.all',
            'view.leave.all',
            // Penalty
            'view.penalty.all',
            // Sales & Products
            'view.sales.all', 'view.products',
            // Reports
            'view.reports', 'view.reports.attendance', 'export.reports',
            // Notifications
            'send.notifications', 'manage.notifications',
            // Audit
            'view.audit.logs',
        ]);

        // 5. Bendahara Umum - Keuangan utama
        $bendaharaUmumRole = Role::firstOrCreate(['name' => 'Bendahara Umum']);
        $bendaharaUmumRole->givePermissionTo([
            // User (view only)
            'view.users',
            // Attendance
            'view.attendance.all',
            // Schedule
            'view.schedule.all',
            // Sales
            'view.sales.all', 'edit.sales',
            // Products
            'view.products',
            // Purchases
            'view.purchases', 'create.purchases', 'edit.purchases',
            // Reports
            'view.reports', 'view.reports.finance', 'view.reports.sales', 'export.reports',
            // Finance - Full access
            'view.finance', 'manage.finance', 'approve.finance',
            // Toko reports
            'view.toko.reports',
        ]);

        // 6. Bendahara Kegiatan - Keuangan kegiatan
        $bendaharaKegiatanRole = Role::firstOrCreate(['name' => 'Bendahara Kegiatan']);
        $bendaharaKegiatanRole->givePermissionTo([
            // User (view only)
            'view.users',
            // Attendance
            'view.attendance.all',
            // Schedule
            'view.schedule.all',
            // Sales
            'view.sales.all',
            // Products
            'view.products',
            // Purchases
            'view.purchases', 'create.purchases',
            // Reports
            'view.reports', 'view.reports.finance', 'export.reports',
            // Finance
            'view.finance', 'manage.finance',
        ]);

        // 7. Bendahara Toko - Keuangan toko
        $bendaharaTokoRole = Role::firstOrCreate(['name' => 'Bendahara Toko']);
        $bendaharaTokoRole->givePermissionTo([
            // User (view only)
            'view.users',
            // Attendance
            'view.attendance.own', 'checkin.attendance',
            // Schedule
            'view.schedule.all', 'input.availability',
            // Sales
            'view.sales.all', 'create.sales', 'edit.sales',
            // Products
            'view.products', 'edit.products',
            // Purchases
            'view.purchases', 'create.purchases',
            // Reports
            'view.reports', 'view.reports.sales', 'export.reports',
            // Finance (toko related)
            'view.finance',
            // Toko
            'view.toko.reports',
        ]);

        // 8. Koordinator Toko - Pengelola toko
        $koordinatorTokoRole = Role::firstOrCreate(['name' => 'Koordinator Toko']);
        $koordinatorTokoRole->givePermissionTo([
            // User (view only)
            'view.users',
            // Attendance
            'view.attendance.all', 'manage.attendance',
            // Schedule - Full for toko
            'view.schedule.all', 'create.schedule', 'edit.schedule',
            'generate.schedule', 'publish.schedule', 'input.availability',
            // Swap & Leave
            'view.swap.all', 'approve.swap.admin',
            'view.leave.all', 'approve.leave.request',
            // Penalty
            'view.penalty.all', 'create.penalty',
            // Sales - Full access
            'view.sales.all', 'create.sales', 'edit.sales', 'delete.sales',
            // Products - Full access
            'view.products', 'create.products', 'edit.products', 'delete.products',
            // Purchases
            'view.purchases', 'create.purchases', 'edit.purchases',
            // Stock
            'manage.stock',
            // Reports
            'view.reports', 'view.reports.sales', 'export.reports',
            // Toko - Full access
            'manage.toko', 'view.toko.reports',
        ]);

        // 9. Koordinator PSDA - Pengembangan SDM Anggota
        $koordinatorPSDARole = Role::firstOrCreate(['name' => 'Koordinator PSDA']);
        $koordinatorPSDARole->givePermissionTo([
            // User management
            'view.users', 'edit.users',
            // Attendance
            'view.attendance.all',
            // Schedule
            'view.schedule.all', 'input.availability',
            // Swap & Leave
            'view.swap.all',
            'view.leave.all',
            // Penalty
            'view.penalty.all', 'create.penalty', 'edit.penalty',
            // Sales & Products (view)
            'view.sales.own', 'view.products',
            // Reports
            'view.reports', 'view.reports.attendance',
            // PSDA - Full access
            'manage.psda', 'view.psda.reports',
            // Notifications
            'send.notifications',
        ]);

        // 10. Koordinator Humsar - Hubungan Masyarakat
        $koordinatorHumsarRole = Role::firstOrCreate(['name' => 'Koordinator Humsar']);
        $koordinatorHumsarRole->givePermissionTo([
            // User (view only)
            'view.users',
            // Attendance
            'view.attendance.own', 'checkin.attendance',
            // Schedule
            'view.schedule.all', 'input.availability',
            // Swap & Leave
            'view.swap.own', 'create.swap.request',
            'view.leave.own', 'create.leave.request',
            // Sales & Products (view)
            'view.sales.own', 'view.products',
            // Reports
            'view.reports',
            // Humsar - Full access
            'manage.humsar', 'view.humsar.reports',
            // Notifications
            'send.notifications',
        ]);

        // 11. Koordinator Produksi dan Pengadaan
        $koordinatorProduksiRole = Role::firstOrCreate(['name' => 'Koordinator Produksi']);
        $koordinatorProduksiRole->givePermissionTo([
            // User (view only)
            'view.users',
            // Attendance
            'view.attendance.own', 'checkin.attendance',
            // Schedule
            'view.schedule.all', 'input.availability',
            // Swap & Leave
            'view.swap.own', 'create.swap.request',
            'view.leave.own', 'create.leave.request',
            // Products - Full access
            'view.products', 'create.products', 'edit.products',
            // Purchases - Full access
            'view.purchases', 'create.purchases', 'edit.purchases', 'delete.purchases',
            // Stock
            'manage.stock',
            // Sales (view)
            'view.sales.all',
            // Reports
            'view.reports', 'view.produksi.reports',
            // Produksi - Full access
            'manage.produksi',
        ]);

        // 12. Koordinator IT
        $koordinatorITRole = Role::firstOrCreate(['name' => 'Koordinator IT']);
        $koordinatorITRole->givePermissionTo([
            // User management
            'view.users', 'create.users', 'edit.users',
            'view.roles',
            // Attendance
            'view.attendance.all',
            // Schedule
            'view.schedule.all',
            // All reports
            'view.reports', 'view.reports.finance', 'view.reports.sales',
            'view.reports.attendance', 'export.reports',
            // IT & System - Full access
            'manage.it', 'manage.system', 'view.system.logs',
            // Settings
            'manage.settings',
            // Audit
            'view.audit.logs',
            // Products & Sales (view)
            'view.products', 'view.sales.all',
        ]);

        // 13. Koordinator Desain
        $koordinatorDesainRole = Role::firstOrCreate(['name' => 'Koordinator Desain']);
        $koordinatorDesainRole->givePermissionTo([
            // User (view only)
            'view.users',
            // Attendance
            'view.attendance.own', 'checkin.attendance',
            // Schedule
            'view.schedule.all', 'input.availability',
            // Swap & Leave
            'view.swap.own', 'create.swap.request',
            'view.leave.own', 'create.leave.request',
            // Products (view for design reference)
            'view.products',
            // Desain - Full access
            'manage.desain', 'view.desain.assets',
        ]);

        // 14. Anggota - Member biasa
        $anggotaRole = Role::firstOrCreate(['name' => 'Anggota']);
        $anggotaRole->givePermissionTo([
            'view.attendance.own',
            'checkin.attendance',
            'view.schedule.own',
            'input.availability',
            'view.swap.own',
            'create.swap.request',
            'approve.swap.target',
            'view.leave.own',
            'create.leave.request',
            'view.penalty.own',
            'appeal.penalty',
            'view.sales.own',
            'create.sales',
            'view.products',
        ]);

        // Legacy roles for backward compatibility
        Role::firstOrCreate(['name' => 'BPH']);
    }
}
