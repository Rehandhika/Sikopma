<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidationSeeder extends Seeder
{
    /**
     * Seeder untuk validasi hasil migrasi database
     * 
     * Jalankan seeder ini setelah migrasi untuk memastikan
     * semua perubahan berhasil dan data tetap konsisten
     */
    public function run(): void
    {
        $this->command->info('🔍 Memulai validasi database...');
        $this->command->newLine();

        $allPassed = true;

        // 1. Validasi Struktur Tabel
        $allPassed = $this->validateTableStructure() && $allPassed;

        // 2. Validasi Data Count
        $allPassed = $this->validateDataCount() && $allPassed;

        // 3. Validasi Foreign Keys
        $allPassed = $this->validateForeignKeys() && $allPassed;

        // 4. Validasi Indexes
        $allPassed = $this->validateIndexes() && $allPassed;

        // 5. Validasi Backup Data
        $allPassed = $this->validateBackupData() && $allPassed;

        $this->command->newLine();
        if ($allPassed) {
            $this->command->info('✅ SEMUA VALIDASI PASSED!');
            $this->command->info('Database siap digunakan.');
        } else {
            $this->command->error('❌ BEBERAPA VALIDASI GAGAL!');
            $this->command->error('Periksa log di atas dan perbaiki masalah sebelum melanjutkan.');
        }
    }

    /**
     * Validasi struktur tabel
     */
    private function validateTableStructure(): bool
    {
        $this->command->info('1️⃣  Validasi Struktur Tabel');

        $checks = [
            // Attendances
            ['table' => 'attendances', 'column' => 'late_minutes', 'required' => true],
            ['table' => 'attendances', 'column' => 'late_category', 'required' => true],
            ['table' => 'attendances', 'column' => 'deleted_at', 'required' => true],
            ['table' => 'attendances', 'column' => 'check_in_photo', 'required' => false], // Harus sudah dihapus

            // Sales
            ['table' => 'sales', 'column' => 'student_id', 'required' => true],
            ['table' => 'sales', 'column' => 'shu_points_earned', 'required' => true],
            ['table' => 'sales', 'column' => 'shu_percentage_bps', 'required' => true],
            ['table' => 'sales', 'column' => 'deleted_at', 'required' => true],

            // Activity Logs
            ['table' => 'activity_logs', 'column' => 'metadata', 'required' => true],

            // Purchase Items
            ['table' => 'purchase_items', 'column' => 'product_variant_id', 'required' => true],

            // Schedule Change Requests
            ['table' => 'schedule_change_requests', 'column' => 'target_id', 'required' => true],
            ['table' => 'schedule_change_requests', 'column' => 'target_assignment_id', 'required' => true],

            // Tabel Baru
            ['table' => 'students', 'column' => null, 'required' => true], // Cek tabel ada
            ['table' => 'shu_point_transactions', 'column' => null, 'required' => true],
            ['table' => 'attendances_photo_backup', 'column' => null, 'required' => true],
        ];

        $allPassed = true;
        foreach ($checks as $check) {
            $table = $check['table'];
            $column = $check['column'];
            $required = $check['required'];

            if ($column === null) {
                // Cek tabel ada
                $exists = Schema::hasTable($table);
                if ($exists === $required) {
                    $this->command->info("  ✓ Tabel {$table} " . ($required ? 'ada' : 'tidak ada'));
                } else {
                    $this->command->error("  ✗ Tabel {$table} " . ($required ? 'TIDAK ADA' : 'MASIH ADA'));
                    $allPassed = false;
                }
            } else {
                // Cek kolom ada
                $exists = Schema::hasColumn($table, $column);
                if ($exists === $required) {
                    $this->command->info("  ✓ {$table}.{$column} " . ($required ? 'ada' : 'tidak ada'));
                } else {
                    $this->command->error("  ✗ {$table}.{$column} " . ($required ? 'TIDAK ADA' : 'MASIH ADA'));
                    $allPassed = false;
                }
            }
        }

        $this->command->newLine();
        return $allPassed;
    }

    /**
     * Validasi jumlah data
     */
    private function validateDataCount(): bool
    {
        $this->command->info('2️⃣  Validasi Jumlah Data');

        // Expected counts dari production (5 April 2026)
        $expectedCounts = [
            'users' => 14,
            'products' => 262,
            'attendances' => 714,
            'sales' => 2,
            'sale_items' => 5,
            'schedules' => 4,
            'schedule_assignments' => 142,
            'penalties' => 668,
        ];

        $allPassed = true;
        foreach ($expectedCounts as $table => $expected) {
            $actual = DB::table($table)->count();
            
            if ($actual >= $expected) {
                $this->command->info("  ✓ {$table}: {$actual} records (expected: >= {$expected})");
            } else {
                $this->command->error("  ✗ {$table}: {$actual} records (expected: >= {$expected})");
                $allPassed = false;
            }
        }

        $this->command->newLine();
        return $allPassed;
    }

    /**
     * Validasi foreign keys
     */
    private function validateForeignKeys(): bool
    {
        $this->command->info('3️⃣  Validasi Foreign Keys');

        $foreignKeys = [
            ['table' => 'sales', 'column' => 'student_id', 'ref_table' => 'students'],
            ['table' => 'shu_point_transactions', 'column' => 'student_id', 'ref_table' => 'students'],
            ['table' => 'shu_point_transactions', 'column' => 'sale_id', 'ref_table' => 'sales'],
            ['table' => 'purchase_items', 'column' => 'product_variant_id', 'ref_table' => 'product_variants'],
            ['table' => 'schedule_change_requests', 'column' => 'target_id', 'ref_table' => 'users'],
            ['table' => 'schedule_change_requests', 'column' => 'target_assignment_id', 'ref_table' => 'schedule_assignments'],
        ];

        $allPassed = true;
        foreach ($foreignKeys as $fk) {
            $exists = $this->checkForeignKeyExists($fk['table'], $fk['column'], $fk['ref_table']);
            
            if ($exists) {
                $this->command->info("  ✓ FK: {$fk['table']}.{$fk['column']} -> {$fk['ref_table']}");
            } else {
                $this->command->error("  ✗ FK: {$fk['table']}.{$fk['column']} -> {$fk['ref_table']} TIDAK ADA");
                $allPassed = false;
            }
        }

        $this->command->newLine();
        return $allPassed;
    }

    /**
     * Validasi indexes
     */
    private function validateIndexes(): bool
    {
        $this->command->info('4️⃣  Validasi Indexes');

        $indexes = [
            ['table' => 'sales', 'columns' => ['student_id', 'date']],
            ['table' => 'shu_point_transactions', 'columns' => ['student_id', 'created_at']],
            ['table' => 'attendances', 'columns' => ['user_id', 'date']],
        ];

        $allPassed = true;
        foreach ($indexes as $idx) {
            $exists = $this->checkIndexExists($idx['table'], $idx['columns']);
            $columnStr = implode(', ', $idx['columns']);
            
            if ($exists) {
                $this->command->info("  ✓ Index: {$idx['table']} ({$columnStr})");
            } else {
                $this->command->warn("  ⚠️  Index: {$idx['table']} ({$columnStr}) tidak ada (optional)");
            }
        }

        $this->command->newLine();
        return $allPassed;
    }

    /**
     * Validasi backup data
     */
    private function validateBackupData(): bool
    {
        $this->command->info('5️⃣  Validasi Backup Data');

        $allPassed = true;

        // Cek backup foto attendance
        if (Schema::hasTable('attendances_photo_backup')) {
            $backupCount = DB::table('attendances_photo_backup')->count();
            $this->command->info("  ✓ Backup foto attendance: {$backupCount} records");

            // Cek apakah semua foto ter-backup
            if (Schema::hasColumn('attendances', 'check_in_photo')) {
                $photoCount = DB::table('attendances')->whereNotNull('check_in_photo')->count();
                if ($backupCount >= $photoCount) {
                    $this->command->info("  ✓ Semua foto ter-backup ({$backupCount} >= {$photoCount})");
                } else {
                    $this->command->error("  ✗ Backup tidak lengkap ({$backupCount} < {$photoCount})");
                    $allPassed = false;
                }
            }
        } else {
            $this->command->warn("  ⚠️  Tabel backup foto tidak ada");
        }

        $this->command->newLine();
        return $allPassed;
    }

    /**
     * Helper: Cek foreign key exists
     */
    private function checkForeignKeyExists(string $table, string $column, string $refTable): bool
    {
        $result = DB::select("
            SELECT COUNT(*) as count
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
            AND REFERENCED_TABLE_NAME = ?
        ", [$table, $column, $refTable]);

        return $result[0]->count > 0;
    }

    /**
     * Helper: Cek index exists
     */
    private function checkIndexExists(string $table, array $columns): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        
        $indexColumns = [];
        foreach ($indexes as $index) {
            if (!isset($indexColumns[$index->Key_name])) {
                $indexColumns[$index->Key_name] = [];
            }
            $indexColumns[$index->Key_name][] = $index->Column_name;
        }

        foreach ($indexColumns as $indexName => $indexCols) {
            if ($indexCols === $columns) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate report
     */
    public function generateReport(): void
    {
        $this->command->info('📊 Generating Migration Report...');

        $report = [
            'timestamp' => now()->toDateTimeString(),
            'database' => DB::getDatabaseName(),
            'tables' => [],
        ];

        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $count = DB::table($tableName)->count();
            $report['tables'][$tableName] = $count;
        }

        $filename = 'migration_report_' . date('Ymd_His') . '.json';
        file_put_contents(storage_path('logs/' . $filename), json_encode($report, JSON_PRETTY_PRINT));

        $this->command->info("✓ Report saved to: storage/logs/{$filename}");
    }
}
