<?php

namespace App\Services\Storage;

/**
 * Interface untuk CacheManager.
 * Mengelola caching URL file untuk performa optimal.
 */
interface CacheManagerInterface
{
    /**
     * Get cached URL atau generate baru menggunakan callback.
     *
     * @param  string  $path  Path file
     * @param  string  $size  Ukuran variant (original, thumbnail, medium, large, etc.)
     * @param  callable  $generator  Callback untuk generate URL jika tidak ada di cache
     * @return string URL file
     */
    public function getUrl(string $path, string $size, callable $generator): string;

    /**
     * Invalidate cache untuk path tertentu.
     * Menghapus semua cache terkait path tersebut (semua size variants).
     *
     * @param  string  $path  Path file
     */
    public function invalidate(string $path): void;

    /**
     * Invalidate semua cache untuk type tertentu.
     * Menghapus semua cache file dalam type tersebut.
     *
     * @param  string  $type  Tipe file (product, banner, attendance, profile, leave, report)
     */
    public function invalidateType(string $type): void;

    /**
     * Invalidate semua cache file storage.
     */
    public function invalidateAll(): void;

    /**
     * Check apakah URL ada di cache.
     *
     * @param  string  $path  Path file
     * @param  string  $size  Ukuran variant
     * @return bool True jika ada di cache
     */
    public function has(string $path, string $size): bool;

    /**
     * Get cache key untuk path dan size tertentu.
     *
     * @param  string  $path  Path file
     * @param  string  $size  Ukuran variant
     * @return string Cache key
     */
    public function getCacheKey(string $path, string $size): string;

    /**
     * Get cache statistics.
     *
     * @return array Statistics array dengan keys: hits, misses, size
     */
    public function getStatistics(): array;
}
