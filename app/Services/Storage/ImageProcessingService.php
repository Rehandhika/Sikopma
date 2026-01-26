<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\ProcessedImage;
use App\Services\Storage\DTOs\ValidationResult;
use App\Services\Storage\Exceptions\FileProcessingException;
use App\Services\Storage\Exceptions\FileValidationException;
use Illuminate\Http\UploadedFile;

/**
 * ImageProcessingService - Menangani validasi, resize, dan konversi format gambar.
 * 
 * Bertanggung jawab untuk:
 * - Validasi file gambar (tipe, ukuran, MIME content)
 * - Resize gambar yang terlalu besar
 * - Konversi ke format WebP untuk optimasi
 * - Generate size variants (thumbnail, medium, large, etc.)
 * - Support JPEG, PNG, GIF, WebP input formats
 */
class ImageProcessingService implements ImageProcessingServiceInterface
{
    /**
     * Supported image MIME types.
     */
    protected const SUPPORTED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * MIME type to GD image type mapping.
     */
    protected const MIME_TO_IMAGETYPE = [
        'image/jpeg' => IMAGETYPE_JPEG,
        'image/png' => IMAGETYPE_PNG,
        'image/gif' => IMAGETYPE_GIF,
        'image/webp' => IMAGETYPE_WEBP,
    ];

    /**
     * {@inheritdoc}
     */
    public function validate(UploadedFile $file, string $type): ValidationResult
    {
        $errors = [];
        $config = $this->getTypeConfig($type);
        
        if (!$config) {
            $errors[] = __('filestorage.validation.invalid_file_type', [
                'type' => $type,
                'valid_types' => implode(', ', array_keys(config('filestorage.types', [])))
            ]);
            return ValidationResult::invalid($errors);
        }

        // Check file size
        $maxSize = $config['max_size'] ?? 5 * 1024 * 1024;
        $fileSize = $file->getSize();
        
        if ($fileSize > $maxSize) {
            $maxMB = round($maxSize / (1024 * 1024), 2);
            $errors[] = __('filestorage.validation.file_too_large', ['max' => $maxMB]);
        }

        // Check MIME type
        $allowedMimes = $config['allowed_mimes'] ?? [];
        $declaredMime = $file->getMimeType();
        
        if (!empty($allowedMimes) && !in_array($declaredMime, $allowedMimes, true)) {
            $errors[] = __('filestorage.validation.invalid_type', [
                'types' => implode(', ', $this->formatMimeTypes($allowedMimes))
            ]);
        }

        // Validate actual content for images
        if ($this->isImageMime($declaredMime)) {
            $tempPath = $file->getRealPath();
            
            if (!$this->isValidImage($tempPath)) {
                $errors[] = __('filestorage.validation.invalid_image');
            } elseif (config('filestorage.security.validate_mime_content', true)) {
                // Validate actual MIME matches declared using magic bytes
                if (!$this->validateMimeContent($tempPath, $declaredMime)) {
                    $errors[] = __('filestorage.validation.mime_mismatch');
                    
                    // Log security event
                    \Log::channel('security')->warning('MIME content mismatch detected', [
                        'declared_mime' => $declaredMime,
                        'filename' => $file->getClientOriginalName(),
                        'ip' => request()->ip(),
                    ]);
                }
            }
        }

        if (!empty($errors)) {
            return ValidationResult::invalid($errors);
        }

        return ValidationResult::valid($declaredMime, $fileSize);
    }

    /**
     * Validate that file content matches declared MIME type using magic bytes.
     *
     * @param string $filePath Path to the file
     * @param string $declaredMime Declared MIME type
     * @return bool True if content matches declared MIME
     */
    protected function validateMimeContent(string $filePath, string $declaredMime): bool
    {
        $actualMime = $this->detectMimeType($filePath);
        
        if ($actualMime === null) {
            // Cannot detect, use PHP's finfo as fallback
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $actualMime = $finfo->file($filePath);
        }

        // Normalize MIME types for comparison
        $declaredMime = $this->normalizeMime($declaredMime);
        $actualMime = $this->normalizeMime($actualMime);

        return $declaredMime === $actualMime;
    }

