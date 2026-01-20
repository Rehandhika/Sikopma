<?php

namespace App\Services\Storage;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CacheManager - Mengelola caching URL file.
 * 
 * Bertanggung jawab untuk:
 * - Cache URL file untuk mengurangi filesystem checks
 * - Invalidate cache saat file diupdate atau dihapus
 * - Menyediakan cache key format yang konsisten: file_url_{type}_{size}_{hash}
 * - Track cache statistics untuk monitoring
 */
class CacheManager implements CacheManagerInterface
{
    /**
     * Cache prefix dari konfigurasi.
     */
    protected string $prefix;

    /**
     * Cache TTL dalam detik.
     */
    protected int $ttl;

    /**
     * StorageOrganizer untuk parsing path.
     */
    protected StorageOrganizerInterface $organizer;

    /**
     * Cache statistics.
     */
    protected array $statistics = [
        'hits' => 0,
        'misses' => 0,
    ];

    public function __construct(StorageOrganizerInterface $organizer)
    {
        $this->organizer = $organizer;
        $this->prefix = config('filestorage.cache.prefix', 'file_url');
        $this->ttl = config('filestorage.cache.ttl', 3600);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(string $path, string $size, callable $generator): string
    {
        $cacheKey = $this->getCacheKey($path, $size);

        // Try to get from cache
        $cachedUrl = Cache::get($cacheKey);

        if ($cachedUrl !== null) {
            $this->statistics['hits']++;
            return $cachedUrl;
        }

        // Cache miss - generate URL
        $this->statistics['misses']++;
        $url = $generator();

        // Store in cache
        Cache::put($cacheKey, $url, $this->ttl);

        Log::debug('CacheManager: URL cached', [
            'path' => $path,
            'size' => $size,
            'cache_key' => $cacheKey,
            'ttl' => $this->ttl,
        ]);

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate(string $path): void
    {
        try {
            $pathInfo = $this->organizer->parsePath($path);
            $type = $pathInfo->type;
            $filename = $pathInfo->filename;

            // Get all possible sizes for this type
            $sizes = $this->getSizesForType($type);

            // Invalidate cache for all sizes
            foreach ($sizes as $size) {
                $cacheKey = $this->buildCacheKey($type, $size, $path);
                Cache::forget($cacheKey);
            }

            // Also invalidate the original size
            $cacheKey = $this->buildCacheKey($type, 'original', $path);
            Cache::forget($cacheKey);

            Log::debug('CacheManager: Cache invalidated for path', [
                'path' => $path,
                'type' => $type,
                'sizes_invalidated' => array_merge($sizes, ['original']),
            ]);
        } catch (\Exception $e) {
            // If path parsing fails, try to invalidate using hash-based key
            Log::warning('CacheManager: Failed to parse path for invalidation', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateType(string $type): void
    {
        // Use cache tags if available, otherwise log warning
        if ($this->supportsCacheTags()) {
            Cache::tags([$this->getTypeTag($type)])->flush();
            
            Log::info('CacheManager: Cache invalidated for type', [
                'type' => $type,
            ]);
        } else {
            // Without cache tags, we can't efficiently invalidate by type
            // Log a warning and suggest using a cache driver that supports tags
            Log::warning('CacheManager: Cannot invalidate by type without cache tags support', [
                'type' => $type,
                'suggestion' => 'Use Redis or Memcached cache driver for tag support',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateAll(): void
    {
        if ($this->supportsCacheTags()) {
            Cache::tags([$this->prefix])->flush();
            
            Log::info('CacheManager: All file URL cache invalidated');
        } else {
            Log::warning('CacheManager: Cannot invalidate all without cache tags support', [
                'suggestion' => 'Use Redis or Memcached cache driver for tag support',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $path, string $size): bool
    {
        $cacheKey = $this->getCacheKey($path, $size);
        return Cache::has($cacheKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey(string $path, string $size): string
    {
        try {
            $pathInfo = $this->organizer->parsePath($path);
            return $this->buildCacheKey($pathInfo->type, $size, $path);
        } catch (\Exception $e) {
            // Fallback to hash-based key if path parsing fails
            return $this->buildCacheKeyFromHash($size, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatistics(): array
    {
        $total = $this->statistics['hits'] + $this->statistics['misses'];
        $hitRate = $total > 0 ? ($this->statistics['hits'] / $total) * 100 : 0;

        return [
            'hits' => $this->statistics['hits'],
            'misses' => $this->statistics['misses'],
            'total' => $total,
            'hit_rate' => round($hitRate, 2),
        ];
    }

    /**
     * Build cache key dengan format: {prefix}_{type}_{size}_{hash}
     */
    protected function buildCacheKey(string $type, string $size, string $path): string
    {
        $hash = $this->hashPath($path);
        return "{$this->prefix}_{$type}_{$size}_{$hash}";
    }

    /**
     * Build cache key from hash only (fallback).
     */
    protected function buildCacheKeyFromHash(string $size, string $path): string
    {
        $hash = $this->hashPath($path);
        return "{$this->prefix}_unknown_{$size}_{$hash}";
    }

    /**
     * Hash path untuk cache key.
     */
    protected function hashPath(string $path): string
    {
        return md5($path);
    }

    /**
     * Get all configured sizes for a file type.
     */
    protected function getSizesForType(string $type): array
    {
        $config = config("filestorage.types.{$type}", []);
        $sizes = [];

        // Get variant sizes
        if (isset($config['variants']) && is_array($config['variants'])) {
            $sizes = array_keys($config['variants']);
        }

        // Add thumbnail if configured
        if (isset($config['thumbnail'])) {
            $sizes[] = 'thumbnail';
        }

        return $sizes;
    }

    /**
     * Get cache tag for type.
     */
    protected function getTypeTag(string $type): string
    {
        return "{$this->prefix}_{$type}";
    }

    /**
     * Check if current cache driver supports tags.
     */
    protected function supportsCacheTags(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached', 'dynamodb']);
    }

    /**
     * Set custom TTL (useful for testing).
     */
    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    /**
     * Get current TTL.
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Reset statistics (useful for testing).
     */
    public function resetStatistics(): void
    {
        $this->statistics = [
            'hits' => 0,
            'misses' => 0,
        ];
    }
}
