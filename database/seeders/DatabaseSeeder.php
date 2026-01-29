<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Konfigurasi & Setting Sistem (Krusial)
            PenaltyTypeSeeder::class,
            SystemSettingSeeder::class,
            StoreSettingSeeder::class,
            ScheduleConfigurationSeeder::class,
            
            // Users, Roles & Permissions
            RolePermissionSeeder::class,
            UserSeeder::class,
            
            // Products dari Katalog
            KatalogSeeder::class,
        ]);
    }
}