    /**
     * Normalize MIME type for comparison.
     */
    protected function normalizeMime(?string $mime): string
    {
        if ($mime === null) {
            return '';
        }

        $mime = strtolower(trim($mime));
        
        // Handle common variations
        $normalizations = [
            'image/jpg' => 'image/jpeg',
            'image/pjpeg' => 'image/jpeg',
        ];

        return $normalizations[$mime] ?? $mime;
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $sourcePath, string $type, string $targetPath): ProcessedImage
    {
        if (!file_exists($sourcePath)) {
            throw FileProcessingException::resizeFailed($sourcePath);
        }

        $config = $this->getTypeConfig($type);
        if (!$config) {
            throw new FileValidationException(
                __('filestorage.validation.invalid_file_type', [
                    'type' => $type,
                    'valid_types' => implode(', ', array_keys(config('filestorage.types', [])))
                ])
            );
        }

        // Check if we should preserve original without any processing
        $preserveOriginal = $config['preserve_original'] ?? false;
        
        // Get original image info
        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw FileValidationException::invalidImage();
        }

        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];
        $imageType = $imageInfo[2];

        // If preserve_original is true, just copy the file without processing
        if ($preserveOriginal) {
            // Ensure target directory exists
            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Copy original file without any modification
            copy($sourcePath, $targetPath);
            
            $fileSize = filesize($targetPath);
            $outputFormat = $this->getFormatFromImageType($imageType);
            $outputMime = $this->getMimeFromImageType($imageType);

            return new ProcessedImage(
                path: $targetPath,
                width: $origWidth,
                height: $origHeight,
                size: $fileSize,
                mimeType: $outputMime,
                format: $outputFormat,
                wasConverted: false,
                wasResized: false
            );
        }

        // Create source image
        $sourceImage = $this->createImageFromFile($sourcePath, $imageType);
        if ($sourceImage === false) {
            throw FileProcessingException::resizeFailed($sourcePath);
        }

        try {
            $wasResized = false;
            $wasConverted = false;
            $processedImage = $sourceImage;
            $newWidth = $origWidth;
            $newHeight = $origHeight;

            // Resize if exceeds max width
            $maxWidth = config('filestorage.image.max_width', 1920);
            if ($origWidth > $maxWidth) {
                $ratio = $maxWidth / $origWidth;
                $newWidth = $maxWidth;
                $newHeight = (int) round($origHeight * $ratio);

                $resized = imagecreatetruecolor($newWidth, $newHeight);
                $this->preserveTransparency($resized);
                
                imagecopyresampled(
                    $resized, $sourceImage,
                    0, 0, 0, 0,
                    $newWidth, $newHeight, $origWidth, $origHeight
                );

                if ($processedImage !== $sourceImage) {
                    imagedestroy($processedImage);
                }
                $processedImage = $resized;
                $wasResized = true;
            }

            // Ensure target directory exists
            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Determine output format
            $convertToWebp = $config['convert_to_webp'] ?? false;
            $quality = config('filestorage.image.default_quality', 85);
            $outputFormat = 'jpeg';
            $outputMime = 'image/jpeg';

            if ($convertToWebp && function_exists('imagewebp')) {
                // Convert to WebP
                $targetPath = $this->changeExtension($targetPath, 'webp');
                imagewebp($processedImage, $targetPath, $quality);
                $outputFormat = 'webp';
                $outputMime = 'image/webp';
                $wasConverted = ($imageType !== IMAGETYPE_WEBP);
            } else {
                // Keep original format or convert to JPEG
                $this->saveImage($processedImage, $targetPath, $imageType, $quality);
                $outputFormat = $this->getFormatFromImageType($imageType);
                $outputMime = $this->getMimeFromImageType($imageType);
            }

            // Get final file size
            $fileSize = filesize($targetPath);

            return new ProcessedImage(
                path: $targetPath,
                width: $newWidth,
                height: $newHeight,
                size: $fileSize,
                mimeType: $outputMime,
                format: $outputFormat,
                wasConverted: $wasConverted,
                wasResized: $wasResized
            );
        } finally {
            // Clean up
            if (isset($processedImage) && $processedImage !== $sourceImage) {
                imagedestroy($processedImage);
            }
            imagedestroy($sourceImage);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function generateVariants(string $originalPath, string $type, string $baseTargetPath): array
    {
        if (!file_exists($originalPath)) {
            throw FileProcessingException::variantFailed($originalPath, 'all');
        }

        $config = $this->getTypeConfig($type);
        if (!$config) {
            return [];
        }

        $variants = $config['variants'] ?? [];
        if (empty($variants)) {
            return [];
        }

        // Get original image info
        $imageInfo = @getimagesize($originalPath);
        if ($imageInfo === false) {
            throw FileValidationException::invalidImage();
        }

        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];
        $imageType = $imageInfo[2];

        // Create source image
        $sourceImage = $this->createImageFromFile($originalPath, $imageType);
        if ($sourceImage === false) {
            throw FileProcessingException::variantFailed($originalPath, 'all');
        }

        $results = [];
        $convertToWebp = $config['convert_to_webp'] ?? false;

        try {
            foreach ($variants as $variantName => $variantConfig) {
                try {
                    $result = $this->generateSingleVariant(
                        $sourceImage,
                        $origWidth,
                        $origHeight,
                        $imageType,
                        $variantName,
                        $variantConfig,
                        $baseTargetPath,
                        $convertToWebp
                    );
                    
                    if ($result !== null) {
                        $results[$variantName] = $result;
                    }
                } catch (\Exception $e) {
                    // Log error but continue with other variants
                    \Log::warning("Failed to generate variant {$variantName}: " . $e->getMessage());
                }
            }
        } finally {
            imagedestroy($sourceImage);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidImage(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            return false;
        }

        // Check if it's a supported image type
        $supportedTypes = [
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_GIF,
            IMAGETYPE_WEBP,
        ];

        return in_array($imageInfo[2], $supportedTypes, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedFormats(): array
    {
        return self::SUPPORTED_MIMES;
    }

    /**
     * Generate a single variant.
     * 
     * Note: quality 100 = maximum quality (minimal compression for JPEG/WebP)
     */
    protected function generateSingleVariant(
        $sourceImage,
        int $origWidth,
        int $origHeight,
        int $imageType,
        string $variantName,
        array $variantConfig,
        string $baseTargetPath,
        bool $convertToWebp
    ): ?ProcessedImage {
        $targetWidth = $variantConfig['width'] ?? null;
        $targetHeight = $variantConfig['height'] ?? null;
        $quality = $variantConfig['quality'] ?? config('filestorage.image.default_quality', 85);

        // Calculate new dimensions
        [$newWidth, $newHeight] = $this->calculateDimensions(
            $origWidth,
            $origHeight,
            $targetWidth,
            $targetHeight
        );

        // Create variant image
        $variant = imagecreatetruecolor($newWidth, $newHeight);
        $this->preserveTransparency($variant);

        imagecopyresampled(
            $variant, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight, $origWidth, $origHeight
        );

        // Determine variant path
        $pathInfo = pathinfo($baseTargetPath);
        $variantDir = $pathInfo['dirname'] . '/' . $variantName;
        $filename = $pathInfo['filename'];
        
        if (!is_dir($variantDir)) {
            mkdir($variantDir, 0755, true);
        }

        // Determine output format and save
        $outputFormat = 'jpeg';
        $outputMime = 'image/jpeg';
        $wasConverted = false;

        if ($convertToWebp && function_exists('imagewebp')) {
            $variantPath = $variantDir . '/' . $filename . '.webp';
            imagewebp($variant, $variantPath, $quality);
            $outputFormat = 'webp';
            $outputMime = 'image/webp';
            $wasConverted = ($imageType !== IMAGETYPE_WEBP);
        } else {
            $extension = $this->getExtensionFromImageType($imageType);
            $variantPath = $variantDir . '/' . $filename . '.' . $extension;
            $this->saveImage($variant, $variantPath, $imageType, $quality);
            $outputFormat = $this->getFormatFromImageType($imageType);
            $outputMime = $this->getMimeFromImageType($imageType);
        }

        imagedestroy($variant);

        $fileSize = filesize($variantPath);

        return new ProcessedImage(
            path: $variantPath,
            width: $newWidth,
            height: $newHeight,
            size: $fileSize,
            mimeType: $outputMime,
            format: $outputFormat,
            wasConverted: $wasConverted,
            wasResized: true
        );
    }

    /**
     * Calculate new dimensions maintaining aspect ratio.
     *
     * @return array{0: int, 1: int} [width, height]
     */
    protected function calculateDimensions(
        int $origWidth,
        int $origHeight,
        ?int $targetWidth,
        ?int $targetHeight
    ): array {
        // If both dimensions specified, use contain mode (fit within bounds)
        if ($targetWidth !== null && $targetHeight !== null) {
            $ratio = min($targetWidth / $origWidth, $targetHeight / $origHeight);
            return [
                (int) round($origWidth * $ratio),
                (int) round($origHeight * $ratio),
            ];
        }

        // If only width specified, calculate height maintaining ratio
        if ($targetWidth !== null) {
            $ratio = $targetWidth / $origWidth;
            return [
                $targetWidth,
                (int) round($origHeight * $ratio),
            ];
        }

        // If only height specified, calculate width maintaining ratio
        if ($targetHeight !== null) {
            $ratio = $targetHeight / $origHeight;
            return [
                (int) round($origWidth * $ratio),
                $targetHeight,
            ];
        }

        // No resize needed
        return [$origWidth, $origHeight];
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
     * Save image to file based on type.
     */
    protected function saveImage($image, string $path, int $imageType, int $quality): bool
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, $quality),
            IMAGETYPE_PNG => imagepng($image, $path, (int) (9 - ($quality / 100 * 9))),
            IMAGETYPE_GIF => imagegif($image, $path),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($image, $path, $quality) : imagejpeg($image, $path, $quality),
            default => imagejpeg($image, $path, $quality),
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
     * Get type configuration.
     */
    protected function getTypeConfig(string $type): ?array
    {
        return config("filestorage.types.{$type}");
    }

    /**
     * Check if MIME type is an image.
     */
    protected function isImageMime(string $mime): bool
    {
        return str_starts_with($mime, 'image/');
    }

    /**
     * Detect actual MIME type from file content.
     */
    protected function detectMimeType(string $path): ?string
    {
        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            return null;
        }

        return match ($imageInfo[2]) {
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png',
            IMAGETYPE_GIF => 'image/gif',
            IMAGETYPE_WEBP => 'image/webp',
            default => null,
        };
    }

    /**
     * Change file extension.
     */
    protected function changeExtension(string $path, string $newExtension): string
    {
        $pathInfo = pathinfo($path);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        return $directory . '/' . $filename . '.' . $newExtension;
    }

    /**
     * Get format name from image type.
     */
    protected function getFormatFromImageType(int $imageType): string
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => 'jpeg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_WEBP => 'webp',
            default => 'jpeg',
        };
    }

    /**
     * Get MIME type from image type.
     */
    protected function getMimeFromImageType(int $imageType): string
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png',
            IMAGETYPE_GIF => 'image/gif',
            IMAGETYPE_WEBP => 'image/webp',
            default => 'image/jpeg',
        };
    }

    /**
     * Get file extension from image type.
     */
    protected function getExtensionFromImageType(int $imageType): string
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_WEBP => 'webp',
            default => 'jpg',
        };
    }

    /**
     * Format MIME types for display.
     */
    protected function formatMimeTypes(array $mimes): array
    {
        return array_map(function ($mime) {
            return match ($mime) {
                'image/jpeg' => 'JPG',
                'image/png' => 'PNG',
                'image/gif' => 'GIF',
                'image/webp' => 'WebP',
                'application/pdf' => 'PDF',
                default => strtoupper(str_replace('image/', '', $mime)),
            };
        }, $mimes);
    }
}
