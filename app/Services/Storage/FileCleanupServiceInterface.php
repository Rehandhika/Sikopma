<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\CleanupResult;
use Illuminate\Support\Collection;

/**
 * Interface untuk FileCleanupService.
 *
 * Bertanggung jawab untuk:
 * - Identifikasi file orphan (tidak ada di database)
 * - Pembersihan file orphan dengan dry-run mode
 * - Pembersihan file temporary
 * - Logging untuk audit
 */
interface FileCleanupServiceInterface
{
    /**
     * Find orphan files yang tidak ada di database.
     *
     * @param  string|null  $type  Filter berdasarkan tipe file (null = semua tipe)
     * @return Collection<int, array{path: string, type: string, size: int, modified_at: \DateTimeInterface}>
     */
    public function findOrphanFiles(?string $type = null): Collection;

    /**
     * Clean orphan files.
     *
     * @param  bool  $dryRun  Jika true, hanya simulasi tanpa menghapus
     * @param  string|null  $type  Filter berdasarkan tipe file (null = semua tipe)
     */
    public function cleanOrphanFiles(bool $dryRun = true, ?string $type = null): CleanupResult;

    /**
     * Clean temporary files yang lebih tua dari threshold.
     *
     * @param  int  $hoursOld  Hapus file lebih tua dari jam ini (default dari config)
     * @param  bool  $dryRun  Jika true, hanya simulasi tanpa menghapus
     */
    public function cleanTempFiles(?int $hoursOld = null, bool $dryRun = true): CleanupResult;

    /**
     * Clean all unused files (orphan + temp).
     *
     * @param  bool  $dryRun  Jika true, hanya simulasi tanpa menghapus
     * @param  string|null  $type  Filter berdasarkan tipe file (null = semua tipe)
     */
    public function cleanAll(bool $dryRun = true, ?string $type = null): CleanupResult;

    /**
     * Get all file paths referenced in database for a specific type.
     *
     * @param  string  $type  File type
     * @return Collection<int, string>
     */
    public function getDatabaseReferences(string $type): Collection;

    /**
     * Get all files in storage for a specific type.
     *
     * @param  string  $type  File type
     * @return Collection<int, array{path: string, size: int, modified_at: \DateTimeInterface}>
     */
    public function getStorageFiles(string $type): Collection;
}
