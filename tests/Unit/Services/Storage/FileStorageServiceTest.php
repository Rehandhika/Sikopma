<?php

namespace Tests\Unit\Services\Storage;

use App\Services\Storage\CacheManager;
use App\Services\Storage\CacheManagerInterface;
use App\Services\Storage\DTOs\FileResult;
use App\Services\Storage\DTOs\ProcessedImage;
use App\Services\Storage\DTOs\ValidationResult;
use App\Services\Storage\Exceptions\FileValidationException;
use App\Services\Storage\FileStorageService;
use App\Services\Storage\ImageProcessingService;
use App\Services\Storage\ImageProcessingServiceInterface;
use App\Services\Storage\StorageOrganizer;
use App\Services\Storage\StorageOrganizerInterface;
use App\Services\Storage\ThumbnailGenerator;
use App\Services\Storage\ThumbnailGeneratorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageServiceTest extends TestCase
{
    protected FileStorageService $service;
    protected StorageOrganizerInterface $storageOrganizer;
    protected CacheManagerInterface $cacheManager;
    protected ImageProcessingServiceInterface $imageProcessor;
    protected ThumbnailGeneratorInterface $thumbnailGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');

        $this->storageOrganizer = new StorageOrganizer();
        $this->cacheManager = new CacheManager($this->storageOrganizer);
        $this->imageProcessor = new ImageProcessingService();
        $this->thumbnailGenerator = new ThumbnailGenerator($this->storageOrganizer);

        $this->service = new FileStorageService(
            $this->imageProcessor,
            $this->storageOrganizer,
            $this->cacheManager,
            $this->thumbnailGenerator
        );
    }


    /** @test */
    public function it_can_upload_image_file()
    {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $result = $this->service->upload($file, 'product');

        $this->assertInstanceOf(FileResult::class, $result);
        $this->assertStringStartsWith('product/', $result->path);
        $this->assertStringEndsWith('.webp', $result->path);
        $this->assertNotEmpty($result->url);
        $this->assertNotEmpty($result->checksum);
        $this->assertGreaterThan(0, $result->size);
        Storage::disk('public')->assertExists($result->path);
    }

    /** @test */
    public function it_generates_variants_for_product_images()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1000, 1000);

        $result = $this->service->upload($file, 'product');

        $this->assertArrayHasKey('thumbnail', $result->variants);
        $this->assertArrayHasKey('medium', $result->variants);
        $this->assertArrayHasKey('large', $result->variants);
    }

    /** @test */
    public function it_throws_exception_for_invalid_type()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $this->expectException(FileValidationException::class);

        $this->service->upload($file, 'invalid_type');
    }

    /** @test */
    public function it_throws_exception_for_file_too_large()
    {
        // Create a file larger than 5MB limit for products
        $file = UploadedFile::fake()->create('large.jpg', 6000, 'image/jpeg');

        $this->expectException(FileValidationException::class);

        $this->service->upload($file, 'product');
    }

    /** @test */
    public function it_can_delete_file_and_variants()
    {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        $result = $this->service->upload($file, 'product');

        // Verify file exists
        Storage::disk('public')->assertExists($result->path);

        // Delete file
        $deleted = $this->service->delete($result->path);

        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing($result->path);
    }


    /** @test */
    public function it_can_check_if_file_exists()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        $result = $this->service->upload($file, 'product');

        $this->assertTrue($this->service->exists($result->path));
        $this->assertFalse($this->service->exists('nonexistent/path.jpg'));
    }

    /** @test */
    public function it_can_get_url_for_file()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);
        $result = $this->service->upload($file, 'product');

        $url = $this->service->getUrl($result->path);

        $this->assertNotNull($url);
        $this->assertStringContainsString($result->path, $url);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_file_url()
    {
        $url = $this->service->getUrl('nonexistent/2026/01/file.jpg');

        $this->assertNull($url);
    }

    /** @test */
    public function it_can_upload_from_base64()
    {
        // Create a simple 1x1 red pixel PNG
        $image = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $red);
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        $base64 = 'data:image/png;base64,' . base64_encode($imageData);

        $result = $this->service->uploadFromBase64($base64, 'attendance');

        $this->assertInstanceOf(FileResult::class, $result);
        $this->assertStringStartsWith('attendance/', $result->path);
        Storage::disk('public')->assertExists($result->path);
    }

    /** @test */
    public function it_deletes_old_file_when_uploading_replacement()
    {
        // Upload first file
        $file1 = UploadedFile::fake()->image('first.jpg', 400, 300);
        $result1 = $this->service->upload($file1, 'profile');
        
        Storage::disk('public')->assertExists($result1->path);

        // Upload replacement with old_path option
        $file2 = UploadedFile::fake()->image('second.jpg', 400, 300);
        $result2 = $this->service->upload($file2, 'profile', ['old_path' => $result1->path]);

        // Old file should be deleted
        Storage::disk('public')->assertMissing($result1->path);
        // New file should exist
        Storage::disk('public')->assertExists($result2->path);
    }


    /** @test */
    public function it_can_get_disk_for_type()
    {
        $this->assertEquals('public', $this->service->getDiskForType('product'));
        $this->assertEquals('public', $this->service->getDiskForType('banner'));
        $this->assertEquals('local', $this->service->getDiskForType('leave'));
        $this->assertEquals('local', $this->service->getDiskForType('report'));
    }

    /** @test */
    public function it_can_get_type_config()
    {
        $config = $this->service->getTypeConfig('product');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('disk', $config);
        $this->assertArrayHasKey('max_size', $config);
        $this->assertArrayHasKey('allowed_mimes', $config);
        $this->assertArrayHasKey('variants', $config);
    }

    /** @test */
    public function it_returns_null_for_invalid_type_config()
    {
        $config = $this->service->getTypeConfig('invalid_type');

        $this->assertNull($config);
    }

    /** @test */
    public function it_stores_non_image_files_without_processing()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $result = $this->service->upload($file, 'leave');

        $this->assertInstanceOf(FileResult::class, $result);
        $this->assertStringStartsWith('leave/', $result->path);
        $this->assertStringEndsWith('.pdf', $result->path);
        $this->assertEmpty($result->variants);
        Storage::disk('local')->assertExists($result->path);
    }

    /** @test */
    public function it_generates_unique_paths_for_multiple_uploads()
    {
        $file1 = UploadedFile::fake()->image('test.jpg', 400, 300);
        $file2 = UploadedFile::fake()->image('test.jpg', 400, 300);

        $result1 = $this->service->upload($file1, 'product');
        $result2 = $this->service->upload($file2, 'product');

        $this->assertNotEquals($result1->path, $result2->path);
    }

    /** @test */
    public function it_includes_checksum_in_result()
    {
        $file = UploadedFile::fake()->image('test.jpg', 400, 300);

        $result = $this->service->upload($file, 'product');

        $this->assertNotEmpty($result->checksum);
        $this->assertEquals(32, strlen($result->checksum)); // MD5 hash length
    }
}
