<?php

namespace App\Services\Storage;

use App\Services\Storage\DTOs\FileResult;
use App\Services\Storage\Exceptions\FileProcessingException;
use App\Services\Storage\Exceptions\FileStorageException;
use App\Services\Storage\Exceptions\FileValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * FileStorageService - Entry point utama untuk semua operasi penyimpanan file.
 *
 * Bertanggung jawab untuk:
 * - Upload file dengan validasi dan processing otomatis
 * - Upload dari base64 (camera capture)
 * - Get URL dengan caching
 * - Delete file beserta semua variants
 * - Integrasi dengan ImageProcessingService, StorageOrganizer, CacheManager
 * - Generate signed URLs untuk private files
 * - Access logging untuk audit
 */
class FileStorageService implements FileStorageServiceInterface
{
    public function __construct(
        protected ImageProcessingServiceInterface $imageProcessor,
        protected StorageOrganizerInterface $storageOrganizer,
        protected CacheManagerInterface $cacheManager,
        protected ThumbnailGeneratorInterface $thumbnailGenerator,
        protected ?FileSecurityServiceInterface $securityService = null
    ) {}

    /**
     * {@inheritdoc}
     */
    public function upload(UploadedFile $file, string $type, array $options = []): FileResult
    {
        // Validate type
        if (! $this->storageOrganizer->isValidType($type)) {
            throw new FileValidationException(
                __('filestorage.validation.invalid_file_type', [
                    'type' => $type,
                    'valid_types' => implode(', ', $this->storageOrganizer->getValidTypes()),
                ])
            );
        }

        $config = $this->getTypeConfig($type);
        $disk = $this->getDiskForType($type);

        // Validate file
        $validationResult = $this->imageProcessor->validate($file, $type);
        if ($validationResult->isFailed()) {
            throw new FileValidationException(
                $validationResult->getFirstError() ?? __('filestorage.validation.file_required'),
                $validationResult->errors
            );
        }

        // Log upload attempt
        $this->logFileAccess('upload_attempt', $type, [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        // Delete old file if provided
        $oldPath = $options['old_path'] ?? null;
        if ($oldPath && $this->exists($oldPath)) {
            $this->delete($oldPath);
        }

        // Determine extension
        $isImage = $this->isImageFile($file);
        $convertToWebp = $config['convert_to_webp'] ?? false;
        $extension = $this->determineExtension($file, $convertToWebp && $isImage);

        // Generate path
        $pathOptions = [];
        if ($type === 'attendance' && isset($options['day'])) {
            $pathOptions['day'] = $options['day'];
        }
        $relativePath = $this->storageOrganizer->generatePath($type, $extension, $pathOptions);

        try {
            // Get full path for processing
            $fullPath = Storage::disk($disk)->path($relativePath);
            $directory = dirname($fullPath);

            // Ensure directory exists
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Process and store file
            $processedImage = null;
            $variants = [];

            if ($isImage && ($convertToWebp || ! empty($config['variants']))) {
                // Process image (resize, convert to WebP if configured)
                $processedImage = $this->imageProcessor->process(
                    $file->getRealPath(),
                    $type,
                    $fullPath
                );

                // Update path if extension changed (e.g., to WebP)
                if ($processedImage->path !== $fullPath) {
                    $relativePath = $this->updatePathExtension($relativePath, $processedImage->format);
                }

                // Generate variants if configured
                if (! empty($config['variants'])) {
                    $variants = $this->generateVariants($processedImage->path, $type, $disk);
                }
            } else {
                // Store file as-is (non-image or no processing needed)
                $file->storeAs(dirname($relativePath), basename($relativePath), $disk);
            }

            // Get file info
            $storedPath = Storage::disk($disk)->path($relativePath);
            $fileSize = file_exists($storedPath) ? filesize($storedPath) : $file->getSize();
            $mimeType = $processedImage ? $processedImage->mimeType : $file->getMimeType();
            $checksum = file_exists($storedPath) ? md5_file($storedPath) : md5_file($file->getRealPath());

            // Generate URL
            $url = Storage::disk($disk)->url($relativePath);

            // Invalidate cache for this path
            $this->cacheManager->invalidate($relativePath);

            // Log upload
            Log::info('FileStorageService: File uploaded', [
                'path' => $relativePath,
                'type' => $type,
                'size' => $fileSize,
                'mime' => $mimeType,
                'variants' => array_keys($variants),
                'user_id' => $options['user_id'] ?? null,
            ]);

            return new FileResult(
                path: $relativePath,
                url: $url,
                variants: $variants,
                size: $fileSize,
                mimeType: $mimeType,
                checksum: $checksum
            );
        } catch (FileValidationException|FileProcessingException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('FileStorageService: Upload failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw FileStorageException::withContext(
                __('filestorage.storage.write_failed'),
                ['type' => $type, 'original_error' => $e->getMessage()]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uploadFromBase64(string $base64, string $type, array $options = []): FileResult
    {
        // Validate type
        if (! $this->storageOrganizer->isValidType($type)) {
            throw new FileValidationException(
                __('filestorage.validation.invalid_file_type', [
                    'type' => $type,
                    'valid_types' => implode(', ', $this->storageOrganizer->getValidTypes()),
                ])
            );
        }

        // Parse base64 data
        $imageData = $this->parseBase64Image($base64);
        if ($imageData === null) {
            throw FileValidationException::invalidImage();
        }

        // Create temporary file
        $tempPath = sys_get_temp_dir().'/'.Str::uuid().'.'.$imageData['extension'];
        file_put_contents($tempPath, $imageData['data']);

        try {
            // Create UploadedFile from temp file
            $uploadedFile = new UploadedFile(
                $tempPath,
                'camera_capture.'.$imageData['extension'],
                $imageData['mime'],
                null,
                true // test mode to skip validation
            );

            // Use regular upload method
            return $this->upload($uploadedFile, $type, $options);
        } finally {
            // Clean up temp file
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(string $path, ?string $size = null): ?string
    {
        try {
            $pathInfo = $this->storageOrganizer->parsePath($path);
            $type = $pathInfo->type;
            $disk = $this->getDiskForType($type);
            $config = $this->getTypeConfig($type);

            // For private files, generate signed URL
            if ($this->isPrivateFile($type)) {
                return $this->getSignedUrl($path, $size);
            }

            // Determine actual path based on size
            $actualPath = $path;
            if ($size !== null && $size !== 'original') {
                // Check if it's a thumbnail request
                if ($size === 'thumbnail' && isset($config['thumbnail'])) {
                    $thumbConfig = $config['thumbnail'];
                    $width = $thumbConfig['width'] ?? 150;
                    $height = $thumbConfig['height'] ?? 150;

                    return $this->thumbnailGenerator->getThumbnailUrl($path, $width, $height, $disk);
                }

                // Check if it's a variant
                if (isset($config['variants'][$size])) {
                    $actualPath = $this->storageOrganizer->getVariantPath($path, $size);
                }
            }

            // Check if file exists before using cache
            if (! Storage::disk($disk)->exists($actualPath)) {
                // Fallback to original if variant doesn't exist
                if ($actualPath !== $path && Storage::disk($disk)->exists($path)) {
                    $actualPath = $path;
                } else {
                    return null;
                }
            }

            // Log file access
            $this->logFileAccess('url_generated', $type, ['path' => $actualPath, 'size' => $size]);

            // Use cache manager to get URL
            return $this->cacheManager->getUrl($actualPath, $size ?? 'original', function () use ($disk, $actualPath) {
                // Use relative URL for public disk to work across different hosts
                if ($disk === 'public') {
                    return '/storage/'.ltrim($actualPath, '/');
                }

                return Storage::disk($disk)->url($actualPath);
            });
        } catch (\Exception $e) {
            Log::warning('FileStorageService: Failed to get URL', [
                'path' => $path,
                'size' => $size,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get signed URL for private file access.
     *
     * @param  string  $path  File path
     * @param  string|null  $size  Size variant
     * @param  int|null  $expirationMinutes  URL expiration in minutes
     * @return string|null Signed URL or null if file doesn't exist
     */
    public function getSignedUrl(string $path, ?string $size = null, ?int $expirationMinutes = null): ?string
    {
        try {
            $pathInfo = $this->storageOrganizer->parsePath($path);
            $type = $pathInfo->type;
            $disk = $this->getDiskForType($type);

            // Determine actual path based on size
            $actualPath = $path;
            if ($size !== null && $size !== 'original') {
                $config = $this->getTypeConfig($type);
                if (isset($config['variants'][$size])) {
                    $actualPath = $this->storageOrganizer->getVariantPath($path, $size);
                }
            }

            // Check if file exists
            if (! Storage::disk($disk)->exists($actualPath)) {
                return null;
            }

            // Log signed URL generation
            $this->logFileAccess('signed_url_generated', $type, [
                'path' => $actualPath,
                'size' => $size,
            ]);

            // Use security service if available
            if ($this->securityService) {
                return $this->securityService->generateSignedUrl($actualPath, $disk, $expirationMinutes);
            }

            // Fallback: generate signed URL manually
            $expirationMinutes = $expirationMinutes ?? config('filestorage.security.signed_url_expiration', 60);

            return \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'file.download',
                now()->addMinutes($expirationMinutes),
                [
                    'path' => base64_encode($actualPath),
                    'disk' => $disk,
                ]
            );
        } catch (\Exception $e) {
            Log::warning('FileStorageService: Failed to generate signed URL', [
                'path' => $path,
                'size' => $size,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if file type uses private storage.
     *
     * @param  string  $type  File type
     * @return bool True if private
     */
    public function isPrivateFile(string $type): bool
    {
        $config = $this->getTypeConfig($type);

        return ($config['disk'] ?? 'public') === 'local';
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): bool
    {
        try {
            $pathInfo = $this->storageOrganizer->parsePath($path);
            $type = $pathInfo->type;
            $disk = $this->getDiskForType($type);
            $config = $this->getTypeConfig($type);

            $deleted = false;

            // Delete original file
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
                $deleted = true;
            }

            // Delete variants
            if (! empty($config['variants'])) {
                foreach (array_keys($config['variants']) as $variantName) {
                    $variantPath = $this->storageOrganizer->getVariantPath($path, $variantName);
                    if (Storage::disk($disk)->exists($variantPath)) {
                        Storage::disk($disk)->delete($variantPath);
                    }
                }
            }

            // Delete thumbnails
            $this->thumbnailGenerator->deleteAll($path, $disk);

            // Invalidate cache
            $this->cacheManager->invalidate($path);

            Log::info('FileStorageService: File deleted', [
                'path' => $path,
                'type' => $type,
            ]);

            return $deleted;
        } catch (\Exception $e) {
            Log::error('FileStorageService: Delete failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        try {
            $pathInfo = $this->storageOrganizer->parsePath($path);
            $disk = $this->getDiskForType($pathInfo->type);

            return Storage::disk($disk)->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDiskForType(string $type): string
    {
        $config = $this->getTypeConfig($type);

        return $config['disk'] ?? 'public';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeConfig(string $type): ?array
    {
        return config("filestorage.types.{$type}");
    }

    /**
     * Generate variants for an image.
     *
     * @param  string  $originalFullPath  Full filesystem path to original image
     * @param  string  $type  File type
     * @param  string  $disk  Storage disk
     * @return array<string, array{path: string, url: string}> Variant info
     */
    protected function generateVariants(string $originalFullPath, string $type, string $disk): array
    {
        $config = $this->getTypeConfig($type);
        if (empty($config['variants'])) {
            return [];
        }

        try {
            // Get relative path from full path
            $diskPath = Storage::disk($disk)->path('');
            $relativePath = str_replace($diskPath, '', $originalFullPath);
            $relativePath = ltrim($relativePath, '/\\');

            // Generate variants using ImageProcessingService
            $processedVariants = $this->imageProcessor->generateVariants(
                $originalFullPath,
                $type,
                $originalFullPath
            );

            $variants = [];
            foreach ($processedVariants as $variantName => $processedImage) {
                // Convert full path to relative path
                $variantRelativePath = str_replace($diskPath, '', $processedImage->path);
                $variantRelativePath = ltrim($variantRelativePath, '/\\');

                $variants[$variantName] = [
                    'path' => $variantRelativePath,
                    'url' => Storage::disk($disk)->url($variantRelativePath),
                    'width' => $processedImage->width,
                    'height' => $processedImage->height,
                    'size' => $processedImage->size,
                ];
            }

            return $variants;
        } catch (\Exception $e) {
            Log::warning('FileStorageService: Variant generation failed', [
                'path' => $originalFullPath,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if file is an image.
     */
    protected function isImageFile(UploadedFile $file): bool
    {
        $mime = $file->getMimeType();

        return $mime !== null && str_starts_with($mime, 'image/');
    }

    /**
     * Determine file extension based on processing options.
     */
    protected function determineExtension(UploadedFile $file, bool $convertToWebp): string
    {
        if ($convertToWebp && $this->isImageFile($file)) {
            return 'webp';
        }

        $extension = $file->getClientOriginalExtension();
        if (empty($extension)) {
            $extension = $file->guessExtension() ?? 'bin';
        }

        return strtolower($extension);
    }

    /**
     * Update path extension.
     */
    protected function updatePathExtension(string $path, string $newExtension): string
    {
        $pathInfo = pathinfo($path);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];

        return $directory.'/'.$filename.'.'.$newExtension;
    }

    /**
     * Parse base64 image data.
     *
     * @param  string  $base64  Base64 encoded image
     * @return array{data: string, mime: string, extension: string}|null
     */
    protected function parseBase64Image(string $base64): ?array
    {
        // Handle data URI format: data:image/jpeg;base64,/9j/4AAQ...
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64, $matches)) {
            $extension = $matches[1];
            $data = base64_decode($matches[2], true);

            if ($data === false) {
                return null;
            }

            $mime = 'image/'.$extension;

            // Normalize extension
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            return [
                'data' => $data,
                'mime' => $mime,
                'extension' => $extension,
            ];
        }

        // Handle raw base64 (assume JPEG)
        $data = base64_decode($base64, true);
        if ($data === false) {
            return null;
        }

        // Try to detect image type from data
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($data);

        if ($mime === false || ! str_starts_with($mime, 'image/')) {
            return null;
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        return [
            'data' => $data,
            'mime' => $mime,
            'extension' => $extension,
        ];
    }

    /**
     * Log file access for audit purposes.
     *
     * @param  string  $action  Action performed
     * @param  string  $type  File type
     * @param  array  $context  Additional context
     */
    protected function logFileAccess(string $action, string $type, array $context = []): void
    {
        if ($this->securityService) {
            $path = $context['path'] ?? $context['filename'] ?? 'unknown';
            $this->securityService->logFileAccess($path, $action, array_merge($context, ['type' => $type]));
        } else {
            // Fallback logging
            Log::channel('file_access')->info("File access: {$action}", array_merge($context, [
                'type' => $type,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'ip' => request()->ip(),
                'timestamp' => now()->toIso8601String(),
            ]));
        }
    }
}
