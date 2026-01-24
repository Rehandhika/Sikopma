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
            RolePermissionSeeder::class,
            UserSeeder::class,
            VariantOptionSeeder::class,
            ProductSeeder::class,
            PurchaseSeeder::class,
            SaleSeeder::class,
            ScheduleSeeder::class,
            PenaltyTypeSeeder::class,
            SystemSettingSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}
