<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\StorageStats;
use App\Services\Storage\DTOs\ThresholdResult;
use Illuminate\Support\Collection;

/**
 * Interface untuk StorageMonitor.
 *
 * Bertanggung jawab untuk:
 * - Monitoring total storage usage
 * - Breakdown usage per file type
 * - Identifikasi file terbesar
 * - Warning/critical threshold alerts
 */
interface StorageMonitorInterface
{
    /**
     * Get storage statistics.
     */
    public function getStatistics(): StorageStats;

    /**
     * Get usage breakdown per file type.
     *
     * @return array<string, array{bytes: int, files: int, percentage: float}>
     */
    public function getUsageByType(): array;

    /**
     * Get largest files in storage.
     *
     * @param  int  $limit  Maximum number of files to return
     * @return Collection<int, array{path: string, size: int, type: string, modified_at: string}>
     */
    public function getLargestFiles(int $limit = 10): Collection;

    /**
     * Check if storage exceeds configured thresholds.
     */
    public function checkThreshold(): ThresholdResult;
}
