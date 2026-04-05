<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateProductionDataSeeder extends Seeder
{
    /**
     * Master seeder untuk migrasi semua data production
     * 
     * Seeder ini akan menjalankan semua migrasi data secara berurutan
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai migrasi data production...');
        $this->command->newLine();

        // Validasi pre-migration
        if (!$this->validatePreMigration()) {
            $this->command->error('❌ Validasi pre-migration gagal!');
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Migrasi Activity Logs
            $this->command->info('1️⃣  Migrasi Activity Logs...');
            $this->migrateActivityLogs();

            // 2. Migrasi Purchase Items
            $this->command->info('2️⃣  Migrasi Purchase Items...');
            $this->migratePurchaseItems();

            // 3. Migrasi Schedule Assignments
            $this->command->info('3️⃣  Migrasi Schedule Assignments...');
            $this->migrateScheduleAssignments();

            // 4. Migrasi Schedule Change Requests
            $this->command->info('4️⃣  Migrasi Schedule Change Requests...');
            $this->migrateScheduleChangeRequests();

            // 5. Migrasi SHU Point Transactions (rename column)
            $this->command->info('5️⃣  Migrasi SHU Point Transactions...');
            $this->migrateShuPointTransactions();

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ Semua migrasi data berhasil!');
            
            // Validasi post-migration
            $this->validatePostMigration();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Migrasi gagal: ' . $e->getMessage());
            $this->command->error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Validasi sebelum migrasi
     */
    private function validatePreMigration(): bool
    {
        $this->command->info('🔍 Validasi pre-migration...');

        $checks = [
            'users' => DB::table('users')->count(),
            'products' => DB::table('products')->count(),
            'attendances' => DB::table('attendances')->count(),
            'sales' => DB::table('sales')->count(),
        ];

        foreach ($checks as $table => $count) {
            $this->command->info("  ✓ {$table}: {$count} records");
        }

        // Cek apakah tabel students sudah ada
        if (!Schema::hasTable('students')) {
            $this->command->warn('  ⚠️  Tabel students belum ada. Jalankan migrasi create_students_table terlebih dahulu.');
            return false;
        }

        return true;
    }

    /**
     * Migrasi Activity Logs - tambah kolom metadata
     */
    private function migrateActivityLogs(): void
    {
        if (!Schema::hasColumn('activity_logs', 'metadata')) {
            DB::statement("
                ALTER TABLE activity_logs 
                ADD COLUMN metadata JSON NULL AFTER user_agent
            ");
            $this->command->info('  ✓ Kolom metadata ditambahkan ke activity_logs');
        } else {
            $this->command->info('  ℹ️  Kolom metadata sudah ada');
        }
    }

    /**
     * Migrasi Purchase Items - tambah kolom product_variant_id
     */
    private function migratePurchaseItems(): void
    {
        if (!Schema::hasColumn('purchase_items', 'product_variant_id')) {
            DB::statement("
                ALTER TABLE purchase_items 
                ADD COLUMN product_variant_id BIGINT UNSIGNED NULL AFTER product_id,
                ADD CONSTRAINT purchase_items_product_variant_id_foreign 
                    FOREIGN KEY (product_variant_id) 
                    REFERENCES product_variants(id) 
                    ON DELETE SET NULL
            ");
            $this->command->info('  ✓ Kolom product_variant_id ditambahkan ke purchase_items');
        } else {
            $this->command->info('  ℹ️  Kolom product_variant_id sudah ada');
        }
    }

    /**
     * Migrasi Schedule Assignments - tambah status in_progress
     */
    private function migrateScheduleAssignments(): void
    {
        // Cek apakah enum sudah include 'in_progress'
        $columnType = DB::select("
            SELECT COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'schedule_assignments' 
            AND COLUMN_NAME = 'status'
        ");

        if (!empty($columnType)) {
            $enumValues = $columnType[0]->COLUMN_TYPE;
            
            if (strpos($enumValues, 'in_progress') === false) {
                DB::statement("
                    ALTER TABLE schedule_assignments 
                    MODIFY COLUMN status ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'swapped') 
                    NOT NULL DEFAULT 'scheduled'
                ");
                $this->command->info('  ✓ Status in_progress ditambahkan ke schedule_assignments');
            } else {
                $this->command->info('  ℹ️  Status in_progress sudah ada');
            }
        }
    }

    /**
     * Migrasi Schedule Change Requests - tambah kolom target
     */
    private function migrateScheduleChangeRequests(): void
    {
        $columnsAdded = false;

        if (!Schema::hasColumn('schedule_change_requests', 'target_id')) {
            DB::statement("
                ALTER TABLE schedule_change_requests 
                ADD COLUMN target_id BIGINT UNSIGNED NULL AFTER user_id,
                ADD CONSTRAINT schedule_change_requests_target_id_foreign 
                    FOREIGN KEY (target_id) 
                    REFERENCES users(id) 
                    ON DELETE SET NULL
            ");
            $columnsAdded = true;
        }

        if (!Schema::hasColumn('schedule_change_requests', 'target_assignment_id')) {
            DB::statement("
                ALTER TABLE schedule_change_requests 
                ADD COLUMN target_assignment_id BIGINT UNSIGNED NULL AFTER original_assignment_id,
                ADD CONSTRAINT schedule_change_requests_target_assignment_id_foreign 
                    FOREIGN KEY (target_assignment_id) 
                    REFERENCES schedule_assignments(id) 
                    ON DELETE SET NULL
            ");
            $columnsAdded = true;
        }

        if ($columnsAdded) {
            $this->command->info('  ✓ Kolom target ditambahkan ke schedule_change_requests');
        } else {
            $this->command->info('  ℹ️  Kolom target sudah ada');
        }
    }

    /**
     * Migrasi SHU Point Transactions - rename percentage_bps ke conversion_rate
     */
    private function migrateShuPointTransactions(): void
    {
        if (!Schema::hasTable('shu_point_transactions')) {
            $this->command->warn('  ⚠️  Tabel shu_point_transactions belum ada');
            return;
        }

        if (Schema::hasColumn('shu_point_transactions', 'percentage_bps') && 
            !Schema::hasColumn('shu_point_transactions', 'conversion_rate')) {
            
            DB::statement("
                ALTER TABLE shu_point_transactions 
                CHANGE COLUMN percentage_bps conversion_rate INT UNSIGNED DEFAULT 0
            ");
            $this->command->info('  ✓ Kolom percentage_bps direname ke conversion_rate');
        } else {
            $this->command->info('  ℹ️  Kolom conversion_rate sudah ada');
        }
    }

    /**
     * Validasi setelah migrasi
     */
    private function validatePostMigration(): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Validasi post-migration...');

        $validations = [
            'activity_logs.metadata' => Schema::hasColumn('activity_logs', 'metadata'),
            'purchase_items.product_variant_id' => Schema::hasColumn('purchase_items', 'product_variant_id'),
            'schedule_change_requests.target_id' => Schema::hasColumn('schedule_change_requests', 'target_id'),
            'schedule_change_requests.target_assignment_id' => Schema::hasColumn('schedule_change_requests', 'target_assignment_id'),
        ];

        $allPassed = true;
        foreach ($validations as $check => $result) {
            if ($result) {
                $this->command->info("  ✓ {$check}");
            } else {
                $this->command->error("  ✗ {$check}");
                $allPassed = false;
            }
        }

        if ($allPassed) {
            $this->command->info('✅ Semua validasi passed!');
        } else {
            $this->command->warn('⚠️  Beberapa validasi gagal');
        }
    }
}
