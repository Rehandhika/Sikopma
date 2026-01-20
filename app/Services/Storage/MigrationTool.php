<?php

namespace App\Services\Storage;

use App\Models\Attendance;
use App\Models\Banner;
use App\Models\LeaveRequest;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use App\Services\Storage\DTOs\MigrationResult;
use App\Services\Storage\Exceptions\FileProcessingException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * MigrationTool - Tool untuk migrasi file existing ke struktur baru.
 * 
 * Bertanggung jawab untuk:
 * - Scan file existing yang perlu dimigrasi
 * - Migrasi file ke struktur direktori baru: {type}/{year}/{month}/{uuid}.{ext}
 * - Update referensi database ke path baru
 * - Generate missing variants untuk file existing
 * - Support backward compatibility selama transisi
 */
class MigrationTool implements MigrationToolInterface
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

    /**
     * Pattern untuk mendeteksi format path baru.
     * Format: {type}/{year}/{month}/{uuid}.{ext}
     */
    protected const NEW_PATH_PATTERN = '/^[a-z]+\/\d{4}\/\d{2}\/[a-f0-9\-]{36}\.[a-z0-9]+$/i';

    public function __construct(
        protected StorageOrganizerInterface $storageOrganizer,
        protected ImageProcessingServiceInterface $imageProcessor,
        protected FileStorageServiceInterface $fileStorage
    ) {}

    /**
     * {@inheritdoc}
     */
    public function scanExistingFiles(?string $type = null): Collection
    {
        $files = collect();
        $types = $type ? [$type] : $this->storageOrganizer->getValidTypes();

        foreach ($types as $fileType) {
            $mapping = $this->typeModelMapping[$fileType] ?? null;
            
            if (!$mapping) {
                continue;
            }

            $modelClass = $mapping['model'];
            $column = $mapping['column'];
            $includeDeleted = $mapping['include_deleted'] ?? false;

            $query = $modelClass::query();
            
            if ($includeDeleted && method_exists($modelClass, 'withTrashed')) {
                $query->withTrashed();
            }

            $records = $query
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->select(['id', $column])
                ->get();

            foreach ($records as $record) {
                $path = $record->{$column};
                
                if (empty($path)) {
                    continue;
                }

                $needsMigration = !$this->isNewPathFormat($path);
                $needsVariants = $this->checkNeedsVariants($path, $fileType);
                $config = config("filestorage.types.{$fileType}");
                $disk = $config['disk'] ?? 'public';
                $size = 0;

                try {
                    if (Storage::disk($disk)->exists($path)) {
                        $size = Storage::disk($disk)->size($path);
                    }
                } catch (\Exception $e) {
                    // Ignore size errors
                }

                $files->push([
                    'path' => $path,
                    'type' => $fileType,
                    'size' => $size,
                    'needs_migration' => $needsMigration,
                    'needs_variants' => $needsVariants,
                    'model_class' => $modelClass,
                    'model_id' => $record->id,
                ]);
            }
        }

        return $files;
    }


    /**
     * {@inheritdoc}
     */
    public function migrateFile(string $oldPath, string $type, bool $preserveOriginal = true): MigrationResult
    {
        $config = config("filestorage.types.{$type}");
        
        if (!$config) {
            return MigrationResult::forSingleFile(
                $oldPath,
                '',
                [],
                false,
                __('filestorage.validation.invalid_file_type', ['type' => $type, 'valid_types' => implode(', ', $this->storageOrganizer->getValidTypes())])
            );
        }

        $disk = $config['disk'] ?? 'public';

        // Check if file exists
        if (!Storage::disk($disk)->exists($oldPath)) {
            return MigrationResult::forSingleFile(
                $oldPath,
                '',
                [],
                false,
                __('filestorage.storage.file_not_found', ['path' => $oldPath])
            );
        }

        // Check if already in new format
        if ($this->isNewPathFormat($oldPath)) {
            // Just generate missing variants
            $variants = $this->generateMissingVariants($oldPath, $type);
            
            return MigrationResult::forSingleFile(
                $oldPath,
                $oldPath,
                $variants,
                false,
                null
            );
        }

        try {
            // Get file extension
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
            
            // Determine if we should convert to WebP
            $convertToWebp = $config['convert_to_webp'] ?? false;
            $isImage = $this->isImageExtension($extension);
            
            if ($convertToWebp && $isImage) {
                $extension = 'webp';
            }

            // Generate new path
            $newPath = $this->storageOrganizer->generatePath($type, $extension);

            // Get full paths
            $oldFullPath = Storage::disk($disk)->path($oldPath);
            $newFullPath = Storage::disk($disk)->path($newPath);

            // Ensure target directory exists
            $newDir = dirname($newFullPath);
            if (!is_dir($newDir)) {
                mkdir($newDir, 0755, true);
            }

            // Process and copy file
            if ($isImage && ($convertToWebp || !empty($config['variants']))) {
                // Process image (resize if needed, convert to WebP if configured)
                $processedImage = $this->imageProcessor->process(
                    $oldFullPath,
                    $type,
                    $newFullPath
                );

                // Update new path if extension changed
                if ($processedImage->path !== $newFullPath) {
                    $newPath = $this->getRelativePath($processedImage->path, $disk);
                }
            } else {
                // Copy file as-is
                copy($oldFullPath, $newFullPath);
            }

            // Generate variants
            $variants = $this->generateMissingVariants($newPath, $type);

            // Update database reference
            $dbUpdated = $this->updateDatabaseReferences($oldPath, $newPath, $type);

            // Delete original if not preserving
            if (!$preserveOriginal) {
                $this->deleteOldFile($oldPath, $type, $disk);
            }

            Log::info('MigrationTool: File migrated', [
                'old_path' => $oldPath,
                'new_path' => $newPath,
                'type' => $type,
                'variants' => array_keys($variants),
                'db_updated' => $dbUpdated,
            ]);

            return MigrationResult::forSingleFile(
                $oldPath,
                $newPath,
                $variants,
                $dbUpdated,
                null
            );
        } catch (\Exception $e) {
            Log::error('MigrationTool: Migration failed', [
                'old_path' => $oldPath,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return MigrationResult::forSingleFile(
                $oldPath,
                '',
                [],
                false,
                $e->getMessage()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function migrateAll(?string $type = null, bool $dryRun = true, int $batchSize = 100): MigrationResult
    {
        $files = $this->scanExistingFiles($type);
        $filesToMigrate = $files->filter(fn($f) => $f['needs_migration'] || $f['needs_variants']);

        $result = MigrationResult::empty($dryRun);
        $processed = 0;

        foreach ($filesToMigrate->chunk($batchSize) as $batch) {
            foreach ($batch as $file) {
                $processed++;

                if ($dryRun) {
                    // Simulate migration
                    $result = $result->merge(new MigrationResult(
                        filesScanned: 1,
                        filesMigrated: $file['needs_migration'] ? 1 : 0,
                        filesSkipped: 0,
                        variantsGenerated: $file['needs_variants'] ? 1 : 0,
                        databaseUpdated: $file['needs_migration'] ? 1 : 0,
                        migratedFiles: [[
                            'old' => $file['path'],
                            'new' => '[would be generated]',
                            'type' => $file['type'],
                        ]],
                        errors: [],
                        dryRun: true,
                    ));
                } else {
                    // Actually migrate
                    $fileResult = $this->migrateFile($file['path'], $file['type'], true);
                    $result = $result->merge($fileResult);
                }

                // Log progress every 100 files
                if ($processed % 100 === 0) {
                    Log::info('MigrationTool: Progress', [
                        'processed' => $processed,
                        'total' => $filesToMigrate->count(),
                        'dry_run' => $dryRun,
                    ]);
                }
            }
        }

        Log::info('MigrationTool: Migration completed', [
            'dry_run' => $dryRun,
            'type' => $type,
            'files_scanned' => $result->filesScanned,
            'files_migrated' => $result->filesMigrated,
            'variants_generated' => $result->variantsGenerated,
            'errors_count' => count($result->errors),
        ]);

        return $result;
    }


    /**
     * {@inheritdoc}
     */
    public function updateDatabaseReferences(string $oldPath, string $newPath, string $type): bool
    {
        $mapping = $this->typeModelMapping[$type] ?? null;
        
        if (!$mapping) {
            return false;
        }

        $modelClass = $mapping['model'];
        $column = $mapping['column'];
        $includeDeleted = $mapping['include_deleted'] ?? false;

        try {
            $query = $modelClass::query();
            
            if ($includeDeleted && method_exists($modelClass, 'withTrashed')) {
                $query->withTrashed();
            }

            $updated = $query
                ->where($column, $oldPath)
                ->update([$column => $newPath]);

            if ($updated > 0) {
                Log::info('MigrationTool: Database references updated', [
                    'old_path' => $oldPath,
                    'new_path' => $newPath,
                    'type' => $type,
                    'records_updated' => $updated,
                ]);
            }

            return $updated > 0;
        } catch (\Exception $e) {
            Log::error('MigrationTool: Failed to update database references', [
                'old_path' => $oldPath,
                'new_path' => $newPath,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateMissingVariants(string $path, string $type): array
    {
        $config = config("filestorage.types.{$type}");
        
        if (!$config || empty($config['variants'])) {
            return [];
        }

        $disk = $config['disk'] ?? 'public';
        
        if (!Storage::disk($disk)->exists($path)) {
            return [];
        }

        // Check if it's an image
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!$this->isImageExtension($extension)) {
            return [];
        }

        $generatedVariants = [];
        $fullPath = Storage::disk($disk)->path($path);

        foreach ($config['variants'] as $variantName => $variantConfig) {
            try {
                $variantPath = $this->storageOrganizer->getVariantPath($path, $variantName);
                
                // Check if variant already exists
                if (Storage::disk($disk)->exists($variantPath)) {
                    continue;
                }

                // Generate variant
                $variants = $this->imageProcessor->generateVariants($fullPath, $type, $fullPath);
                
                if (isset($variants[$variantName])) {
                    $generatedVariants[$variantName] = $this->getRelativePath($variants[$variantName]->path, $disk);
                    
                    Log::info('MigrationTool: Variant generated', [
                        'original' => $path,
                        'variant' => $variantName,
                        'variant_path' => $generatedVariants[$variantName],
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('MigrationTool: Failed to generate variant', [
                    'path' => $path,
                    'variant' => $variantName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $generatedVariants;
    }

    /**
     * {@inheritdoc}
     */
    public function isNewPathFormat(string $path): bool
    {
        // New format: {type}/{year}/{month}/{uuid}.{ext}
        // Example: product/2026/01/550e8400-e29b-41d4-a716-446655440000.webp
        
        // Normalize path
        $path = trim($path, '/\\');
        $path = str_replace('\\', '/', $path);

        // Check against pattern
        if (!preg_match(self::NEW_PATH_PATTERN, $path)) {
            return false;
        }

        // Extract type and validate
        $parts = explode('/', $path);
        if (count($parts) < 4) {
            return false;
        }

        $type = $parts[0];
        $year = $parts[1];
        $month = $parts[2];
        $filename = pathinfo($parts[3], PATHINFO_FILENAME);

        // Validate type
        if (!$this->storageOrganizer->isValidType($type)) {
            return false;
        }

        // Validate year (reasonable range)
        $yearInt = (int) $year;
        if ($yearInt < 2020 || $yearInt > 2100) {
            return false;
        }

        // Validate month
        $monthInt = (int) $month;
        if ($monthInt < 1 || $monthInt > 12) {
            return false;
        }

        // Validate UUID format
        if (!Str::isUuid($filename)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationStats(?string $type = null): array
    {
        $files = $this->scanExistingFiles($type);
        
        $stats = [
            'total_files' => $files->count(),
            'migrated' => $files->filter(fn($f) => !$f['needs_migration'])->count(),
            'pending' => $files->filter(fn($f) => $f['needs_migration'])->count(),
            'missing_variants' => $files->filter(fn($f) => $f['needs_variants'])->count(),
            'by_type' => [],
        ];

        $types = $type ? [$type] : $this->storageOrganizer->getValidTypes();
        
        foreach ($types as $fileType) {
            $typeFiles = $files->filter(fn($f) => $f['type'] === $fileType);
            
            $stats['by_type'][$fileType] = [
                'total' => $typeFiles->count(),
                'migrated' => $typeFiles->filter(fn($f) => !$f['needs_migration'])->count(),
                'pending' => $typeFiles->filter(fn($f) => $f['needs_migration'])->count(),
                'missing_variants' => $typeFiles->filter(fn($f) => $f['needs_variants'])->count(),
            ];
        }

        return $stats;
    }


    /**
     * Check if file needs variants generated.
     */
    protected function checkNeedsVariants(string $path, string $type): bool
    {
        $config = config("filestorage.types.{$type}");
        
        if (!$config || empty($config['variants'])) {
            return false;
        }

        $disk = $config['disk'] ?? 'public';
        
        // Check if it's an image
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!$this->isImageExtension($extension)) {
            return false;
        }

        // Check if any variant is missing
        foreach (array_keys($config['variants']) as $variantName) {
            try {
                $variantPath = $this->storageOrganizer->getVariantPath($path, $variantName);
                
                if (!Storage::disk($disk)->exists($variantPath)) {
                    return true;
                }
            } catch (\Exception $e) {
                // If we can't determine variant path, assume it needs variants
                return true;
            }
        }

        return false;
    }

    /**
     * Check if extension is an image extension.
     */
    protected function isImageExtension(string $extension): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        return in_array(strtolower($extension), $imageExtensions, true);
    }

    /**
     * Get relative path from full path.
     */
    protected function getRelativePath(string $fullPath, string $disk): string
    {
        $diskPath = Storage::disk($disk)->path('');
        $relativePath = str_replace($diskPath, '', $fullPath);
        return ltrim(str_replace('\\', '/', $relativePath), '/');
    }

    /**
     * Delete old file and its variants.
     */
    protected function deleteOldFile(string $path, string $type, string $disk): void
    {
        try {
            // Delete main file
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }

            // Delete variants
            $config = config("filestorage.types.{$type}");
            
            if (!empty($config['variants'])) {
                foreach (array_keys($config['variants']) as $variantName) {
                    try {
                        $variantPath = $this->storageOrganizer->getVariantPath($path, $variantName);
                        
                        if (Storage::disk($disk)->exists($variantPath)) {
                            Storage::disk($disk)->delete($variantPath);
                        }
                    } catch (\Exception $e) {
                        // Ignore variant deletion errors
                    }
                }
            }

            // Delete thumbnails
            $this->deleteOldThumbnails($path, $disk);

            Log::info('MigrationTool: Old file deleted', [
                'path' => $path,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            Log::warning('MigrationTool: Failed to delete old file', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete old thumbnails for a file.
     */
    protected function deleteOldThumbnails(string $path, string $disk): void
    {
        try {
            $directory = dirname($path);
            $filename = pathinfo($path, PATHINFO_FILENAME);
            $thumbnailDir = $directory . '/thumbnails';

            if (!Storage::disk($disk)->exists($thumbnailDir)) {
                return;
            }

            $files = Storage::disk($disk)->files($thumbnailDir);
            
            foreach ($files as $file) {
                if (str_starts_with(basename($file), $filename . '_')) {
                    Storage::disk($disk)->delete($file);
                }
            }
        } catch (\Exception $e) {
            // Ignore thumbnail deletion errors
        }
    }
}
