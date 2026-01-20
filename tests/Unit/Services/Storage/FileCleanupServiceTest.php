<?php

namespace Tests\Unit\Services\Storage;

use App\Models\Product;
use App\Services\Storage\FileCleanupService;
use App\Services\Storage\StorageOrganizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class FileCleanupServiceTest extends TestCase
{
    protected FileCleanupService $cleanupService;
    protected StorageOrganizer $organizer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organizer = new StorageOrganizer();
        $this->cleanupService = new FileCleanupService($this->organizer);
        
        // Use fake storage for tests
        Storage::fake('public');
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test getStorageFiles returns files from storage.
     */
    public function test_get_storage_files_returns_files_from_storage(): void
    {
        // Create test files
        Storage::disk('public')->put('product/2026/01/abc-123.webp', 'test content');
        Storage::disk('public')->put('product/2026/01/def-456.webp', 'test content 2');
        
        $files = $this->cleanupService->getStorageFiles('product');
        
        $this->assertCount(2, $files);
    }

    /**
     * Test getStorageFiles excludes variant directories.
     */
    public function test_get_storage_files_excludes_variant_directories(): void
    {
        // Create original and variant files
        Storage::disk('public')->put('product/2026/01/abc-123.webp', 'original');
        Storage::disk('public')->put('product/2026/01/thumbnail/abc-123.webp', 'thumbnail');
        Storage::disk('public')->put('product/2026/01/medium/abc-123.webp', 'medium');
        
        $files = $this->cleanupService->getStorageFiles('product');
        
        // Should only return original, not variants
        $this->assertCount(1, $files);
        $this->assertEquals('product/2026/01/abc-123.webp', $files->first()['path']);
    }

    /**
     * Test getStorageFiles returns empty for non-existent type directory.
     */
    public function test_get_storage_files_returns_empty_for_nonexistent_directory(): void
    {
        $files = $this->cleanupService->getStorageFiles('product');
        
        $this->assertCount(0, $files);
    }

    /**
     * Test cleanTempFiles removes old temp files.
     */
    public function test_clean_temp_files_removes_old_temp_files(): void
    {
        // Create temp files
        Storage::disk('public')->put('livewire-tmp/old-file.tmp', 'old content');
        Storage::disk('public')->put('livewire-tmp/new-file.tmp', 'new content');
        
        // Set old file to be older than threshold
        $this->setFileModificationTime('public', 'livewire-tmp/old-file.tmp', Carbon::now()->subHours(48));
        
        $result = $this->cleanupService->cleanTempFiles(hoursOld: 24, dryRun: false);
        
        // Old file should be deleted, new file should remain
        $this->assertFalse(Storage::disk('public')->exists('livewire-tmp/old-file.tmp'));
        $this->assertTrue(Storage::disk('public')->exists('livewire-tmp/new-file.tmp'));
        $this->assertEquals(1, $result->filesDeleted);
    }

    /**
     * Test cleanTempFiles dry-run mode doesn't delete files.
     */
    public function test_clean_temp_files_dry_run_does_not_delete(): void
    {
        // Create temp file
        Storage::disk('public')->put('livewire-tmp/old-file.tmp', 'old content');
        $this->setFileModificationTime('public', 'livewire-tmp/old-file.tmp', Carbon::now()->subHours(48));
        
        $result = $this->cleanupService->cleanTempFiles(hoursOld: 24, dryRun: true);
        
        // File should still exist
        $this->assertTrue(Storage::disk('public')->exists('livewire-tmp/old-file.tmp'));
        $this->assertTrue($result->dryRun);
        $this->assertEquals(1, $result->filesDeleted);
    }

    /**
     * Test CleanupResult provides correct summary.
     */
    public function test_cleanup_result_provides_correct_summary(): void
    {
        Storage::disk('public')->put('livewire-tmp/old.tmp', str_repeat('x', 1024 * 100)); // 100KB
        $this->setFileModificationTime('public', 'livewire-tmp/old.tmp', Carbon::now()->subHours(48));
        
        $result = $this->cleanupService->cleanTempFiles(hoursOld: 24, dryRun: true);
        
        $this->assertGreaterThan(0, $result->bytesFreed);
        $this->assertStringContainsString('would be deleted', $result->getSummary());
    }

    /**
     * Test CleanupResult empty factory method.
     */
    public function test_cleanup_result_empty_factory(): void
    {
        $result = \App\Services\Storage\DTOs\CleanupResult::empty(true);
        
        $this->assertEquals(0, $result->filesScanned);
        $this->assertEquals(0, $result->filesDeleted);
        $this->assertEquals(0, $result->bytesFreed);
        $this->assertTrue($result->dryRun);
        $this->assertTrue($result->isSuccessful());
    }

    /**
     * Test CleanupResult toArray method.
     */
    public function test_cleanup_result_to_array(): void
    {
        $result = new \App\Services\Storage\DTOs\CleanupResult(
            filesScanned: 10,
            filesDeleted: 5,
            bytesFreed: 1024 * 1024, // 1MB
            deletedFiles: ['file1.txt', 'file2.txt'],
            errors: [],
            dryRun: false
        );
        
        $array = $result->toArray();
        
        $this->assertEquals(10, $array['files_scanned']);
        $this->assertEquals(5, $array['files_deleted']);
        $this->assertEquals(1024 * 1024, $array['bytes_freed']);
        $this->assertEquals(1.0, $array['bytes_freed_mb']);
        $this->assertFalse($array['dry_run']);
        $this->assertTrue($array['successful']);
    }

    /**
     * Test getDatabaseReferences returns empty for unknown type.
     */
    public function test_get_database_references_returns_empty_for_unknown_type(): void
    {
        $references = $this->cleanupService->getDatabaseReferences('unknown_type');
        
        $this->assertCount(0, $references);
    }

    /**
     * Helper to set file modification time for testing.
     */
    protected function setFileModificationTime(string $disk, string $path, Carbon $time): void
    {
        $fullPath = Storage::disk($disk)->path($path);
        if (file_exists($fullPath)) {
            touch($fullPath, $time->timestamp);
        }
    }
}
