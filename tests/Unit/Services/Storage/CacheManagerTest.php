<?php

namespace Tests\Unit\Services\Storage;

use App\Services\Storage\CacheManager;
use App\Services\Storage\StorageOrganizer;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheManagerTest extends TestCase
{
    protected CacheManager $cacheManager;

    protected StorageOrganizer $organizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = new StorageOrganizer;
        $this->cacheManager = new CacheManager($this->organizer);

        // Clear cache before each test
        Cache::flush();
    }

    /**
     * Test getUrl returns cached value on cache hit.
     */
    public function test_get_url_returns_cached_value_on_cache_hit(): void
    {
        $path = 'product/2026/01/abc-123.webp';
        $size = 'original';
        $expectedUrl = '/storage/product/2026/01/abc-123.webp';

        $generatorCallCount = 0;
        $generator = function () use ($expectedUrl, &$generatorCallCount) {
            $generatorCallCount++;

            return $expectedUrl;
        };

        // First call - should call generator
        $url1 = $this->cacheManager->getUrl($path, $size, $generator);
        $this->assertEquals($expectedUrl, $url1);
        $this->assertEquals(1, $generatorCallCount);

        // Second call - should return cached value
        $url2 = $this->cacheManager->getUrl($path, $size, $generator);
        $this->assertEquals($expectedUrl, $url2);
        $this->assertEquals(1, $generatorCallCount); // Generator not called again
    }

    /**
     * Test getUrl calls generator on cache miss.
     */
    public function test_get_url_calls_generator_on_cache_miss(): void
    {
        $path = 'product/2026/01/abc-123.webp';
        $size = 'thumbnail';
        $expectedUrl = '/storage/product/2026/01/thumbnail/abc-123.webp';

        $generatorCalled = false;
        $generator = function () use ($expectedUrl, &$generatorCalled) {
            $generatorCalled = true;

            return $expectedUrl;
        };

        $url = $this->cacheManager->getUrl($path, $size, $generator);

        $this->assertTrue($generatorCalled);
        $this->assertEquals($expectedUrl, $url);
    }

    /**
     * Test getCacheKey generates correct format.
     */
    public function test_get_cache_key_generates_correct_format(): void
    {
        $path = 'product/2026/01/abc-123.webp';
        $size = 'thumbnail';

        $cacheKey = $this->cacheManager->getCacheKey($path, $size);

        // Should match format: file_url_{type}_{size}_{hash}
        $this->assertMatchesRegularExpression(
            '/^file_url_product_thumbnail_[a-f0-9]{32}$/',
            $cacheKey
        );
    }

    /**
     * Test getCacheKey uses fallback for invalid path.
     */
    public function test_get_cache_key_uses_fallback_for_invalid_path(): void
    {
        $path = 'invalid/path';
        $size = 'original';

        $cacheKey = $this->cacheManager->getCacheKey($path, $size);

        // Should use fallback format: file_url_unknown_{size}_{hash}
        $this->assertMatchesRegularExpression(
            '/^file_url_unknown_original_[a-f0-9]{32}$/',
            $cacheKey
        );
    }

    /**
     * Test invalidate removes cache for path.
     */
    public function test_invalidate_removes_cache_for_path(): void
    {
        $path = 'product/2026/01/abc-123.webp';
        $size = 'original';
        $url = '/storage/product/2026/01/abc-123.webp';

        // Cache the URL
        $this->cacheManager->getUrl($path, $size, fn () => $url);

        // Verify it's cached
        $this->assertTrue($this->cacheManager->has($path, $size));

        // Invalidate
        $this->cacheManager->invalidate($path);

        // Verify it's removed
        $this->assertFalse($this->cacheManager->has($path, $size));
    }

    /**
     * Test has returns true when cached.
     */
    public function test_has_returns_true_when_cached(): void
    {
        $path = 'product/2026/01/abc-123.webp';
        $size = 'original';

        // Initially not cached
        $this->assertFalse($this->cacheManager->has($path, $size));

        // Cache it
        $this->cacheManager->getUrl($path, $size, fn () => '/some/url');

        // Now it should be cached
        $this->assertTrue($this->cacheManager->has($path, $size));
    }

    /**
     * Test getStatistics tracks hits and misses.
     */
    public function test_get_statistics_tracks_hits_and_misses(): void
    {
        $this->cacheManager->resetStatistics();

        $path = 'product/2026/01/abc-123.webp';
        $size = 'original';

        // First call - miss
        $this->cacheManager->getUrl($path, $size, fn () => '/url');

        // Second call - hit
        $this->cacheManager->getUrl($path, $size, fn () => '/url');

        // Third call - hit
        $this->cacheManager->getUrl($path, $size, fn () => '/url');

        $stats = $this->cacheManager->getStatistics();

        $this->assertEquals(1, $stats['misses']);
        $this->assertEquals(2, $stats['hits']);
        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(66.67, $stats['hit_rate']);
    }

    /**
     * Test different sizes have different cache keys.
     */
    public function test_different_sizes_have_different_cache_keys(): void
    {
        $path = 'product/2026/01/abc-123.webp';

        $keyOriginal = $this->cacheManager->getCacheKey($path, 'original');
        $keyThumbnail = $this->cacheManager->getCacheKey($path, 'thumbnail');
        $keyMedium = $this->cacheManager->getCacheKey($path, 'medium');

        $this->assertNotEquals($keyOriginal, $keyThumbnail);
        $this->assertNotEquals($keyOriginal, $keyMedium);
        $this->assertNotEquals($keyThumbnail, $keyMedium);
    }

    /**
     * Test different paths have different cache keys.
     */
    public function test_different_paths_have_different_cache_keys(): void
    {
        $size = 'original';

        $key1 = $this->cacheManager->getCacheKey('product/2026/01/abc-123.webp', $size);
        $key2 = $this->cacheManager->getCacheKey('product/2026/01/def-456.webp', $size);

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test setTtl and getTtl work correctly.
     */
    public function test_set_ttl_and_get_ttl_work_correctly(): void
    {
        $originalTtl = $this->cacheManager->getTtl();

        $this->cacheManager->setTtl(7200);
        $this->assertEquals(7200, $this->cacheManager->getTtl());

        // Reset to original
        $this->cacheManager->setTtl($originalTtl);
    }

    /**
     * Test resetStatistics clears counters.
     */
    public function test_reset_statistics_clears_counters(): void
    {
        $path = 'product/2026/01/abc-123.webp';

        // Generate some stats
        $this->cacheManager->getUrl($path, 'original', fn () => '/url');
        $this->cacheManager->getUrl($path, 'original', fn () => '/url');

        $statsBefore = $this->cacheManager->getStatistics();
        $this->assertGreaterThan(0, $statsBefore['total']);

        // Reset
        $this->cacheManager->resetStatistics();

        $statsAfter = $this->cacheManager->getStatistics();
        $this->assertEquals(0, $statsAfter['hits']);
        $this->assertEquals(0, $statsAfter['misses']);
        $this->assertEquals(0, $statsAfter['total']);
    }

    /**
     * Test cache key includes type from path.
     */
    public function test_cache_key_includes_type_from_path(): void
    {
        $productKey = $this->cacheManager->getCacheKey('product/2026/01/abc.webp', 'original');
        $bannerKey = $this->cacheManager->getCacheKey('banner/2026/01/abc.webp', 'original');

        $this->assertStringContainsString('product', $productKey);
        $this->assertStringContainsString('banner', $bannerKey);
    }

    /**
     * Test invalidate handles invalid path gracefully.
     */
    public function test_invalidate_handles_invalid_path_gracefully(): void
    {
        // Should not throw exception
        $this->cacheManager->invalidate('invalid/path');

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    /**
     * Test getUrl with attendance path including day.
     */
    public function test_get_url_with_attendance_path_including_day(): void
    {
        $path = 'attendance/2026/01/15/abc-123.webp';
        $size = 'thumbnail';
        $expectedUrl = '/storage/attendance/2026/01/15/thumbnails/abc-123_80x80.webp';

        $url = $this->cacheManager->getUrl($path, $size, fn () => $expectedUrl);

        $this->assertEquals($expectedUrl, $url);

        // Verify cache key format
        $cacheKey = $this->cacheManager->getCacheKey($path, $size);
        $this->assertStringContainsString('attendance', $cacheKey);
    }
}
