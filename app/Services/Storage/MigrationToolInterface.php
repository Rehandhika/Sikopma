<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\MigrationResult;
use Illuminate\Support\Collection;

/**
 * Interface untuk MigrationTool.
 * 
 * Bertanggung jawab untuk:
 * - Scan file existing yang perlu dimigrasi
 * - Migrasi file ke struktur direktori baru
 * - Update referensi database ke path baru
 * - Generate missing variants untuk file existing
 * - Support backward compatibility selama transisi
 */
interface MigrationToolInterface
{
    /**
     * Scan existing files yang perlu dimigrasi ke struktur baru.
     *
     * @param string|null $type Filter berdasarkan tipe file (null = semua tipe)
     * @return Collection<int, array{
     *     path: string,
     *     type: string,
     *     size: int,
     *     needs_migration: bool,
     *     needs_variants: bool,
     *     model_class: string|null,
     *     model_id: int|null
     * }>
     */
    public function scanExistingFiles(?string $type = null): Collection;

    /**
     * Migrasi single file ke struktur baru.
     *
     * @param string $oldPath Path file lama
     * @param string $type Tipe file
     * @param bool $preserveOriginal Jika true, file original tidak dihapus
     * @return MigrationResult
     */
    public function migrateFile(string $oldPath, string $type, bool $preserveOriginal = true): MigrationResult;

    /**
     * Migrasi semua file untuk tipe tertentu atau semua tipe.
     *
     * @param string|null $type Filter berdasarkan tipe file (null = semua tipe)
     * @param bool $dryRun Jika true, hanya simulasi tanpa migrasi
     * @param int $batchSize Jumlah file per batch
     * @return MigrationResult
     */
    public function migrateAll(?string $type = null, bool $dryRun = true, int $batchSize = 100): MigrationResult;

    /**
     * Update referensi database dari path lama ke path baru.
     *
     * @param string $oldPath Path file lama
     * @param string $newPath Path file baru
     * @param string $type Tipe file
     * @return bool True jika berhasil update
     */
    public function updateDatabaseReferences(string $oldPath, string $newPath, string $type): bool;

    /**
     * Generate missing variants untuk file existing.
     *
     * @param string $path Path file
     * @param string $type Tipe file
     * @return array<string, string> Array variant name => variant path
     */
    public function generateMissingVariants(string $path, string $type): array;

    /**
     * Check apakah file sudah dalam format path baru.
     *
     * @param string $path Path file
     * @return bool True jika sudah format baru
     */
    public function isNewPathFormat(string $path): bool;

    /**
     * Get statistics migrasi.
     *
     * @param string|null $type Filter berdasarkan tipe file
     * @return array{
     *     total_files: int,
     *     migrated: int,
     *     pending: int,
     *     missing_variants: int,
     *     by_type: array<string, array{total: int, migrated: int, pending: int}>
     * }
     */
    public function getMigrationStats(?string $type = null): array;
}
