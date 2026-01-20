<?php

namespace Tests\Unit\Services\Storage;

use App\Services\Storage\DTOs\PathInfo;
use App\Services\Storage\Exceptions\FileValidationException;
use App\Services\Storage\StorageOrganizer;
use Tests\TestCase;

class StorageOrganizerTest extends TestCase
{
    protected StorageOrganizer $organizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizer = new StorageOrganizer();
    }

    /**
     * Test generatePath creates correct format.
     */
    public function test_generate_path_creates_correct_format(): void
    {
        $path = $this->organizer->generatePath('product', 'webp');
        
        // Should match pattern: product/YYYY/MM/uuid.webp
        $this->assertMatchesRegularExpression(
            '/^product\/\d{4}\/\d{2}\/[a-f0-9-]{36}\.webp$/',
            $path
        );
    }

    /**
     * Test generatePath with day option for attendance.
     */
    public function test_generate_path_with_day_for_attendance(): void
    {
        $path = $this->organizer->generatePath('attendance', 'webp', ['day' => 15]);
        
        // Should match pattern: attendance/YYYY/MM/DD/uuid.webp
        $this->assertMatchesRegularExpression(
            '/^attendance\/\d{4}\/\d{2}\/15\/[a-f0-9-]{36}\.webp$/',
            $path
        );
    }

    /**
     * Test generatePath pads single digit day.
     */
    public function test_generate_path_pads_single_digit_day(): void
    {
        $path = $this->organizer->generatePath('attendance', 'webp', ['day' => 5]);
        
        // Should have padded day: 05
        $this->assertMatchesRegularExpression(
            '/^attendance\/\d{4}\/\d{2}\/05\/[a-f0-9-]{36}\.webp$/',
            $path
        );
    }

    /**
     * Test generatePath throws exception for invalid type.
     */
    public function test_generate_path_throws_exception_for_invalid_type(): void
    {
        $this->expectException(FileValidationException::class);
        
        $this->organizer->generatePath('invalid_type', 'webp');
    }

    /**
     * Test generatePath sanitizes extension.
     */
    public function test_generate_path_sanitizes_extension(): void
    {
        $path = $this->organizer->generatePath('product', '.WEBP');
        
        // Extension should be lowercase without leading dot
        $this->assertStringEndsWith('.webp', $path);
    }

    /**
     * Test getVariantPath creates correct path.
     */
    public function test_get_variant_path_creates_correct_path(): void
    {
        $originalPath = 'product/2026/01/abc-123.webp';
        $variantPath = $this->organizer->getVariantPath($originalPath, 'thumbnail');
        
        $this->assertEquals('product/2026/01/thumbnail/abc-123.webp', $variantPath);
    }

    /**
     * Test getVariantPath with day component.
     */
    public function test_get_variant_path_with_day_component(): void
    {
        $originalPath = 'attendance/2026/01/15/abc-123.webp';
        $variantPath = $this->organizer->getVariantPath($originalPath, 'thumbnail');
        
        $this->assertEquals('attendance/2026/01/15/thumbnail/abc-123.webp', $variantPath);
    }

    /**
     * Test getThumbnailPath creates correct path.
     */
    public function test_get_thumbnail_path_creates_correct_path(): void
    {
        $originalPath = 'product/2026/01/abc-123.webp';
        $thumbnailPath = $this->organizer->getThumbnailPath($originalPath, 150, 150);
        
        $this->assertEquals('product/2026/01/thumbnails/abc-123_150x150.webp', $thumbnailPath);
    }

    /**
     * Test getThumbnailPath with day component.
     */
    public function test_get_thumbnail_path_with_day_component(): void
    {
        $originalPath = 'attendance/2026/01/15/abc-123.webp';
        $thumbnailPath = $this->organizer->getThumbnailPath($originalPath, 80, 80);
        
        $this->assertEquals('attendance/2026/01/15/thumbnails/abc-123_80x80.webp', $thumbnailPath);
    }

    /**
     * Test parsePath extracts correct metadata.
     */
    public function test_parse_path_extracts_correct_metadata(): void
    {
        $path = 'product/2026/01/abc-123.webp';
        $pathInfo = $this->organizer->parsePath($path);
        
        $this->assertInstanceOf(PathInfo::class, $pathInfo);
        $this->assertEquals('product', $pathInfo->type);
        $this->assertEquals('2026', $pathInfo->year);
        $this->assertEquals('01', $pathInfo->month);
        $this->assertEquals('abc-123', $pathInfo->filename);
        $this->assertEquals('webp', $pathInfo->extension);
        $this->assertNull($pathInfo->variant);
        $this->assertNull($pathInfo->day);
    }

    /**
     * Test parsePath with day component.
     */
    public function test_parse_path_with_day_component(): void
    {
        $path = 'attendance/2026/01/15/abc-123.webp';
        $pathInfo = $this->organizer->parsePath($path);
        
        $this->assertEquals('attendance', $pathInfo->type);
        $this->assertEquals('2026', $pathInfo->year);
        $this->assertEquals('01', $pathInfo->month);
        $this->assertEquals('15', $pathInfo->day);
        $this->assertEquals('abc-123', $pathInfo->filename);
        $this->assertEquals('webp', $pathInfo->extension);
    }

    /**
     * Test parsePath with variant.
     */
    public function test_parse_path_with_variant(): void
    {
        $path = 'product/2026/01/thumbnail/abc-123.webp';
        $pathInfo = $this->organizer->parsePath($path);
        
        $this->assertEquals('product', $pathInfo->type);
        $this->assertEquals('thumbnail', $pathInfo->variant);
        $this->assertEquals('abc-123', $pathInfo->filename);
    }

    /**
     * Test parsePath with day and variant.
     */
    public function test_parse_path_with_day_and_variant(): void
    {
        $path = 'attendance/2026/01/15/thumbnail/abc-123.webp';
        $pathInfo = $this->organizer->parsePath($path);
        
        $this->assertEquals('attendance', $pathInfo->type);
        $this->assertEquals('15', $pathInfo->day);
        $this->assertEquals('thumbnail', $pathInfo->variant);
        $this->assertEquals('abc-123', $pathInfo->filename);
    }

    /**
     * Test parsePath throws exception for invalid path.
     */
    public function test_parse_path_throws_exception_for_invalid_path(): void
    {
        $this->expectException(FileValidationException::class);
        
        $this->organizer->parsePath('invalid/path');
    }

    /**
     * Test sanitizeFilename removes path traversal.
     */
    public function test_sanitize_filename_removes_path_traversal(): void
    {
        $dangerous = '../../../etc/passwd';
        $sanitized = $this->organizer->sanitizeFilename($dangerous);
        
        $this->assertStringNotContainsString('..', $sanitized);
        $this->assertStringNotContainsString('/', $sanitized);
    }

    /**
     * Test sanitizeFilename removes null bytes.
     */
    public function test_sanitize_filename_removes_null_bytes(): void
    {
        $dangerous = "file\0name.txt";
        $sanitized = $this->organizer->sanitizeFilename($dangerous);
        
        $this->assertStringNotContainsString("\0", $sanitized);
    }

    /**
     * Test sanitizeFilename removes dangerous characters.
     */
    public function test_sanitize_filename_removes_dangerous_characters(): void
    {
        $dangerous = 'file:name*with?bad<chars>.txt';
        $sanitized = $this->organizer->sanitizeFilename($dangerous);
        
        $this->assertStringNotContainsString(':', $sanitized);
        $this->assertStringNotContainsString('*', $sanitized);
        $this->assertStringNotContainsString('?', $sanitized);
        $this->assertStringNotContainsString('<', $sanitized);
        $this->assertStringNotContainsString('>', $sanitized);
    }

    /**
     * Test sanitizeFilename generates UUID for empty result.
     */
    public function test_sanitize_filename_generates_uuid_for_empty_result(): void
    {
        $dangerous = '../../../';
        $sanitized = $this->organizer->sanitizeFilename($dangerous);
        
        // Should be a valid UUID
        $this->assertMatchesRegularExpression('/^[a-f0-9-]{36}$/', $sanitized);
    }

    /**
     * Test isValidType returns true for valid types.
     */
    public function test_is_valid_type_returns_true_for_valid_types(): void
    {
        $this->assertTrue($this->organizer->isValidType('product'));
        $this->assertTrue($this->organizer->isValidType('banner'));
        $this->assertTrue($this->organizer->isValidType('attendance'));
        $this->assertTrue($this->organizer->isValidType('profile'));
        $this->assertTrue($this->organizer->isValidType('leave'));
        $this->assertTrue($this->organizer->isValidType('report'));
    }

    /**
     * Test isValidType returns false for invalid types.
     */
    public function test_is_valid_type_returns_false_for_invalid_types(): void
    {
        $this->assertFalse($this->organizer->isValidType('invalid'));
        $this->assertFalse($this->organizer->isValidType(''));
        $this->assertFalse($this->organizer->isValidType('PRODUCT')); // Case sensitive
    }

    /**
     * Test getValidTypes returns all configured types.
     */
    public function test_get_valid_types_returns_all_configured_types(): void
    {
        $types = $this->organizer->getValidTypes();
        
        $this->assertContains('product', $types);
        $this->assertContains('banner', $types);
        $this->assertContains('attendance', $types);
        $this->assertContains('profile', $types);
        $this->assertContains('leave', $types);
        $this->assertContains('report', $types);
    }

    /**
     * Test parsePath sanitizes path traversal attempts.
     */
    public function test_parse_path_sanitizes_path_traversal(): void
    {
        // This should sanitize the path before parsing
        $path = '../product/2026/01/abc-123.webp';
        
        // After sanitization, it should be: product/2026/01/abc-123.webp
        $pathInfo = $this->organizer->parsePath($path);
        
        $this->assertEquals('product', $pathInfo->type);
    }
}
