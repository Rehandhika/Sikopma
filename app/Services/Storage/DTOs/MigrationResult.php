<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk hasil operasi migrasi file.
 */
class MigrationResult
{
    public function __construct(
        public readonly int $filesScanned,
        public readonly int $filesMigrated,
        public readonly int $filesSkipped,
        public readonly int $variantsGenerated,
        public readonly int $databaseUpdated,
        public readonly array $migratedFiles,
        public readonly array $errors,
        public readonly bool $dryRun,
    ) {}

    /**
     * Create empty result.
     */
    public static function empty(bool $dryRun = false): self
    {
        return new self(
            filesScanned: 0,
            filesMigrated: 0,
            filesSkipped: 0,
            variantsGenerated: 0,
            databaseUpdated: 0,
            migratedFiles: [],
            errors: [],
            dryRun: $dryRun,
        );
    }

    /**
     * Create result for single file migration.
     */
    public static function forSingleFile(
        string $oldPath,
        string $newPath,
        array $variants = [],
        bool $databaseUpdated = false,
        ?string $error = null
    ): self {
        return new self(
            filesScanned: 1,
            filesMigrated: $error === null ? 1 : 0,
            filesSkipped: $error !== null ? 1 : 0,
            variantsGenerated: count($variants),
            databaseUpdated: $databaseUpdated ? 1 : 0,
            migratedFiles: $error === null ? [['old' => $oldPath, 'new' => $newPath, 'variants' => $variants]] : [],
            errors: $error !== null ? [['path' => $oldPath, 'error' => $error]] : [],
            dryRun: false,
        );
    }

    /**
     * Check if migration was successful (no errors).
     */
    public function isSuccessful(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if any files were migrated.
     */
    public function hasMigratedFiles(): bool
    {
        return $this->filesMigrated > 0;
    }

    /**
     * Check if there were errors.
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get success rate as percentage.
     */
    public function getSuccessRate(): float
    {
        if ($this->filesScanned === 0) {
            return 100.0;
        }
        
        return round(($this->filesMigrated / $this->filesScanned) * 100, 2);
    }

    /**
     * Get summary string.
     */
    public function getSummary(): string
    {
        $action = $this->dryRun ? 'would be migrated' : 'migrated';
        
        return sprintf(
            "Scanned %d files, %d %s, %d skipped, %d variants generated, %d DB records updated",
            $this->filesScanned,
            $this->filesMigrated,
            $action,
            $this->filesSkipped,
            $this->variantsGenerated,
            $this->databaseUpdated
        );
    }

    /**
     * Merge with another result.
     */
    public function merge(MigrationResult $other): self
    {
        return new self(
            filesScanned: $this->filesScanned + $other->filesScanned,
            filesMigrated: $this->filesMigrated + $other->filesMigrated,
            filesSkipped: $this->filesSkipped + $other->filesSkipped,
            variantsGenerated: $this->variantsGenerated + $other->variantsGenerated,
            databaseUpdated: $this->databaseUpdated + $other->databaseUpdated,
            migratedFiles: array_merge($this->migratedFiles, $other->migratedFiles),
            errors: array_merge($this->errors, $other->errors),
            dryRun: $this->dryRun || $other->dryRun,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'files_scanned' => $this->filesScanned,
            'files_migrated' => $this->filesMigrated,
            'files_skipped' => $this->filesSkipped,
            'variants_generated' => $this->variantsGenerated,
            'database_updated' => $this->databaseUpdated,
            'migrated_files' => $this->migratedFiles,
            'errors' => $this->errors,
            'dry_run' => $this->dryRun,
            'successful' => $this->isSuccessful(),
            'success_rate' => $this->getSuccessRate(),
        ];
    }
}
