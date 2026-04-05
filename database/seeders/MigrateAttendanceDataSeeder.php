<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateAttendanceDataSeeder extends Seeder
{
    /**
     * Seeder untuk migrasi data attendance dari production ke struktur baru
     * 
     * Perubahan:
     * 1. Backup data check_in_photo ke tabel terpisah (untuk dokumentasi)
     * 2. Tambah kolom late_minutes, late_category, deleted_at
     * 3. Hapus kolom check_in_photo
     * 4. Hapus file foto fisik dari storage
     */
    public function run(): void
    {
        $this->command->info('🔄 Memulai migrasi data attendance...');

        // Step 1: Backup foto check-in (untuk dokumentasi saja)
        $this->backupCheckInPhotos();

        // Step 2: Tambah kolom baru jika belum ada
        $this->addNewColumns();

        // Step 3: Migrasi data late_minutes dan late_category dari notes jika ada
        $this->migrateLateData();

        // Step 4: Hapus kolom check_in_photo
        $this->removeCheckInPhotoColumn();

        // Step 5: Hapus file foto fisik dari storage
        $this->deletePhotoFiles();

        $this->command->info('✅ Migrasi attendance selesai!');
    }

    /**
     * Backup data foto check-in ke tabel terpisah
     */
    private function backupCheckInPhotos(): void
    {
        $this->command->info('📸 Backup foto check-in...');

        // Buat tabel backup jika belum ada
        if (!Schema::hasTable('attendances_photo_backup')) {
            DB::statement("
                CREATE TABLE attendances_photo_backup (
                    id BIGINT UNSIGNED PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    date DATE NOT NULL,
                    check_in_photo VARCHAR(255),
                    created_at TIMESTAMP NULL,
                    backed_up_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_date (user_id, date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Insert data foto yang belum di-backup
        $count = DB::statement("
            INSERT IGNORE INTO attendances_photo_backup (id, user_id, date, check_in_photo, created_at)
            SELECT id, user_id, date, check_in_photo, created_at
            FROM attendances
            WHERE check_in_photo IS NOT NULL
            AND id NOT IN (SELECT id FROM attendances_photo_backup)
        ");

        $total = DB::table('attendances_photo_backup')->count();
        $this->command->info("✓ Backup {$total} foto check-in");
    }

    /**
     * Tambah kolom baru jika belum ada
     */
    private function addNewColumns(): void
    {
        $this->command->info('➕ Menambah kolom baru...');

        // Tambah late_minutes
        if (!Schema::hasColumn('attendances', 'late_minutes')) {
            DB::statement("
                ALTER TABLE attendances 
                ADD COLUMN late_minutes INT NULL AFTER status
            ");
            $this->command->info('✓ Kolom late_minutes ditambahkan');
        }

        // Tambah late_category
        if (!Schema::hasColumn('attendances', 'late_category')) {
            DB::statement("
                ALTER TABLE attendances 
                ADD COLUMN late_category VARCHAR(5) NULL AFTER late_minutes
            ");
            $this->command->info('✓ Kolom late_category ditambahkan');
        }

        // Tambah deleted_at untuk soft delete
        if (!Schema::hasColumn('attendances', 'deleted_at')) {
            DB::statement("
                ALTER TABLE attendances 
                ADD COLUMN deleted_at TIMESTAMP NULL AFTER updated_at
            ");
            $this->command->info('✓ Kolom deleted_at ditambahkan');
        }
    }

    /**
     * Migrasi data late dari notes jika ada pattern tertentu
     */
    private function migrateLateData(): void
    {
        $this->command->info('⏰ Migrasi data keterlambatan...');

        // Update late_minutes dan late_category dari data yang sudah ada
        // Jika ada data di production yang sudah punya informasi late
        
        // Contoh: Extract dari notes atau field lain
        // Sesuaikan dengan format data Anda
        
        $updated = DB::table('attendances')
            ->where('status', 'late')
            ->whereNull('late_minutes')
            ->update([
                'late_minutes' => 0, // Default, bisa disesuaikan
                'late_category' => null
            ]);

        $this->command->info("✓ Update {$updated} record keterlambatan");
    }

    /**
     * Hapus kolom check_in_photo
     */
    private function removeCheckInPhotoColumn(): void
    {
        $this->command->warn('⚠️  MENGHAPUS kolom check_in_photo...');

        // Verifikasi backup
        $backupCount = DB::table('attendances_photo_backup')->count();
        $photoCount = DB::table('attendances')->whereNotNull('check_in_photo')->count();

        if ($backupCount < $photoCount) {
            $this->command->error("❌ GAGAL: Backup tidak lengkap! Backup: {$backupCount}, Actual: {$photoCount}");
            $this->command->error("Backup dulu sebelum menghapus kolom!");
            return;
        }

        // Hapus kolom
        if (Schema::hasColumn('attendances', 'check_in_photo')) {
            Schema::table('attendances', function ($table) {
                $table->dropColumn('check_in_photo');
            });
            $this->command->info('✓ Kolom check_in_photo dihapus');
        } else {
            $this->command->info('ℹ️  Kolom check_in_photo sudah tidak ada');
        }
    }

    /**
     * Hapus file foto fisik dari storage
     */
    private function deletePhotoFiles(): void
    {
        $this->command->warn('🗑️  MENGHAPUS file foto dari storage...');

        // Get list foto dari backup
        $photos = DB::table('attendances_photo_backup')
            ->whereNotNull('check_in_photo')
            ->pluck('check_in_photo');

        if ($photos->isEmpty()) {
            $this->command->info('ℹ️  Tidak ada foto untuk dihapus');
            return;
        }

        $deleted = 0;
        $notFound = 0;
        $storagePath = storage_path('app/public/');

        foreach ($photos as $photo) {
            $filePath = $storagePath . $photo;
            
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    $deleted++;
                }
            } else {
                $notFound++;
            }
        }

        $this->command->info("✓ Foto dihapus: {$deleted}");
        if ($notFound > 0) {
            $this->command->warn("⚠️  Foto tidak ditemukan: {$notFound}");
        }

        // Hapus folder kosong
        $this->cleanupEmptyFolders($storagePath . 'attendance/');
        
        $this->command->info('✓ Cleanup storage selesai');
    }

    /**
     * Hapus folder kosong secara rekursif
     */
    private function cleanupEmptyFolders(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        $items = array_diff($items, ['.', '..']);

        foreach ($items as $item) {
            $itemPath = $path . '/' . $item;
            if (is_dir($itemPath)) {
                $this->cleanupEmptyFolders($itemPath);
                
                // Cek apakah folder kosong setelah cleanup
                $contents = scandir($itemPath);
                $contents = array_diff($contents, ['.', '..']);
                
                if (empty($contents)) {
                    rmdir($itemPath);
                }
            }
        }
    }

    /**
     * DEPRECATED: Restore tidak diperlukan karena foto akan dihapus permanen
     * 
     * Jika benar-benar perlu restore (emergency), gunakan backup database lengkap
     */
    public function restore(): void
    {
        $this->command->error('❌ Restore foto tidak tersedia!');
        $this->command->warn('⚠️  Foto check-in sudah dihapus permanen dari storage.');
        $this->command->warn('⚠️  Data path foto masih ada di tabel attendances_photo_backup untuk dokumentasi.');
        $this->command->info('ℹ️  Jika perlu restore, gunakan backup database lengkap sebelum migrasi.');
    }
}
