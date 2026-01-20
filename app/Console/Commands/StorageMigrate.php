<?php

namespace App\Console\Commands;

use App\Services\Storage\MigrationToolInterface;
use Illuminate\Console\Command;

class StorageMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate 
                            {--dry-run : Simulasi tanpa migrasi file}
                            {--type= : Filter berdasarkan tipe file (product, banner, attendance, profile, leave, report)}
                            {--batch-size=100 : Jumlah file per batch}
                            {--force : Jalankan tanpa konfirmasi}
                            {--stats : Tampilkan statistik migrasi saja}
                            {--generate-variants : Hanya generate missing variants tanpa migrasi path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi file existing ke struktur direktori baru dan generate missing variants';

    /**
     * Execute the console command.
     */
    public function handle(MigrationToolInterface $migrationTool): int
    {
        $dryRun = $this->option('dry-run');
        $type = $this->option('type');
        $batchSize = (int) $this->option('batch-size');
        $force = $this->option('force');
        $statsOnly = $this->option('stats');
        $generateVariantsOnly = $this->option('generate-variants');

        // Validate type if provided
        if ($type) {
            $validTypes = config('filestorage.types');
            if (!isset($validTypes[$type])) {
                $this->error("Tipe file tidak valid: {$type}");
                $this->info('Tipe yang valid: ' . implode(', ', array_keys($validTypes)));
                return Command::FAILURE;
            }
        }

        // Show stats only
        if ($statsOnly) {
            return $this->showStats($migrationTool, $type);
        }

        // Show mode
        if ($dryRun) {
            $this->warn('🔍 Mode simulasi (dry-run) - tidak ada file yang akan dimigrasi');
        } else {
            $this->warn('⚠️  Mode aktif - file akan dimigrasi dan database akan diupdate!');
        }

        $this->newLine();

        // Show current stats
        $this->showStats($migrationTool, $type);
        $this->newLine();

        // Confirmation if not dry-run and not forced
        if (!$dryRun && !$force) {
            if (!$this->confirm('Apakah Anda yakin ingin melanjutkan migrasi?')) {
                $this->info('Operasi dibatalkan.');
                return Command::SUCCESS;
            }
        }

        // Run migration
        if ($generateVariantsOnly) {
            $this->info('Generating missing variants...');
            $result = $this->generateVariantsOnly($migrationTool, $type, $dryRun);
        } else {
            $this->info('Memulai migrasi file...');
            $result = $migrationTool->migrateAll($type, $dryRun, $batchSize);
        }

        $this->newLine();

        // Show results
        $this->displayResults($result, $dryRun);

        // Show errors if any
        if ($result->hasErrors()) {
            $this->newLine();
            $this->error('Terjadi error pada beberapa file:');
            
            $errors = array_slice($result->errors, 0, 10);
            foreach ($errors as $error) {
                $this->line("  - {$error['path']}: {$error['error']}");
            }
            
            if (count($result->errors) > 10) {
                $remaining = count($result->errors) - 10;
                $this->line("  ... dan {$remaining} error lainnya");
            }
        }

        return $result->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Show migration statistics.
     */
    protected function showStats(MigrationToolInterface $migrationTool, ?string $type): int
    {
        $this->info('📊 Statistik Migrasi Storage');
        $this->newLine();

        $stats = $migrationTool->getMigrationStats($type);

        // Overall stats
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total File', $stats['total_files']],
                ['Sudah Migrasi', $stats['migrated']],
                ['Pending Migrasi', $stats['pending']],
                ['Missing Variants', $stats['missing_variants']],
            ]
        );

        // Stats by type
        if (!empty($stats['by_type'])) {
            $this->newLine();
            $this->info('📁 Detail per Tipe:');
            
            $typeRows = [];
            foreach ($stats['by_type'] as $fileType => $typeStats) {
                if ($typeStats['total'] > 0) {
                    $typeRows[] = [
                        $fileType,
                        $typeStats['total'],
                        $typeStats['migrated'],
                        $typeStats['pending'],
                        $typeStats['missing_variants'],
                    ];
                }
            }

            if (!empty($typeRows)) {
                $this->table(
                    ['Tipe', 'Total', 'Migrasi', 'Pending', 'Missing Variants'],
                    $typeRows
                );
            }
        }

        if ($stats['pending'] > 0 || $stats['missing_variants'] > 0) {
            $this->newLine();
            $this->info('💡 Jalankan `php artisan storage:migrate` untuk memulai migrasi.');
        } else {
            $this->newLine();
            $this->info('✅ Semua file sudah dalam format baru.');
        }

        return Command::SUCCESS;
    }

    /**
     * Generate variants only without path migration.
     */
    protected function generateVariantsOnly(MigrationToolInterface $migrationTool, ?string $type, bool $dryRun)
    {
        $files = $migrationTool->scanExistingFiles($type);
        $filesNeedingVariants = $files->filter(fn($f) => $f['needs_variants']);

        $variantsGenerated = 0;
        $errors = [];

        foreach ($filesNeedingVariants as $file) {
            if ($dryRun) {
                $variantsGenerated++;
                continue;
            }

            try {
                $variants = $migrationTool->generateMissingVariants($file['path'], $file['type']);
                $variantsGenerated += count($variants);
            } catch (\Exception $e) {
                $errors[] = [
                    'path' => $file['path'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return new \App\Services\Storage\DTOs\MigrationResult(
            filesScanned: $filesNeedingVariants->count(),
            filesMigrated: 0,
            filesSkipped: 0,
            variantsGenerated: $variantsGenerated,
            databaseUpdated: 0,
            migratedFiles: [],
            errors: $errors,
            dryRun: $dryRun,
        );
    }

    /**
     * Display migration results.
     */
    protected function displayResults($result, bool $dryRun): void
    {
        $action = $dryRun ? 'akan dimigrasi' : 'dimigrasi';
        
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['File dipindai', $result->filesScanned],
                ["File {$action}", $result->filesMigrated],
                ['File dilewati', $result->filesSkipped],
                ['Variants dibuat', $result->variantsGenerated],
                ['Database diupdate', $result->databaseUpdated],
                ['Error', count($result->errors)],
                ['Success Rate', $result->getSuccessRate() . '%'],
            ]
        );

        if ($dryRun && ($result->filesMigrated > 0 || $result->variantsGenerated > 0)) {
            $this->newLine();
            $this->info('💡 Jalankan tanpa --dry-run untuk melakukan migrasi secara permanen.');
        }

        if (!$dryRun && $result->filesMigrated > 0) {
            $this->newLine();
            $this->info("✅ Berhasil migrasi {$result->filesMigrated} file");
        }

        if (!$dryRun && $result->variantsGenerated > 0) {
            $this->info("✅ Berhasil generate {$result->variantsGenerated} variants");
        }
    }
}
