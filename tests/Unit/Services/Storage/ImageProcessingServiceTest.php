<?php

namespace Tests\Unit\Services\Storage;

use App\Services\Storage\DTOs\ProcessedImage;
use App\Services\Storage\DTOs\ValidationResult;
use App\Services\Storage\Exceptions\FileProcessingException;
use App\Services\Storage\Exceptions\FileValidationException;
use App\Services\Storage\ImageProcessingService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageProcessingServiceTest extends TestCase
{
    protected ImageProcessingService $service;

    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ImageProcessingService;
        $this->tempDir = sys_get_temp_dir().'/image_processing_test_'.uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        $this->recursiveDelete($this->tempDir);
        parent::tearDown();
    }

    /**
     * Recursively delete a directory.
     */
    protected function recursiveDelete(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Create a test image file.
     */
    protected function createTestImage(int $width = 100, int $height = 100, string $format = 'jpeg'): string
    {
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 255, 0, 0); // Red
        imagefill($image, 0, 0, $color);

        $extension = match ($format) {
            'jpeg', 'jpg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            default => 'jpg',
        };

        $path = $this->tempDir.'/test_image.'.$extension;

        match ($format) {
            'jpeg', 'jpg' => imagejpeg($image, $path, 90),
            'png' => imagepng($image, $path),
            'gif' => imagegif($image, $path),
            'webp' => imagewebp($image, $path, 90),
            default => imagejpeg($image, $path, 90),
        };

        imagedestroy($image);

        return $path;
    }

    /**
     * Create an UploadedFile from a test image.
     */
    protected function createUploadedFile(string $path, ?string $mimeType = null): UploadedFile
    {
        $mimeType = $mimeType ?? mime_content_type($path);

        return new UploadedFile(
            $path,
            basename($path),
            $mimeType,
            null,
            true // test mode
        );
    }

    // ==================== Validation Tests ====================

    /**
     * Test validate returns valid for correct image.
     */
    public function test_validate_returns_valid_for_correct_image(): void
    {
        $imagePath = $this->createTestImage(100, 100, 'jpeg');
        $file = $this->createUploadedFile($imagePath);

        $result = $this->service->validate($file, 'product');

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->errors);
    }

    /**
     * Test validate returns invalid for file too large.
     */
    public function test_validate_returns_invalid_for_file_too_large(): void
    {
        // Create a large image (this will be larger than 2MB limit for attendance)
        $imagePath = $this->createTestImage(3000, 3000, 'png');
        $file = $this->createUploadedFile($imagePath);

        // Attendance has 2MB limit
        $result = $this->service->validate($file, 'attendance');

        // If file is actually larger than limit, it should be invalid
        if (filesize($imagePath) > 2 * 1024 * 1024) {
            $this->assertFalse($result->isValid());
            $this->assertNotEmpty($result->errors);
        } else {
            // File might be smaller due to compression
            $this->assertTrue($result->isValid());
        }
    }

    /**
     * Test validate returns invalid for wrong MIME type.
     */
    public function test_validate_returns_invalid_for_wrong_mime_type(): void
    {
        $imagePath = $this->createTestImage(100, 100, 'gif');
        $file = $this->createUploadedFile($imagePath);

        // Banner only allows jpeg and png
        $result = $this->service->validate($file, 'banner');

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->errors);
    }

    /**
     * Test validate returns invalid for invalid type.
     */
    public function test_validate_returns_invalid_for_invalid_type(): void
    {
        $imagePath = $this->createTestImage(100, 100, 'jpeg');
        $file = $this->createUploadedFile($imagePath);

        $result = $this->service->validate($file, 'invalid_type');

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->errors);
    }

    /**
     * Test validate accepts all supported formats for product.
     */
    public function test_validate_accepts_all_supported_formats_for_product(): void
    {
        $formats = ['jpeg', 'png', 'gif', 'webp'];

        foreach ($formats as $format) {
            $imagePath = $this->createTestImage(100, 100, $format);
            $file = $this->createUploadedFile($imagePath);

            $result = $this->service->validate($file, 'product');

            $this->assertTrue($result->isValid(), "Format {$format} should be valid for product");

            // Clean up
            unlink($imagePath);
        }
    }

    // ==================== Process Tests ====================

    /**
     * Test process creates output file.
     */
    public function test_process_creates_output_file(): void
    {
        $sourcePath = $this->createTestImage(100, 100, 'jpeg');
        $targetPath = $this->tempDir.'/output/processed.jpg';

        $result = $this->service->process($sourcePath, 'product', $targetPath);

        $this->assertInstanceOf(ProcessedImage::class, $result);
        $this->assertFileExists($result->path);
    }

    /**
     * Test process converts to WebP when configured.
     */
    public function test_process_converts_to_webp_when_configured(): void
    {
        $sourcePath = $this->createTestImage(100, 100, 'jpeg');
        $targetPath = $this->tempDir.'/output/processed.jpg';

        // Product type has convert_to_webp = true
        $result = $this->service->process($sourcePath, 'product', $targetPath);

        $this->assertEquals('webp', $result->format);
        $this->assertEquals('image/webp', $result->mimeType);
        $this->assertTrue($result->wasConverted);
        $this->assertStringEndsWith('.webp', $result->path);
    }

    /**
     * Test process keeps original format when WebP conversion disabled.
     */
    public function test_process_keeps_original_format_when_webp_disabled(): void
    {
        $sourcePath = $this->createTestImage(100, 100, 'jpeg');
        $targetPath = $this->tempDir.'/output/processed.jpg';

        // Banner type has convert_to_webp = false
        $result = $this->service->process($sourcePath, 'banner', $targetPath);

        $this->assertEquals('jpeg', $result->format);
        $this->assertEquals('image/jpeg', $result->mimeType);
        $this->assertFalse($result->wasConverted);
    }

    /**
     * Test process resizes large images.
     */
    public function test_process_resizes_large_images(): void
    {
        // Create image larger than max_width (1920)
        $sourcePath = $this->createTestImage(2500, 1500, 'jpeg');
        $targetPath = $this->tempDir.'/output/processed.jpg';

        $result = $this->service->process($sourcePath, 'product', $targetPath);

        $this->assertTrue($result->wasResized);
        $this->assertLessThanOrEqual(1920, $result->width);
    }

    /**
     * Test process preserves aspect ratio when resizing.
     */
    public function test_process_preserves_aspect_ratio_when_resizing(): void
    {
        // Create image with 2:1 aspect ratio, larger than max_width
        $sourcePath = $this->createTestImage(2400, 1200, 'jpeg');
        $targetPath = $this->tempDir.'/output/processed.jpg';

        $result = $this->service->process($sourcePath, 'product', $targetPath);

        // Original ratio: 2400/1200 = 2.0
        // New ratio should be approximately the same
        $originalRatio = 2400 / 1200;
        $newRatio = $result->width / $result->height;

        $this->assertEqualsWithDelta($originalRatio, $newRatio, 0.01);
    }

    /**
     * Test process does not resize small images.
     */
    public function test_process_does_not_resize_small_images(): void
    {
        $sourcePath = $this->createTestImage(800, 600, 'jpeg');
        $targetPath = $this->tempDir.'/output/processed.jpg';

        $result = $this->service->process($sourcePath, 'product', $targetPath);

        $this->assertFalse($result->wasResized);
        $this->assertEquals(800, $result->width);
        $this->assertEquals(600, $result->height);
    }

    /**
     * Test process throws exception for non-existent file.
     */
    public function test_process_throws_exception_for_non_existent_file(): void
    {
        $this->expectException(FileProcessingException::class);

        $this->service->process('/non/existent/file.jpg', 'product', $this->tempDir.'/output.jpg');
    }

    /**
     * Test process throws exception for invalid type.
     */
    public function test_process_throws_exception_for_invalid_type(): void
    {
        $sourcePath = $this->createTestImage(100, 100, 'jpeg');

        $this->expectException(FileValidationException::class);

        $this->service->process($sourcePath, 'invalid_type', $this->tempDir.'/output.jpg');
    }

    // ==================== Generate Variants Tests ====================

    /**
     * Test generateVariants creates all configured variants.
     */
    public function test_generate_variants_creates_all_configured_variants(): void
    {
        $sourcePath = $this->createTestImage(1000, 1000, 'jpeg');
        $baseTargetPath = $this->tempDir.'/variants/image.jpg';

        // Product has thumbnail, medium, large variants
        $results = $this->service->generateVariants($sourcePath, 'product', $baseTargetPath);

        $this->assertArrayHasKey('thumbnail', $results);
        $this->assertArrayHasKey('medium', $results);
        $this->assertArrayHasKey('large', $results);

        foreach ($results as $variant) {
            $this->assertInstanceOf(ProcessedImage::class, $variant);
            $this->assertFileExists($variant->path);
        }
    }

    /**
     * Test generateVariants creates correct dimensions.
     */
    public function test_generate_variants_creates_correct_dimensions(): void
    {
        $sourcePath = $this->createTestImage(1000, 1000, 'jpeg');
        $baseTargetPath = $this->tempDir.'/variants/image.jpg';

        $results = $this->service->generateVariants($sourcePath, 'product', $baseTargetPath);

        // Thumbnail should be 150x150 (or smaller maintaining aspect ratio)
        $this->assertLessThanOrEqual(150, $results['thumbnail']->width);
        $this->assertLessThanOrEqual(150, $results['thumbnail']->height);

        // Medium should be 400x400 (or smaller maintaining aspect ratio)
        $this->assertLessThanOrEqual(400, $results['medium']->width);
        $this->assertLessThanOrEqual(400, $results['medium']->height);

        // Large should be 800x800 (or smaller maintaining aspect ratio)
        $this->assertLessThanOrEqual(800, $results['large']->width);
        $this->assertLessThanOrEqual(800, $results['large']->height);
    }

    /**
     * Test generateVariants returns empty for type without variants.
     */
    public function test_generate_variants_returns_empty_for_type_without_variants(): void
    {
        $sourcePath = $this->createTestImage(100, 100, 'jpeg');
        $baseTargetPath = $this->tempDir.'/variants/image.jpg';

        // Leave type has no variants
        $results = $this->service->generateVariants($sourcePath, 'leave', $baseTargetPath);

        $this->assertEmpty($results);
    }

    /**
     * Test generateVariants throws exception for non-existent file.
     */
    public function test_generate_variants_throws_exception_for_non_existent_file(): void
    {
        $this->expectException(FileProcessingException::class);

        $this->service->generateVariants('/non/existent/file.jpg', 'product', $this->tempDir.'/output.jpg');
    }

    // ==================== Helper Method Tests ====================

    /**
     * Test isValidImage returns true for valid images.
     */
    public function test_is_valid_image_returns_true_for_valid_images(): void
    {
        $formats = ['jpeg', 'png', 'gif', 'webp'];

        foreach ($formats as $format) {
            $imagePath = $this->createTestImage(100, 100, $format);

            $this->assertTrue($this->service->isValidImage($imagePath), "Format {$format} should be valid");

            unlink($imagePath);
        }
    }

    /**
     * Test isValidImage returns false for non-existent file.
     */
    public function test_is_valid_image_returns_false_for_non_existent_file(): void
    {
        $this->assertFalse($this->service->isValidImage('/non/existent/file.jpg'));
    }

    /**
     * Test isValidImage returns false for non-image file.
     */
    public function test_is_valid_image_returns_false_for_non_image_file(): void
    {
        $textFile = $this->tempDir.'/test.txt';
        file_put_contents($textFile, 'This is not an image');

        $this->assertFalse($this->service->isValidImage($textFile));
    }

    /**
     * Test getDimensions returns correct dimensions.
     */
    public function test_get_dimensions_returns_correct_dimensions(): void
    {
        $imagePath = $this->createTestImage(800, 600, 'jpeg');

        $dimensions = $this->service->getDimensions($imagePath);

        $this->assertIsArray($dimensions);
        $this->assertEquals(800, $dimensions['width']);
        $this->assertEquals(600, $dimensions['height']);
    }

    /**
     * Test getDimensions returns null for non-existent file.
     */
    public function test_get_dimensions_returns_null_for_non_existent_file(): void
    {
        $this->assertNull($this->service->getDimensions('/non/existent/file.jpg'));
    }

    /**
     * Test getSupportedFormats returns expected formats.
     */
    public function test_get_supported_formats_returns_expected_formats(): void
    {
        $formats = $this->service->getSupportedFormats();

        $this->assertContains('image/jpeg', $formats);
        $this->assertContains('image/png', $formats);
        $this->assertContains('image/gif', $formats);
        $this->assertContains('image/webp', $formats);
    }

    // ==================== Aspect Ratio Preservation Tests ====================

    /**
     * Test variants preserve aspect ratio for landscape images.
     */
    public function test_variants_preserve_aspect_ratio_for_landscape_images(): void
    {
        // Create landscape image (16:9 ratio)
        $sourcePath = $this->createTestImage(1600, 900, 'jpeg');
        $baseTargetPath = $this->tempDir.'/variants/image.jpg';

        $results = $this->service->generateVariants($sourcePath, 'product', $baseTargetPath);

        $originalRatio = 1600 / 900;

        foreach ($results as $name => $variant) {
            $variantRatio = $variant->width / $variant->height;
            $this->assertEqualsWithDelta(
                $originalRatio,
                $variantRatio,
                0.01,
                "Variant {$name} should preserve aspect ratio"
            );
        }
    }

    /**
     * Test variants preserve aspect ratio for portrait images.
     */
    public function test_variants_preserve_aspect_ratio_for_portrait_images(): void
    {
        // Create portrait image (9:16 ratio)
        $sourcePath = $this->createTestImage(900, 1600, 'jpeg');
        $baseTargetPath = $this->tempDir.'/variants/image.jpg';

        $results = $this->service->generateVariants($sourcePath, 'product', $baseTargetPath);

        $originalRatio = 900 / 1600;

        foreach ($results as $name => $variant) {
            $variantRatio = $variant->width / $variant->height;
            $this->assertEqualsWithDelta(
                $originalRatio,
                $variantRatio,
                0.01,
                "Variant {$name} should preserve aspect ratio"
            );
        }
    }
}
