<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Execution order is critical - dependencies must be seeded first:
     * 1. Penalty types, system settings (no dependencies)
     * 2. Schedule configurations (no dependencies)
     * 3. Roles & permissions (needed by users)
     * 4. Users (depends on roles)
     * 5. Store settings (depends on users for manual_set_by)
     * 6. Payment configuration (uses settings table)
     * 7. Product catalog (no dependencies)
     */
    public function run(): void
    {
        $this->call([
            // =====================================================
            // FASE 1: Konfigurasi & Setting Sistem (No dependencies)
            // =====================================================
            PenaltyTypeSeeder::class,
            SystemSettingSeeder::class,
            ScheduleConfigurationSeeder::class,

            // =====================================================
            // FASE 2: Roles & Permissions (Before users)
            // =====================================================
            RolePermissionSeeder::class,

            // =====================================================
            // FASE 3: Users (Depends on roles)
            // =====================================================
            UserSeeder::class,

            // =====================================================
            // FASE 4: Store & Payment Settings (Depends on users)
            // =====================================================
            StoreSettingSeeder::class,
            PaymentConfigurationSeeder::class,

            // =====================================================
            // FASE 5: Product Catalog (No dependencies)
            // =====================================================
            KatalogSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ ✅ ✅  SEEDING SELESAI!  ✅ ✅ ✅');
        $this->command->info('');
    }
}
