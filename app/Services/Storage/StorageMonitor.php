<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\StorageStats;
use App\Services\Storage\DTOs\ThresholdResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * StorageMonitor - Service untuk monitoring penggunaan storage.
 * 
 * Bertanggung jawab untuk:
 * - Tracking total storage per file type
 * - Counting files per category
 * - Threshold warning/critical alerts
 * - Identifying largest files
 */
class StorageMonitor implements StorageMonitorInterface
{
    /**
     * Cache for statistics to avoid repeated filesystem scans.
     */
    protected ?StorageStats $cachedStats = null;

    /**
     * Cache timestamp.
     */
    protected ?int $cacheTimestamp = null;

    /**
     * Cache TTL in seconds.
     */
    protected int $cacheTtl = 300; // 5 minutes

    /**
     * {@inheritdoc}
     */
    public function getStatistics(): StorageStats
    {
        // Return cached stats if still valid
        if ($this->isCacheValid()) {
            return $this->cachedStats;
        }

        $byType = $this->getUsageByType();
        
        // Calculate totals
        $totalBytes = 0;
        $totalFiles = 0;
        
        foreach ($byType as $typeStats) {
            $totalBytes += $typeStats['bytes'];
            $totalFiles += $typeStats['files'];
        }

        // Get available disk space
        $availableBytes = $this->getAvailableDiskSpace();
        $totalDiskSpace = $this->getTotalDiskSpace();
        
        // Calculate usage percentage
        $usagePercentage = $totalDiskSpace > 0 
            ? round(($totalBytes / $totalDiskSpace) * 100, 2) 
            : 0;

        $stats = new StorageStats(
            totalBytes: $totalBytes,
            totalFiles: $totalFiles,
            byType: $byType,
            availableBytes: $availableBytes,
            usagePercentage: $usagePercentage,
            generatedAt: new \DateTimeImmutable()
        );

        // Cache the result
        $this->cachedStats = $stats;
        $this->cacheTimestamp = time();

        return $stats;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsageByType(): array
    {
        $types = config('filestorage.types', []);
        $result = [];
        $totalBytes = 0;

        foreach ($types as $type => $config) {
            $disk = $config['disk'] ?? 'public';
            $basePath = $config['base_path'] ?? $type;

            $typeStats = $this->scanDirectory($disk, $basePath);
            $result[$type] = [
                'bytes' => $typeStats['bytes'],
                'files' => $typeStats['files'],
                'percentage' => 0, // Will be calculated after total is known
                'disk' => $disk,
                'base_path' => $basePath,
            ];

            $totalBytes += $typeStats['bytes'];
        }

        // Calculate percentages
        foreach ($result as $type => &$stats) {
            $stats['percentage'] = $totalBytes > 0 
                ? round(($stats['bytes'] / $totalBytes) * 100, 2) 
                : 0;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getLargestFiles(int $limit = 10): Collection
    {
        $types = config('filestorage.types', []);
        $allFiles = collect();

        foreach ($types as $type => $config) {
            $disk = $config['disk'] ?? 'public';
            $basePath = $config['base_path'] ?? $type;

            $files = $this->getFilesWithSize($disk, $basePath, $type);
            $allFiles = $allFiles->merge($files);
        }

        return $allFiles
            ->sortByDesc('size')
            ->take($limit)
            ->values();
    }

    /**
     * {@inheritdoc}
     */
    public function checkThreshold(): ThresholdResult
    {
        $stats = $this->getStatistics();
        $warningThreshold = config('filestorage.monitoring.threshold_warning', 80);
        $criticalThreshold = config('filestorage.monitoring.threshold_critical', 95);

        $usagePercentage = $stats->usagePercentage;

        if ($usagePercentage >= $criticalThreshold) {
            Log::critical('StorageMonitor: Storage usage critical', [
                'usage_percentage' => $usagePercentage,
                'threshold' => $criticalThreshold,
            ]);

            return ThresholdResult::critical($usagePercentage, $warningThreshold, $criticalThreshold);
        }

        if ($usagePercentage >= $warningThreshold) {
            Log::warning('StorageMonitor: Storage usage warning', [
                'usage_percentage' => $usagePercentage,
                'threshold' => $warningThreshold,
            ]);

            return ThresholdResult::warning($usagePercentage, $warningThreshold, $criticalThreshold);
        }

        return ThresholdResult::ok($usagePercentage, $warningThreshold, $criticalThreshold);
    }


    /**
     * Scan directory and calculate total size and file count.
     *
     * @param string $disk Storage disk name
     * @param string $path Directory path
     * @return array{bytes: int, files: int}
     */
    protected function scanDirectory(string $disk, string $path): array
    {
        $totalBytes = 0;
        $totalFiles = 0;

        try {
            $storage = Storage::disk($disk);
            
            if (!$storage->exists($path)) {
                return ['bytes' => 0, 'files' => 0];
            }

            $files = $storage->allFiles($path);

            foreach ($files as $file) {
                try {
                    $size = $storage->size($file);
                    $totalBytes += $size;
                    $totalFiles++;
                } catch (\Exception $e) {
                    // Skip files that can't be read
                    Log::debug('StorageMonitor: Could not read file size', [
                        'file' => $file,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('StorageMonitor: Failed to scan directory', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'bytes' => $totalBytes,
            'files' => $totalFiles,
        ];
    }

    /**
     * Get files with their sizes from a directory.
     *
     * @param string $disk Storage disk name
     * @param string $path Directory path
     * @param string $type File type
     * @return Collection<int, array{path: string, size: int, type: string, modified_at: string}>
     */
    protected function getFilesWithSize(string $disk, string $path, string $type): Collection
    {
        $result = collect();

        try {
            $storage = Storage::disk($disk);
            
            if (!$storage->exists($path)) {
                return $result;
            }

            $files = $storage->allFiles($path);

            foreach ($files as $file) {
                try {
                    $size = $storage->size($file);
                    $lastModified = $storage->lastModified($file);

                    $result->push([
                        'path' => $file,
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'type' => $type,
                        'disk' => $disk,
                        'modified_at' => date('Y-m-d H:i:s', $lastModified),
                    ]);
                } catch (\Exception $e) {
                    // Skip files that can't be read
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error('StorageMonitor: Failed to get files with size', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Get available disk space for the storage path.
     *
     * @return int Available bytes
     */
    protected function getAvailableDiskSpace(): int
    {
        try {
            // Use public disk path as reference
            $path = Storage::disk('public')->path('');
            $available = disk_free_space($path);
            
            return $available !== false ? (int) $available : 0;
        } catch (\Exception $e) {
            Log::error('StorageMonitor: Failed to get available disk space', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Get total disk space for the storage path.
     *
     * @return int Total bytes
     */
    protected function getTotalDiskSpace(): int
    {
        try {
            // Use public disk path as reference
            $path = Storage::disk('public')->path('');
            $total = disk_total_space($path);
            
            return $total !== false ? (int) $total : 0;
        } catch (\Exception $e) {
            Log::error('StorageMonitor: Failed to get total disk space', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Check if cached statistics are still valid.
     *
     * @return bool
     */
    protected function isCacheValid(): bool
    {
        if ($this->cachedStats === null || $this->cacheTimestamp === null) {
            return false;
        }

        return (time() - $this->cacheTimestamp) < $this->cacheTtl;
    }

    /**
     * Clear cached statistics.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->cachedStats = null;
        $this->cacheTimestamp = null;
    }

    /**
     * Format bytes to human readable string.
     *
     * @param int $bytes
     * @param int $precision
     * @return string
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
     * Get storage growth data for trending.
     * Note: This requires historical data storage which is not implemented yet.
     *
     * @param int $days Number of days to look back
     * @return array
     */
    public function getGrowthTrend(int $days = 30): array
    {
        // TODO: Implement historical data storage for growth trends
        // For now, return current snapshot only
        $stats = $this->getStatistics();

        return [
            'current' => [
                'date' => $stats->generatedAt->format('Y-m-d'),
                'total_bytes' => $stats->totalBytes,
                'total_files' => $stats->totalFiles,
            ],
            'trend' => [],
            'message' => 'Historical data not yet implemented.',
        ];
    }
}
