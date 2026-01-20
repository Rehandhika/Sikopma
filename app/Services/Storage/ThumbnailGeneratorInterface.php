<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\BatchResult;
use App\Services\Storage\DTOs\ProcessedImage;

/**
 * Interface untuk ThumbnailGenerator.
 * Menangani pembuatan thumbnail on-demand dan batch processing.
 */
interface ThumbnailGeneratorInterface
{
    /**
     * Get thumbnail URL (generate on-demand jika belum ada).
     *
     * @param string $originalPath Path file original
     * @param int $width Lebar thumbnail
     * @param int $height Tinggi thumbnail
     * @param string|null $disk Storage disk (null = auto-detect dari path)
     * @return string URL thumbnail
     */
    public function getThumbnailUrl(string $originalPath, int $width, int $height, ?string $disk = null): string;

    /**
     * Generate thumbnail secara eksplisit.
     *
     * @param string $originalPath Path file original
     * @param int $width Lebar thumbnail
     * @param int $height Tinggi thumbnail
     * @param string|null $disk Storage disk (null = auto-detect dari path)
     * @return ProcessedImage Hasil thumbnail yang di-generate
     */
    public function generate(string $originalPath, int $width, int $height, ?string $disk = null): ProcessedImage;

    /**
     * Batch generate thumbnails.
     *
     * @param array $paths Array of original paths
     * @param int $width Lebar thumbnail
     * @param int $height Tinggi thumbnail
     * @param string|null $disk Storage disk (null = auto-detect dari path)
     * @return BatchResult Hasil batch processing
     */
    public function generateBatch(array $paths, int $width, int $height, ?string $disk = null): BatchResult;

    /**
     * Check apakah thumbnail sudah ada.
     *
     * @param string $originalPath Path file original
     * @param int $width Lebar thumbnail
     * @param int $height Tinggi thumbnail
     * @param string|null $disk Storage disk
     * @return bool True jika thumbnail exists
     */
    public function exists(string $originalPath, int $width, int $height, ?string $disk = null): bool;

    /**
     * Delete thumbnail.
     *
     * @param string $originalPath Path file original
     * @param int $width Lebar thumbnail
     * @param int $height Tinggi thumbnail
     * @param string|null $disk Storage disk
     * @return bool True jika berhasil dihapus
     */
    public function delete(string $originalPath, int $width, int $height, ?string $disk = null): bool;

    /**
     * Delete semua thumbnails untuk file original.
     *
     * @param string $originalPath Path file original
     * @param string|null $disk Storage disk
     * @return int Jumlah thumbnails yang dihapus
     */
    public function deleteAll(string $originalPath, ?string $disk = null): int;

    /**
     * Get thumbnail path dari original path.
     *
     * @param string $originalPath Path file original
     * @param int $width Lebar thumbnail
     * @param int $height Tinggi thumbnail
     * @return string Path thumbnail
     */
    public function getThumbnailPath(string $originalPath, int $width, int $height): string;
}
