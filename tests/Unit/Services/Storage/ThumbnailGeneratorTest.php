<?php

namespace Tests\Unit\Services\Storage;

use App\Services\Storage\DTOs\BatchResult;
use App\Services\Storage\DTOs\ProcessedImage;
use App\Services\Storage\Exceptions\FileNotFoundException;
use App\Services\Storage\Exceptions\FileProcessingException;
use App\Services\Storage\StorageOrganizer;
use App\Services\Storage\ThumbnailGenerator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThumbnailGeneratorTest extends TestCase
{
    protected ThumbnailGenerator $generator;
    protected StorageOrganizer $organizer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use fake storage for testing
        Storage::fake('public');
        
        $this->organizer = new StorageOrganizer();
        $this->generator = new ThumbnailGenerator($this->organizer);
    }

    /**
     * Create a test image file.
     */
    protected function createTestImage(string $path, int $width = 800, int $height = 600): string
    {
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $color);
        
        $fullPath = Storage::disk('public')->path($path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        imagejpeg($image, $fullPath, 90);
        imagedestroy($image);
        
        return $path;
    }

    /**
     * Test getThumbnailPath returns correct path format.
     */
    public function test_get_thumbnail_path_returns_correct_format(): void
    {
        $originalPath = 'product/2026/01/abc-123.webp';
        $thumbnailPath = $this->generator->getThumbnailPath($originalPath, 150, 150);
        
        $this->assertEquals('product/2026/01/thumbnails/abc-123_150x150.webp', $thumbnailPath);
    }

    /**
     * Test getThumbnailPath with attendance path (includes day).
     */
    public function test_get_thumbnail_path_with_day_component(): void
    {
        $originalPath = 'attendance/2026/01/15/abc-123.webp';
        $thumbnailPath = $this->generator->getThumbnailPath($originalPath, 80, 80);
        
        $this->assertEquals('attendance/2026/01/15/thumbnails/abc-123_80x80.webp', $thumbnailPath);
    }

    /**
     * Test generate creates thumbnail with correct dimensions.
     */
    public function test_generate_creates_thumbnail_with_correct_dimensions(): void
    {
        $originalPath = $this->createTestImage('product/2026/01/test-image.jpg', 800, 600);
        
        $result = $this->generator->generate($originalPath, 150, 150, 'public');
        
        $this->assertInstanceOf(ProcessedImage::class, $result);
        $this->assertEquals(150, $result->width);
        $this->assertEquals(150, $result->height);
        $this->assertEquals('webp', $result->format);
        $this->assertEquals('image/webp', $result->mimeType);
        $this->assertTrue($result->wasResized);
    }

    /**
     * Test generate throws exception for non-existent file.
     */
    public function test_generate_throws_exception_for_non_existent_file(): void
    {
        $this->expectException(FileNotFoundException::class);
        
        $this->generator->generate('product/2026/01/non-existent.jpg', 150, 150, 'public');
    }

    /**
     * Test generate throws exception for invalid image.
     */
    public function test_generate_throws_exception_for_invalid_image(): void
    {
        // Create a non-image file
        Storage::disk('public')->put('product/2026/01/not-an-image.txt', 'This is not an image');
        
        $this->expectException(FileProcessingException::class);
        
        $this->generator->generate('product/2026/01/not-an-image.txt', 150, 150, 'public');
    }

    /**
     * Test exists returns false when thumbnail does not exist.
     */
    public function test_exists_returns_false_when_thumbnail_does_not_exist(): void
    {
        $originalPath = $this->createTestImage('product/2026/01/test-image.jpg');
        
        $this->assertFalse($this->generator->exists($originalPath, 150, 150, 'public'));
    }

    /**
     * Test exists returns true after thumbnail is generated.
     */
    public function test_exists_returns_true_after_thumbnail_generated(): void
    {
        $originalPath = $this->createTestImage('product/2026/01/test-image.jpg');
        
        $this->generator->generate($originalPath, 150, 150, 'public');
        
        $this->assertTrue($this->generator->exists($originalPath, 150, 150, 'public'));
    }

    /**
     * Test getThumbnailUrl generates thumbnail on-demand.
     */
    public function test_get_thumbnail_url_generates_on_demand(): void
    {
        $originalPath = $this->createTestImage('product/2026/01/test-image.jpg');
        
        // Thumbnail should not exist yet
        $this->assertFalse($this->generator->exists($originalPath, 150, 150, 'public'));
        
        // Get URL should generate thumbnail
        $url = $this->generator->getThumbnailUrl($originalPath, 150, 150, 'public');
        
        // Thumbnail should now exist
        $this->assertTrue($this->generator->exists($originalPath, 150, 150, 'public'));
        $this->assertNotEmpty($url);
    }

    /**
     * Test getThumbnailUrl returns cached thumbnail.
     */
    public function test_get_thumbnail_url_returns_cached_thumbnail(): void
    {
        $originalPath = $this->createTestImage('product/2026/01/test-image.jpg');
        
        // Generate thumbnail first
        $this->generator->generate($originalPath, 150, 150, 'public');
        
        // Get URL should return cached version
        $url = $this->generator->getThumbnailUrl($originalPath, 150, 150, 'public');
        
        $this->assertNotEmpty($url);
        $this->assertStringContainsString('thumbnails', $url);
    }

    /**
     * Test getThumbnailUrl falls back to original on failure.
     */
    public function test_get_thumbnail_url_falls_back_to_original_on_failure(): void
    {
        // Create a non-image file
        Storage::disk('public')->put('product/2026/01/not-an-image.txt', 'This is not an image');
        
        // Should return original URL as fallback
        $url = $this->generator->getThumbnailUrl('product/2026/01/not-an-image.txt', 150, 150, 'public');
        
        $this->assertNotEmpty($url);
        $this->assertStringContainsString('not-an-image.txt', $url);
    }

    /**
     * Test generateBatch processes multiple images.
     */
    public function test_generate_batch_processes_multiple_images(): void
    {
        $paths = [
            $this->createTestImage('product/2026/01/image1.jpg'),
            $this->createTestImage('product/2026/01/image2.jpg'),
            $this->createTestImage('product/2026/01/image3.jpg'),
        ];
        
        $result = $this->generator->generateBatch($paths, 150, 150, 'public');
        
        $this->assertInstanceOf(BatchResult::class, $result);
        $this->assertEquals(3, $result->total);
        $this->assertEquals(3, $result->success);
        $this->assertEquals(0, $result->failed);
        $this->assertTrue($result->isAllSuccessful());
    }

    /**
     * Test generateBatch handles failures gracefully.
     */
    public function test_generate_batch_handles_failures_gracefully(): void
    {
        $paths = [
            $this->createTestImage('product/2026/01/image1.jpg'),
            'product/2026/01/non-existent.jpg', // This will fail
            $this->createTestImage('product/2026/01/image3.jpg'),
        ];
        
        $result = $this->generator->generateBatch($paths, 150, 150, 'public');
        
        $this->assertEquals(3, $result->total);
        $this->assertEquals(2, $result->success);
        $this->assertEquals(1, $result->failed);
        $this->assertTrue($result->hasFailures());
        $this->assertArrayHasKey('product/2026/01/non-existent.jpg', $result->errors);
    }

    /**
     * Test delete removes thumbnail.
     */
    public function test_delete_removes_thumbnail(): void
    {
        $originalPath = $this->createTestImage('product/2026/01/test-image.jpg');
        
        // Generate thumbnail
        $this->generator->generate($originalPath, 150, 150, 'public');
        $this->assertTrue($this->generator->exists($originalPath, 150, 150, 'public'));
        
        // Delete thumbnail
        $result = $this->generator->delete($originalPath, 150, 150, 'public');
        
        $this->assertTrue($result);
        $this->assertFalse($this->generator->exists($originalPath, 150, 150, 'public'));
    }

    /**
     * Test delete returns true for non-existent thumbnail.
     */
    public function test_delete_returns_true_for_non_existent_thumbnail(): void
    {
        $result = $this->generator->delete('product/2026/01/non-existent.jpg', 150, 150, 'public');
        
        $this->assertTrue($result);
    }

    /**
     * Test deleteAll removes all thumbnails for a file.
     */
    public function test_delete_all_removes_all_thumbnails(): void
    {
        $originalPath = $this->createTestImage('product/2026/01/test-image.jpg');
        
        // Generate multiple thumbnails
        $this->generator->generate($originalPath, 150, 150, 'public');
        $this->generator->generate($originalPath, 80, 80, 'public');
        $this->generator->generate($originalPath, 40, 40, 'public');
        
        // Verify all exist
        $this->assertTrue($this->generator->exists($originalPath, 150, 150, 'public'));
        $this->assertTrue($this->generator->exists($originalPath, 80, 80, 'public'));
        $this->assertTrue($this->generator->exists($originalPath, 40, 40, 'public'));
        
        // Delete all
        $deleted = $this->generator->deleteAll($originalPath, 'public');
        
        $this->assertEquals(3, $deleted);
        $this->assertFalse($this->generator->exists($originalPath, 150, 150, 'public'));
        $this->assertFalse($this->generator->exists($originalPath, 80, 80, 'public'));
        $this->assertFalse($this->generator->exists($originalPath, 40, 40, 'public'));
    }

    /**
     * Test thumbnail preserves cover mode cropping.
     */
    public function test_thumbnail_uses_cover_mode_cropping(): void
    {
        // Create a wide image (landscape)
        $originalPath = $this->createTestImage('product/2026/01/wide-image.jpg', 1000, 500);
        
        // Generate square thumbnail
        $result = $this->generator->generate($originalPath, 100, 100, 'public');
        
        // Should be exactly 100x100 (cover mode fills entire area)
        $this->assertEquals(100, $result->width);
        $this->assertEquals(100, $result->height);
    }

    /**
     * Test thumbnail converts to WebP format.
     */
    public function test_thumbnail_converts_to_webp(): void
    {
        $originalPath = $this->createTestImage('product/2026/01/test-image.jpg');
        
        $result = $this->generator->generate($originalPath, 150, 150, 'public');
        
        $this->assertEquals('webp', $result->format);
        $this->assertEquals('image/webp', $result->mimeType);
        $this->assertTrue($result->wasConverted);
    }
}
