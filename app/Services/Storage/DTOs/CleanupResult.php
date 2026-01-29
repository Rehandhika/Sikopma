<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk hasil operasi cleanup file.
 */
class CleanupResult
{
    public function __construct(
        public readonly int $filesScanned,
        public readonly int $filesDeleted,
        public readonly int $bytesFreed,
        public readonly array $deletedFiles,
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
            filesDeleted: 0,
            bytesFreed: 0,
            deletedFiles: [],
            errors: [],
            dryRun: $dryRun,
        );
    }

    /**
     * Check if cleanup was successful (no errors).
     */
    public function isSuccessful(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if any files were deleted.
     */
    public function hasDeletedFiles(): bool
    {
        return $this->filesDeleted > 0;
    }

    /**
     * Check if there were errors.
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Get bytes freed in KB.
     */
    public function getBytesFreedKB(): float
    {
        return round($this->bytesFreed / 1024, 2);
    }

    /**
     * Get bytes freed in MB.
     */
    public function getBytesFreedMB(): float
    {
        return round($this->bytesFreed / (1024 * 1024), 2);
    }

    /**
     * Get summary string.
     */
    public function getSummary(): string
    {
        $action = $this->dryRun ? 'would be deleted' : 'deleted';
        $freed = $this->getBytesFreedMB();

        return "Scanned {$this->filesScanned} files, {$this->filesDeleted} {$action}, {$freed} MB freed";
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'files_scanned' => $this->filesScanned,
            'files_deleted' => $this->filesDeleted,
            'bytes_freed' => $this->bytesFreed,
            'bytes_freed_mb' => $this->getBytesFreedMB(),
            'deleted_files' => $this->deletedFiles,
            'errors' => $this->errors,
            'dry_run' => $this->dryRun,
            'successful' => $this->isSuccessful(),
        ];
    }
}
