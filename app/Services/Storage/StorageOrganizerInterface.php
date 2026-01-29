<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\PathInfo;

/**
 * Interface untuk StorageOrganizer.
 * Mengatur struktur direktori dan path file.
 */
interface StorageOrganizerInterface
{
    /**
     * Generate path untuk file baru.
     * Format: {type}/{year}/{month}/{uuid}.{ext}
     *
     * @param  string  $type  Tipe file (product, banner, attendance, profile, leave, report)
     * @param  string  $extension  Extension file (webp, jpg, png, pdf, etc.)
     * @param  array  $options  Opsi tambahan ['day' => null] untuk attendance
     * @return string Path yang di-generate
     */
    public function generatePath(string $type, string $extension, array $options = []): string;

    /**
     * Get variant path dari original path.
     *
     * @param  string  $originalPath  Path file original
     * @param  string  $size  Nama variant (thumbnail, medium, large, etc.)
     * @return string Path untuk variant
     */
    public function getVariantPath(string $originalPath, string $size): string;

    /**
     * Get thumbnail path dari original path.
     *
     * @param  string  $originalPath  Path file original
     * @param  int  $width  Lebar thumbnail
     * @param  int  $height  Tinggi thumbnail
     * @return string Path untuk thumbnail
     */
    public function getThumbnailPath(string $originalPath, int $width, int $height): string;

    /**
     * Parse path untuk extract metadata.
     *
     * @param  string  $path  Path file
     * @return PathInfo Informasi path yang di-parse
     */
    public function parsePath(string $path): PathInfo;

    /**
     * Sanitize filename untuk mencegah path traversal attacks.
     *
     * @param  string  $filename  Filename yang akan di-sanitize
     * @return string Filename yang sudah di-sanitize
     */
    public function sanitizeFilename(string $filename): string;

    /**
     * Validate apakah type file valid.
     *
     * @param  string  $type  Tipe file
     * @return bool True jika valid
     */
    public function isValidType(string $type): bool;

    /**
     * Get semua valid types.
     *
     * @return array List of valid types
     */
    public function getValidTypes(): array;
}
