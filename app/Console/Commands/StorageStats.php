<?php

namespace App\Console\Commands;

use App\Services\Storage\StorageMonitorInterface;
use Illuminate\Console\Command;

class StorageStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:stats 
                            {--type= : Filter berdasarkan tipe file (product, banner, attendance, profile, leave, report)}
                            {--largest= : Tampilkan N file terbesar (default: 10)}
                            {--json : Output dalam format JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tampilkan statistik penggunaan storage';

    /**
     * Execute the console command.
     */
    public function handle(StorageMonitorInterface $storageMonitor): int
    {
        $type = $this->option('type');
        $largestCount = (int) ($this->option('largest') ?? 10);
        $jsonOutput = $this->option('json');

        // Validate type if provided
        if ($type) {
            $validTypes = config('filestorage.types');
            if (!isset($validTypes[$type])) {
                $this->error("Tipe file tidak valid: {$type}");
                $this->info('Tipe yang valid: ' . implode(', ', array_keys($validTypes)));
                return Command::FAILURE;
            }
        }

        // Get statistics
        $stats = $storageMonitor->getStatistics();
        $thresholdResult = $storageMonitor->checkThreshold();

        // JSON output
        if ($jsonOutput) {
            $output = [
                'statistics' => $stats->toArray(),
                'threshold' => $thresholdResult->toArray(),
            ];

            if ($largestCount > 0) {
                $largestFiles = $storageMonitor->getLargestFiles($largestCount);
                $output['largest_files'] = $largestFiles->toArray();
            }

            $this->line(json_encode($output, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        // Display header
        $this->displayHeader($stats, $thresholdResult);

        // Display usage by type
        $this->displayUsageByType($stats, $type);

        // Display largest files
        if ($largestCount > 0) {
            $this->displayLargestFiles($storageMonitor, $largestCount, $type);
        }

        // Display threshold status
        $this->displayThresholdStatus($thresholdResult);

        return Command::SUCCESS;
    }

    /**
     * Display header with overall statistics.
     */
    protected function displayHeader($stats, $thresholdResult): void
    {
        $this->newLine();
        $this->info('📊 Statistik Storage SIKOPMA');
        $this->line('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Status indicator
        $statusIcon = match ($thresholdResult->status) {
            'ok' => '🟢',
            'warning' => '🟡',
            'critical' => '🔴',
            default => '⚪',
        };

        $this->line("Status: {$statusIcon} {$thresholdResult->getStatusLabel()}");
        $this->newLine();

        // Overall stats table
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total Penggunaan', $this->formatBytes($stats->totalBytes)],
                ['Total File', number_format($stats->totalFiles)],
                ['Ruang Tersedia', $this->formatBytes($stats->availableBytes)],
                ['Persentase Terpakai', number_format($stats->usagePercentage, 2) . '%'],
                ['Waktu Generate', $stats->generatedAt->format('Y-m-d H:i:s')],
            ]
        );
    }

    /**
     * Display usage breakdown by type.
     */
    protected function displayUsageByType($stats, ?string $filterType): void
    {
        $this->newLine();
        $this->info('📁 Penggunaan per Tipe File');
        $this->line('───────────────────────────────────────────────────────');
        $this->newLine();

        $rows = [];
        foreach ($stats->byType as $type => $typeStats) {
            // Skip if filtering by type and doesn't match
            if ($filterType && $type !== $filterType) {
                continue;
            }

            $rows[] = [
                $type,
                $this->formatBytes($typeStats['bytes']),
                number_format($typeStats['files']),
                number_format($typeStats['percentage'], 2) . '%',
                $typeStats['disk'],
            ];
        }

        if (empty($rows)) {
            $this->warn('Tidak ada data untuk ditampilkan.');
            return;
        }

        $this->table(
            ['Tipe', 'Ukuran', 'Jumlah File', 'Persentase', 'Disk'],
            $rows
        );

        // Show bar chart visualization
        $this->displayBarChart($stats->byType, $filterType);
    }

    /**
     * Display simple bar chart for usage.
     */
    protected function displayBarChart(array $byType, ?string $filterType): void
    {
        $this->newLine();
        $maxBarLength = 40;

        foreach ($byType as $type => $typeStats) {
            if ($filterType && $type !== $filterType) {
                continue;
            }

            $percentage = $typeStats['percentage'];
            $barLength = (int) round(($percentage / 100) * $maxBarLength);
            $bar = str_repeat('█', max(1, $barLength));
            $empty = str_repeat('░', $maxBarLength - $barLength);

            $this->line(sprintf(
                '  %-12s [%s%s] %5.1f%%',
                $type,
                $bar,
                $empty,
                $percentage
            ));
        }
    }


    /**
     * Display largest files.
     */
    protected function displayLargestFiles(StorageMonitorInterface $storageMonitor, int $limit, ?string $filterType): void
    {
        $this->newLine();
        $this->info("📦 {$limit} File Terbesar");
        $this->line('───────────────────────────────────────────────────────');
        $this->newLine();

        $largestFiles = $storageMonitor->getLargestFiles($limit);

        // Filter by type if specified
        if ($filterType) {
            $largestFiles = $largestFiles->filter(fn($file) => $file['type'] === $filterType);
        }

        if ($largestFiles->isEmpty()) {
            $this->warn('Tidak ada file ditemukan.');
            return;
        }

        $rows = [];
        foreach ($largestFiles as $index => $file) {
            $rows[] = [
                $index + 1,
                $this->truncatePath($file['path'], 50),
                $file['size_formatted'],
                $file['type'],
                $file['modified_at'],
            ];
        }

        $this->table(
            ['#', 'Path', 'Ukuran', 'Tipe', 'Terakhir Diubah'],
            $rows
        );
    }

    /**
     * Display threshold status with recommendations.
     */
    protected function displayThresholdStatus($thresholdResult): void
    {
        $this->newLine();
        $this->info('⚠️  Status Threshold');
        $this->line('───────────────────────────────────────────────────────');
        $this->newLine();

        $this->table(
            ['Parameter', 'Nilai'],
            [
                ['Penggunaan Saat Ini', number_format($thresholdResult->usagePercentage, 2) . '%'],
                ['Batas Peringatan', number_format($thresholdResult->warningThreshold, 2) . '%'],
                ['Batas Kritis', number_format($thresholdResult->criticalThreshold, 2) . '%'],
                ['Status', $thresholdResult->getStatusLabel()],
            ]
        );

        // Show message
        if ($thresholdResult->message) {
            $this->newLine();
            
            if ($thresholdResult->isCritical()) {
                $this->error($thresholdResult->message);
            } elseif ($thresholdResult->isWarning()) {
                $this->warn($thresholdResult->message);
            } else {
                $this->info($thresholdResult->message);
            }
        }

        // Show recommendations
        if (!$thresholdResult->isOk()) {
            $this->newLine();
            $this->info('💡 Rekomendasi:');
            $this->line('  1. Jalankan `php artisan storage:cleanup --dry-run` untuk melihat file yang bisa dihapus');
            $this->line('  2. Jalankan `php artisan storage:cleanup` untuk membersihkan file orphan');
            $this->line('  3. Pertimbangkan untuk menghapus file lama yang tidak diperlukan');
            
            if ($thresholdResult->isCritical()) {
                $this->line('  4. ⚠️  SEGERA lakukan pembersihan untuk menghindari masalah sistem!');
            }
        }

        $this->newLine();
    }

    /**
     * Format bytes to human readable string.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Truncate path for display.
     */
    protected function truncatePath(string $path, int $maxLength): string
    {
        if (strlen($path) <= $maxLength) {
            return $path;
        }

        $start = substr($path, 0, 15);
        $end = substr($path, -($maxLength - 18));

        return $start . '...' . $end;
    }
}
