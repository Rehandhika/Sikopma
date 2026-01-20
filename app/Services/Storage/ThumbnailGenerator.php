<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\BatchResult;
use App\Services\Storage\DTOs\ProcessedImage;
use App\Services\Storage\Exceptions\FileNotFoundException;
use App\Services\Storage\Exceptions\FileProcessingException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * ThumbnailGenerator - Menangani pembuatan thumbnail on-demand dan batch processing.
 * 
 * Bertanggung jawab untuk:
 * - Generate WebP thumbnails on-demand saat pertama kali diminta
 * - Return cached version jika thumbnail sudah ada
 * - Support configurable thumbnail sizes per file type
 * - Use cover mode cropping untuk consistent dimensions
 * - Store thumbnails di {original_path}/thumbnails/{filename}_{width}x{height}.webp
 * - Fallback ke original image URL jika generation gagal
 */
class ThumbnailGenerator implements ThumbnailGeneratorInterface
{
    /**
     * Default thumbnail quality.
     */
    protected const DEFAULT_QUALITY = 75;

    /**
     * StorageOrganizer instance.
     */
    protected StorageOrganizerInterface $storageOrganizer;

    public function __construct(StorageOrganizerInterface $storageOrganizer)
    {
        $this->storageOrganizer = $storageOrganizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getThumbnailUrl(string $originalPath, int $width, int $height, ?string $disk = null): string
    {
        $disk = $disk ?? $this->detectDisk($originalPath);
        $thumbnailPath = $this->getThumbnailPath($originalPath, $width, $height);

        // Return cached thumbnail if exists
        if (Storage::disk($disk)->exists($thumbnailPath)) {
            return Storage::disk($disk)->url($thumbnailPath);
        }

        // Generate thumbnail on-demand
        try {
            $this->generate($originalPath, $width, $height, $disk);
            return Storage::disk($disk)->url($thumbnailPath);
        } catch (\Exception $e) {
            Log::warning("Thumbnail generation failed for {$originalPath}: " . $e->getMessage());
            // Fallback to original if thumbnail generation fails
            return Storage::disk($disk)->url($originalPath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $originalPath, int $width, int $height, ?string $disk = null): ProcessedImage
    {
        $disk = $disk ?? $this->detectDisk($originalPath);
        $fullPath = Storage::disk($disk)->path($originalPath);

        if (!file_exists($fullPath)) {
            throw FileNotFoundException::forPath($originalPath);
        }

        // Validate image
        $imageInfo = @getimagesize($fullPath);
        if ($imageInfo === false) {
            throw FileProcessingException::resizeFailed($originalPath);
        }

        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];
        $imageType = $imageInfo[2];

        // Create source image
        $sourceImage = $this->createImageFromFile($fullPath, $imageType);
        if ($sourceImage === false) {
            throw FileProcessingException::resizeFailed($originalPath);
        }

        try {
            // Calculate crop dimensions (cover mode - fill entire thumbnail)
            [$srcX, $srcY, $srcWidth, $srcHeight] = $this->calculateCoverCrop(
                $origWidth,
                $origHeight,
                $width,
                $height
            );

            // Create thumbnail
            $thumbnail = imagecreatetruecolor($width, $height);
            $this->preserveTransparency($thumbnail);

            // Resample with high quality
            imagecopyresampled(
                $thumbnail, $sourceImage,
                0, 0, $srcX, $srcY,
                $width, $height, $srcWidth, $srcHeight
            );

            // Get thumbnail path
            $thumbnailPath = $this->getThumbnailPath($originalPath, $width, $height);
            $thumbnailFullPath = Storage::disk($disk)->path($thumbnailPath);

            // Create thumbnail directory if not exists
            $thumbnailDir = dirname($thumbnailFullPath);
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Save as WebP
            $quality = config('filestorage.image.default_quality', self::DEFAULT_QUALITY);
            imagewebp($thumbnail, $thumbnailFullPath, $quality);

            // Get file size
            $fileSize = filesize($thumbnailFullPath);

            return new ProcessedImage(
                path: $thumbnailPath,
                width: $width,
                height: $height,
                size: $fileSize,
                mimeType: 'image/webp',
                format: 'webp',
                wasConverted: ($imageType !== IMAGETYPE_WEBP),
                wasResized: true
            );
        } finally {
            // Free memory
            if (isset($thumbnail)) {
                imagedestroy($thumbnail);
            }
            imagedestroy($sourceImage);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateBatch(array $paths, int $width, int $height, ?string $disk = null): BatchResult
    {
        $results = [];

        foreach ($paths as $path) {
            try {
                $processedImage = $this->generate($path, $width, $height, $disk);
                $results[] = [
                    'path' => $path,
                    'thumbnail_path' => $processedImage->path,
                    'success' => true,
                    'error' => null,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'path' => $path,
                    'thumbnail_path' => null,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return BatchResult::fromResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $originalPath, int $width, int $height, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->detectDisk($originalPath);
        $thumbnailPath = $this->getThumbnailPath($originalPath, $width, $height);

        return Storage::disk($disk)->exists($thumbnailPath);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $originalPath, int $width, int $height, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->detectDisk($originalPath);
        $thumbnailPath = $this->getThumbnailPath($originalPath, $width, $height);

        if (Storage::disk($disk)->exists($thumbnailPath)) {
            return Storage::disk($disk)->delete($thumbnailPath);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll(string $originalPath, ?string $disk = null): int
    {
        $disk = $disk ?? $this->detectDisk($originalPath);
        
        // Get thumbnails directory
        $pathInfo = pathinfo($originalPath);
        $thumbnailsDir = $pathInfo['dirname'] . '/thumbnails';
        $filename = $pathInfo['filename'];

        $deleted = 0;

        if (Storage::disk($disk)->exists($thumbnailsDir)) {
            $files = Storage::disk($disk)->files($thumbnailsDir);
            
            foreach ($files as $file) {
                // Check if file belongs to this original (starts with filename_)
                $thumbFilename = basename($file);
                if (str_starts_with($thumbFilename, $filename . '_')) {
                    if (Storage::disk($disk)->delete($file)) {
                        $deleted++;
                    }
                }
            }
        }

        return $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function getThumbnailPath(string $originalPath, int $width, int $height): string
    {
        return $this->storageOrganizer->getThumbnailPath($originalPath, $width, $height);
    }

    /**
     * Calculate cover crop dimensions.
     * Cover mode fills the entire target area, cropping excess.
     *
     * @return array{0: int, 1: int, 2: int, 3: int} [srcX, srcY, srcWidth, srcHeight]
     */
    protected function calculateCoverCrop(
        int $origWidth,
        int $origHeight,
        int $targetWidth,
        int $targetHeight
    ): array {
        $ratio = max($targetWidth / $origWidth, $targetHeight / $origHeight);
        
        $newWidth = (int) ($origWidth * $ratio);
        $newHeight = (int) ($origHeight * $ratio);
        
        $srcX = (int) (($newWidth - $targetWidth) / 2 / $ratio);
        $srcY = (int) (($newHeight - $targetHeight) / 2 / $ratio);
        $srcWidth = (int) ($targetWidth / $ratio);
        $srcHeight = (int) ($targetHeight / $ratio);

        return [$srcX, $srcY, $srcWidth, $srcHeight];
    }

    /**
     * Create GD image resource from file.
     *
     * @return \GdImage|false
     */
    protected function createImageFromFile(string $path, int $imageType)
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    /**
     * Preserve transparency for PNG/GIF images.
     */
    protected function preserveTransparency($image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
    }

    /**
     * Detect storage disk from path.
     * Defaults to 'public' if cannot determine.
     */
    protected function detectDisk(string $path): string
    {
        // Try to parse path and get type
        try {
            $pathInfo = $this->storageOrganizer->parsePath($path);
            $typeConfig = config("filestorage.types.{$pathInfo->type}");
            
            if ($typeConfig && isset($typeConfig['disk'])) {
                return $typeConfig['disk'];
            }
        } catch (\Exception $e) {
            // Cannot parse path, use default
        }

        return 'public';
    }
}
