<?php

namespace App\Console\Commands;

use App\Services\Storage\FileCleanupServiceInterface;
use Illuminate\Console\Command;

class StorageCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:cleanup 
                            {--dry-run : Simulasi tanpa menghapus file}
                            {--type= : Filter berdasarkan tipe file (product, banner, attendance, profile, leave, report)}
                            {--force : Jalankan tanpa konfirmasi}
                            {--temp-only : Hanya bersihkan file temporary}
                            {--orphan-only : Hanya bersihkan file orphan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan file orphan dan temporary dari storage';

    /**
     * Execute the console command.
     */
    public function handle(FileCleanupServiceInterface $cleanupService): int
    {
        $dryRun = $this->option('dry-run');
        $type = $this->option('type');
        $force = $this->option('force');
        $tempOnly = $this->option('temp-only');
        $orphanOnly = $this->option('orphan-only');

        // Validate type if provided
        if ($type) {
            $validTypes = config('filestorage.types');
            if (!isset($validTypes[$type])) {
                $this->error("Tipe file tidak valid: {$type}");
                $this->info('Tipe yang valid: ' . implode(', ', array_keys($validTypes)));
                return Command::FAILURE;
            }
        }

        // Show mode
        if ($dryRun) {
            $this->warn('🔍 Mode simulasi (dry-run) - tidak ada file yang akan dihapus');
        } else {
            $this->warn('⚠️  Mode aktif - file akan dihapus secara permanen!');
        }

        $this->newLine();

        // Confirmation if not dry-run and not forced
        if (!$dryRun && !$force) {
            if (!$this->confirm('Apakah Anda yakin ingin melanjutkan?')) {
                $this->info('Operasi dibatalkan.');
                return Command::SUCCESS;
            }
        }

        // Determine what to clean
        if ($tempOnly) {
            $this->info('Membersihkan file temporary...');
            $result = $cleanupService->cleanTempFiles(null, $dryRun);
        } elseif ($orphanOnly) {
            $this->info('Membersihkan file orphan...');
            $this->showOrphanPreview($cleanupService, $type);
            $result = $cleanupService->cleanOrphanFiles($dryRun, $type);
        } else {
            $this->info('Membersihkan semua file tidak terpakai...');
            $this->showOrphanPreview($cleanupService, $type);
            $result = $cleanupService->cleanAll($dryRun, $type);
        }

        $this->newLine();

        // Show results
        $this->displayResults($result, $dryRun);

        // Show errors if any
        if ($result->hasErrors()) {
            $this->newLine();
            $this->error('Terjadi error pada beberapa file:');
            foreach ($result->errors as $error) {
                $this->line("  - {$error['path']}: {$error['error']}");
            }
        }

        return $result->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Show preview of orphan files.
     */
    protected function showOrphanPreview(FileCleanupServiceInterface $cleanupService, ?string $type): void
    {
        $orphanFiles = $cleanupService->findOrphanFiles($type);

        if ($orphanFiles->isEmpty()) {
            $this->info('Tidak ada file orphan yang ditemukan.');
            return;
        }

        $this->info("Ditemukan {$orphanFiles->count()} file orphan:");
        $this->newLine();

        // Group by type
        $grouped = $orphanFiles->groupBy('type');

        foreach ($grouped as $fileType => $files) {
            $totalSize = $files->sum('size');
            $sizeMB = round($totalSize / (1024 * 1024), 2);
            
            $this->line("  📁 {$fileType}: {$files->count()} file ({$sizeMB} MB)");
            
            // Show first 5 files as sample
            $sample = $files->take(5);
            foreach ($sample as $file) {
                $sizeKB = round($file['size'] / 1024, 1);
                $age = $file['modified_at']->diffForHumans();
                $this->line("     - {$file['path']} ({$sizeKB} KB, {$age})");
            }
            
            if ($files->count() > 5) {
                $remaining = $files->count() - 5;
                $this->line("     ... dan {$remaining} file lainnya");
            }
        }

        $this->newLine();
    }

    /**
     * Display cleanup results.
     */
    protected function displayResults($result, bool $dryRun): void
    {
        $action = $dryRun ? 'akan dihapus' : 'dihapus';
        
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['File dipindai', $result->filesScanned],
                ["File {$action}", $result->filesDeleted],
                ['Ruang dibebaskan', $result->getBytesFreedMB() . ' MB'],
                ['Error', count($result->errors)],
            ]
        );

        if ($dryRun && $result->filesDeleted > 0) {
            $this->newLine();
            $this->info('💡 Jalankan tanpa --dry-run untuk menghapus file secara permanen.');
        }

        if (!$dryRun && $result->filesDeleted > 0) {
            $this->newLine();
            $this->info("✅ Berhasil membersihkan {$result->filesDeleted} file ({$result->getBytesFreedMB()} MB)");
        }
    }
}
