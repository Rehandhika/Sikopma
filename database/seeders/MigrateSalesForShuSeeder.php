<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSalesForShuSeeder extends Seeder
{
    /**
     * Seeder untuk migrasi tabel sales untuk sistem SHU Point
     * 
     * Perubahan:
     * 1. Tambah kolom student_id (foreign key ke students)
     * 2. Tambah kolom shu_points_earned
     * 3. Tambah kolom shu_percentage_bps
     * 4. Tambah index untuk performa
     * 5. Tambah soft delete
     */
    public function run(): void
    {
        $this->command->info('🔄 Memulai migrasi tabel sales untuk SHU...');

        // Step 1: Tambah kolom baru
        $this->addNewColumns();

        // Step 2: Set default values untuk data existing
        $this->setDefaultValues();

        // Step 3: Tambah indexes
        $this->addIndexes();

        // Step 4: Tambah soft delete
        $this->addSoftDelete();

        $this->command->info('✅ Migrasi sales selesai!');
    }

    /**
     * Tambah kolom baru untuk SHU
     */
    private function addNewColumns(): void
    {
        $this->command->info('➕ Menambah kolom SHU...');

        // Tambah student_id
        if (!Schema::hasColumn('sales', 'student_id')) {
            DB::statement("
                ALTER TABLE sales 
                ADD COLUMN student_id BIGINT UNSIGNED NULL AFTER cashier_id,
                ADD CONSTRAINT sales_student_id_foreign 
                    FOREIGN KEY (student_id) 
                    REFERENCES students(id) 
                    ON DELETE SET NULL
            ");
            $this->command->info('✓ Kolom student_id ditambahkan');
        }

        // Tambah shu_points_earned
        if (!Schema::hasColumn('sales', 'shu_points_earned')) {
            DB::statement("
                ALTER TABLE sales 
                ADD COLUMN shu_points_earned BIGINT UNSIGNED DEFAULT 0 AFTER change_amount
            ");
            $this->command->info('✓ Kolom shu_points_earned ditambahkan');
        }

        // Tambah shu_percentage_bps
        if (!Schema::hasColumn('sales', 'shu_percentage_bps')) {
            DB::statement("
                ALTER TABLE sales 
                ADD COLUMN shu_percentage_bps INT UNSIGNED DEFAULT 0 AFTER shu_points_earned
            ");
            $this->command->info('✓ Kolom shu_percentage_bps ditambahkan');
        }
    }

    /**
     * Set default values untuk data existing
     */
    private function setDefaultValues(): void
    {
        $this->command->info('🔧 Set default values...');

        // Update existing sales dengan default values
        $updated = DB::table('sales')
            ->whereNull('shu_points_earned')
            ->orWhereNull('shu_percentage_bps')
            ->update([
                'shu_points_earned' => 0,
                'shu_percentage_bps' => 0,
                'student_id' => null
            ]);

        $this->command->info("✓ Update {$updated} sales records dengan default values");
    }

    /**
     * Tambah indexes untuk performa
     */
    private function addIndexes(): void
    {
        $this->command->info('📊 Menambah indexes...');

        // Check dan tambah index student_id, date
        $indexExists = DB::select("
            SHOW INDEX FROM sales 
            WHERE Key_name = 'sales_student_id_date_index'
        ");

        if (empty($indexExists)) {
            DB::statement("
                ALTER TABLE sales 
                ADD INDEX sales_student_id_date_index (student_id, date)
            ");
            $this->command->info('✓ Index (student_id, date) ditambahkan');
        }
    }

    /**
     * Tambah soft delete
     */
    private function addSoftDelete(): void
    {
        $this->command->info('🗑️  Menambah soft delete...');

        if (!Schema::hasColumn('sales', 'deleted_at')) {
            DB::statement("
                ALTER TABLE sales 
                ADD COLUMN deleted_at TIMESTAMP NULL AFTER updated_at
            ");
            $this->command->info('✓ Kolom deleted_at ditambahkan');
        }
    }
}
