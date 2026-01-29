<?php

namespace App\Services\Storage;

use App\Models\Attendance;
use App\Models\Banner;
use App\Models\LeaveRequest;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use App\Services\Storage\DTOs\CleanupResult;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * FileCleanupService - Service untuk pembersihan file orphan dan temporary.
 *
 * Bertanggung jawab untuk:
 * - Identifikasi file orphan (tidak ada di database)
 * - Pembersihan file orphan dengan dry-run mode
 * - Pembersihan file temporary
 * - Logging untuk audit
 */
class FileCleanupService implements FileCleanupServiceInterface
{
    /**
     * Mapping tipe file ke model dan kolom yang menyimpan path.
     */
    protected array $typeModelMapping = [
        'product' => [
            'model' => Product::class,
            'column' => 'image',
            'include_deleted' => true,
        ],
        'banner' => [
            'model' => Banner::class,
            'column' => 'image_path',
            'include_deleted' => false,
        ],
        'attendance' => [
            'model' => Attendance::class,
            'column' => 'check_in_photo',
            'include_deleted' => false,
        ],
        'profile' => [
            'model' => User::class,
            'column' => 'photo',
            'include_deleted' => true,
        ],
        'leave' => [
            'model' => LeaveRequest::class,
            'column' => 'attachment',
            'include_deleted' => false,
        ],
        'report' => [
            'model' => Report::class,
            'column' => 'file_path',
            'include_deleted' => false,
        ],
    ];

    public function __construct(
        protected StorageOrganizerInterface $storageOrganizer
    ) {}

