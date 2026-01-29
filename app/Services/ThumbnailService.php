<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ThumbnailService
{
    /**
     * Generate WebP thumbnail for attendance photos
     * Uses native PHP GD - no external dependencies
     * Optimized for minimal bandwidth and fast loading
     */
    public static function getThumbnailUrl(string $originalPath, int $width = 80, int $height = 80): string
    {
        $thumbnailPath = self::getThumbnailPath($originalPath, $width, $height);

        // Return cached thumbnail if exists
        if (Storage::exists($thumbnailPath)) {
            return Storage::url($thumbnailPath);
        }

        // Generate thumbnail on-demand
        try {
            self::generateThumbnail($originalPath, $thumbnailPath, $width, $height);

            return Storage::url($thumbnailPath);
        } catch (\Exception $e) {
            // Fallback to original if thumbnail generation fails
            return Storage::url($originalPath);
        }
    }

    /**
     * Generate thumbnail path based on original
     */
    private static function getThumbnailPath(string $originalPath, int $width, int $height): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];

        return "{$directory}/thumbnails/{$filename}_{$width}x{$height}.webp";
    }

    /**
     * Generate WebP thumbnail using native PHP GD
     * Supports JPEG, PNG, GIF, WebP input formats
     */
    private static function generateThumbnail(string $originalPath, string $thumbnailPath, int $width, int $height): void
    {
        $fullPath = Storage::path($originalPath);

        if (! file_exists($fullPath)) {
            throw new \Exception("Original file not found: {$originalPath}");
        }

        // Detect image type and create source image
        $imageInfo = getimagesize($fullPath);
        if ($imageInfo === false) {
            throw new \Exception("Invalid image file: {$originalPath}");
        }

        $sourceImage = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($fullPath),
            IMAGETYPE_PNG => imagecreatefrompng($fullPath),
            IMAGETYPE_GIF => imagecreatefromgif($fullPath),
            IMAGETYPE_WEBP => imagecreatefromwebp($fullPath),
            default => throw new \Exception('Unsupported image type'),
        };

        if ($sourceImage === false) {
            throw new \Exception('Failed to create image from file');
        }

        // Get original dimensions
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Calculate crop dimensions (cover mode - fill entire thumbnail)
        $ratio = max($width / $origWidth, $height / $origHeight);
        $newWidth = (int) ($origWidth * $ratio);
        $newHeight = (int) ($origHeight * $ratio);
        $srcX = (int) (($newWidth - $width) / 2 / $ratio);
        $srcY = (int) (($newHeight - $height) / 2 / $ratio);
        $srcWidth = (int) ($width / $ratio);
        $srcHeight = (int) ($height / $ratio);

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($width, $height);

        // Preserve transparency for PNG
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);

        // Resample with high quality
        imagecopyresampled(
            $thumbnail, $sourceImage,
            0, 0, $srcX, $srcY,
            $width, $height, $srcWidth, $srcHeight
        );

        // Create thumbnail directory if not exists
        $thumbnailDir = dirname(Storage::path($thumbnailPath));
        if (! is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // Save as WebP with 75% quality (good balance of size/quality)
        imagewebp($thumbnail, Storage::path($thumbnailPath), 75);

        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
    }

    /**
     * Batch generate thumbnails for existing photos
     * Run via artisan command for initial setup
     */
    public static function generateBatchThumbnails(array $paths, int $width = 80, int $height = 80): array
    {
        $results = ['success' => 0, 'failed' => 0];

        foreach ($paths as $path) {
            try {
                self::getThumbnailUrl($path, $width, $height);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }
}