    /**
     * {@inheritdoc}
     */
    public function findOrphanFiles(?string $type = null): Collection
    {
        $orphanFiles = collect();
        $types = $type ? [$type] : $this->storageOrganizer->getValidTypes();

        foreach ($types as $fileType) {
            $storageFiles = $this->getStorageFiles($fileType);
            $databaseRefs = $this->getDatabaseReferences($fileType);

            // Normalize database references for comparison
            $normalizedRefs = $databaseRefs->map(fn ($path) => $this->normalizePath($path))->filter()->toArray();

            foreach ($storageFiles as $file) {
                $normalizedPath = $this->normalizePath($file['path']);

                // Check if file is referenced in database
                if (! in_array($normalizedPath, $normalizedRefs, true)) {
                    // Check grace period
                    $gracePeriodDays = config('filestorage.cleanup.orphan_grace_period', 7);
                    $graceDate = Carbon::now()->subDays($gracePeriodDays);

                    if ($file['modified_at'] < $graceDate) {
                        $orphanFiles->push([
                            'path' => $file['path'],
                            'type' => $fileType,
                            'size' => $file['size'],
                            'modified_at' => $file['modified_at'],
                        ]);
                    }
                }
            }
        }

        return $orphanFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanOrphanFiles(bool $dryRun = true, ?string $type = null): CleanupResult
    {
        $orphanFiles = $this->findOrphanFiles($type);

        $filesScanned = $orphanFiles->count();
        $filesDeleted = 0;
        $bytesFreed = 0;
        $deletedFiles = [];
        $errors = [];

        foreach ($orphanFiles as $file) {
            try {
                $config = config("filestorage.types.{$file['type']}");
                $disk = $config['disk'] ?? 'public';

                if (! $dryRun) {
                    // Delete the file
                    if (Storage::disk($disk)->exists($file['path'])) {
                        Storage::disk($disk)->delete($file['path']);

                        // Also delete variants if they exist
                        $this->deleteVariants($file['path'], $file['type'], $disk);
                    }

                    // Log deletion for audit
                    Log::info('FileCleanupService: Orphan file deleted', [
                        'path' => $file['path'],
                        'type' => $file['type'],
                        'size' => $file['size'],
                    ]);
                }

                $filesDeleted++;
                $bytesFreed += $file['size'];
                $deletedFiles[] = $file['path'];
            } catch (\Exception $e) {
                $errors[] = [
                    'path' => $file['path'],
                    'error' => $e->getMessage(),
                ];

                Log::error('FileCleanupService: Failed to delete orphan file', [
                    'path' => $file['path'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $result = new CleanupResult(
            filesScanned: $filesScanned,
            filesDeleted: $filesDeleted,
            bytesFreed: $bytesFreed,
            deletedFiles: $deletedFiles,
            errors: $errors,
            dryRun: $dryRun
        );

        // Log summary
        Log::info('FileCleanupService: Orphan cleanup completed', [
            'dry_run' => $dryRun,
            'type' => $type,
            'files_scanned' => $filesScanned,
            'files_deleted' => $filesDeleted,
            'bytes_freed' => $bytesFreed,
            'errors_count' => count($errors),
        ]);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanTempFiles(?int $hoursOld = null, bool $dryRun = true): CleanupResult
    {
        $hoursOld = $hoursOld ?? config('filestorage.cleanup.temp_max_age', 24);
        $threshold = Carbon::now()->subHours($hoursOld);

        $filesScanned = 0;
        $filesDeleted = 0;
        $bytesFreed = 0;
        $deletedFiles = [];
        $errors = [];

        // Clean Livewire temp files
        $tempPaths = [
            'livewire-tmp',
            'temp',
        ];

        foreach ($tempPaths as $tempPath) {
            try {
                // Check both public and local disks
                foreach (['public', 'local'] as $disk) {
                    if (! Storage::disk($disk)->exists($tempPath)) {
                        continue;
                    }

                    $files = Storage::disk($disk)->files($tempPath);

                    foreach ($files as $file) {
                        $filesScanned++;

                        try {
                            $lastModified = Carbon::createFromTimestamp(
                                Storage::disk($disk)->lastModified($file)
                            );

                            if ($lastModified < $threshold) {
                                $size = Storage::disk($disk)->size($file);

                                if (! $dryRun) {
                                    Storage::disk($disk)->delete($file);

                                    Log::info('FileCleanupService: Temp file deleted', [
                                        'path' => $file,
                                        'disk' => $disk,
                                        'age_hours' => $lastModified->diffInHours(Carbon::now()),
                                    ]);
                                }

                                $filesDeleted++;
                                $bytesFreed += $size;
                                $deletedFiles[] = "{$disk}:{$file}";
                            }
                        } catch (\Exception $e) {
                            $errors[] = [
                                'path' => $file,
                                'error' => $e->getMessage(),
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'path' => $tempPath,
                    'error' => $e->getMessage(),
                ];

                Log::error('FileCleanupService: Failed to clean temp directory', [
                    'path' => $tempPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $result = new CleanupResult(
            filesScanned: $filesScanned,
            filesDeleted: $filesDeleted,
            bytesFreed: $bytesFreed,
            deletedFiles: $deletedFiles,
            errors: $errors,
            dryRun: $dryRun
        );

        Log::info('FileCleanupService: Temp cleanup completed', [
            'dry_run' => $dryRun,
            'hours_old' => $hoursOld,
            'files_scanned' => $filesScanned,
            'files_deleted' => $filesDeleted,
            'bytes_freed' => $bytesFreed,
        ]);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanAll(bool $dryRun = true, ?string $type = null): CleanupResult
    {
        // Clean orphan files
        $orphanResult = $this->cleanOrphanFiles($dryRun, $type);

        // Clean temp files
        $tempResult = $this->cleanTempFiles(null, $dryRun);

        // Combine results
        return new CleanupResult(
            filesScanned: $orphanResult->filesScanned + $tempResult->filesScanned,
            filesDeleted: $orphanResult->filesDeleted + $tempResult->filesDeleted,
            bytesFreed: $orphanResult->bytesFreed + $tempResult->bytesFreed,
            deletedFiles: array_merge($orphanResult->deletedFiles, $tempResult->deletedFiles),
            errors: array_merge($orphanResult->errors, $tempResult->errors),
            dryRun: $dryRun
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabaseReferences(string $type): Collection
    {
        $mapping = $this->typeModelMapping[$type] ?? null;

        if (! $mapping) {
            return collect();
        }

        $modelClass = $mapping['model'];
        $column = $mapping['column'];
        $includeDeleted = $mapping['include_deleted'] ?? false;

        $query = $modelClass::query();

        // Include soft deleted records if applicable
        if ($includeDeleted && method_exists($modelClass, 'withTrashed')) {
            $query->withTrashed();
        }

        return $query
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->pluck($column);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageFiles(string $type): Collection
    {
        $config = config("filestorage.types.{$type}");

        if (! $config) {
            return collect();
        }

        $disk = $config['disk'] ?? 'public';
        $basePath = $type; // Files are stored under {type}/{year}/{month}/

        $files = collect();

        try {
            if (! Storage::disk($disk)->exists($basePath)) {
                return $files;
            }

            // Get all files recursively
            $allFiles = Storage::disk($disk)->allFiles($basePath);

            foreach ($allFiles as $filePath) {
                // Skip variant directories (thumbnail, medium, large, etc.)
                if ($this->isVariantFile($filePath)) {
                    continue;
                }

                try {
                    $size = Storage::disk($disk)->size($filePath);
                    $lastModified = Carbon::createFromTimestamp(
                        Storage::disk($disk)->lastModified($filePath)
                    );

                    $files->push([
                        'path' => $filePath,
                        'size' => $size,
                        'modified_at' => $lastModified,
                    ]);
                } catch (\Exception $e) {
                    // Skip files that can't be read
                    Log::warning('FileCleanupService: Cannot read file info', [
                        'path' => $filePath,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('FileCleanupService: Failed to list storage files', [
                'type' => $type,
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);
        }

        return $files;
    }

    /**
     * Check if a file path is a variant (thumbnail, medium, etc.).
     */
    protected function isVariantFile(string $path): bool
    {
        $variantDirs = ['thumbnail', 'thumbnails', 'medium', 'large', 'small', 'desktop', 'tablet', 'mobile'];

        foreach ($variantDirs as $dir) {
            if (str_contains($path, "/{$dir}/")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize path for comparison.
     */
    protected function normalizePath(string $path): string
    {
        // Remove leading/trailing slashes
        $path = trim($path, '/\\');

        // Normalize directory separators
        $path = str_replace('\\', '/', $path);

        return $path;
    }

    /**
     * Delete variants for a file.
     */
    protected function deleteVariants(string $originalPath, string $type, string $disk): void
    {
        $config = config("filestorage.types.{$type}");

        if (empty($config['variants'])) {
            return;
        }

        foreach (array_keys($config['variants']) as $variantName) {
            try {
                $variantPath = $this->storageOrganizer->getVariantPath($originalPath, $variantName);

                if (Storage::disk($disk)->exists($variantPath)) {
                    Storage::disk($disk)->delete($variantPath);
                }
            } catch (\Exception $e) {
                Log::warning('FileCleanupService: Failed to delete variant', [
                    'original_path' => $originalPath,
                    'variant' => $variantName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Also delete thumbnails directory if exists
        try {
            $pathInfo = $this->storageOrganizer->parsePath($originalPath);
            $thumbnailDir = dirname($originalPath).'/thumbnails';

            if (Storage::disk($disk)->exists($thumbnailDir)) {
                // Delete all thumbnails for this file
                $thumbnailPattern = $pathInfo->filename.'_';
                $thumbnailFiles = Storage::disk($disk)->files($thumbnailDir);

                foreach ($thumbnailFiles as $thumbFile) {
                    if (str_contains(basename($thumbFile), $thumbnailPattern)) {
                        Storage::disk($disk)->delete($thumbFile);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore thumbnail deletion errors
        }
    }
}
