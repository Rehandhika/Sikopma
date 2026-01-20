<?php

namespace App\Services;

use App\Services\Storage\FileStorageServiceInterface;
use App\Services\Storage\DTOs\FileResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ProductImageService - Wrapper untuk backward compatibility.
 * 
 * Service ini sekarang mendelegasikan ke FileStorageService untuk semua operasi file.
 * Mempertahankan API yang sama untuk backward compatibility dengan kode existing.
 */
class ProductImageService
{
    // Legacy constants untuk backward compatibility
    private const DISK = 'public';
    private const BASE_PATH = 'products';

    public function __construct(
        protected FileStorageServiceInterface $fileStorageService
    ) {}

    /**
     * Upload and process product image.
     * Delegates to FileStorageService.
     * 
     * @param UploadedFile $file
     * @param string|null $oldImagePath Path file lama untuk dihapus
     * @return string Path file yang disimpan
     */
    public function upload(UploadedFile $file, ?string $oldImagePath = null): string
    {
        try {
            $result = $this->fileStorageService->upload($file, 'product', [
                'old_path' => $oldImagePath,
            ]);

            return $result->path;
        } catch (\Exception $e) {
            Log::error('ProductImageService: Upload failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get image URL with specific size.
     * Delegates to FileStorageService.
     * 
     * @param string|null $path
     * @param string $size Size variant (thumbnail, medium, large)
     * @return string|null
     */
    public function getUrl(?string $path, string $size = 'medium'): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            // Try new FileStorageService first
            $url = $this->fileStorageService->getUrl($path, $size);
            
            if ($url) {
                return $url;
            }

            // Fallback for legacy paths - check if file exists directly
            if (Storage::disk(self::DISK)->exists($path)) {
                // Use relative URL for cross-device compatibility
                return '/storage/' . ltrim($path, '/');
            }

            // Try legacy variant path format
            $legacyVariantPath = $this->getLegacyVariantPath($path, $size);
            if (Storage::disk(self::DISK)->exists($legacyVariantPath)) {
                // Use relative URL for cross-device compatibility
                return '/storage/' . ltrim($legacyVariantPath, '/');
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('ProductImageService: Failed to get URL', [
                'path' => $path,
                'size' => $size,
                'error' => $e->getMessage(),
            ]);
            
            // Ultimate fallback - return direct URL if file exists
            if (Storage::disk(self::DISK)->exists($path)) {
                // Use relative URL for cross-device compatibility
                return '/storage/' . ltrim($path, '/');
            }
            
            return null;
        }
    }

    /**
     * Get thumbnail URL (shortcut).
     * 
     * @param string|null $path
     * @return string|null
     */
    public function getThumbnailUrl(?string $path): ?string
    {
        return $this->getUrl($path, 'thumbnail');
    }

    /**
     * Delete image and all variants.
     * Delegates to FileStorageService.
     * 
     * @param string|null $path
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        try {
            // Try new FileStorageService first
            $deleted = $this->fileStorageService->delete($path);
            
            if ($deleted) {
                return true;
            }

            // Fallback: try to delete legacy format files
            return $this->deleteLegacyFiles($path);
        } catch (\Exception $e) {
            Log::warning('ProductImageService: Delete failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            
            // Try legacy delete as fallback
            return $this->deleteLegacyFiles($path);
        }
    }

    /**
     * Check if file exists.
     * 
     * @param string|null $path
     * @return bool
     */
    public function exists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        try {
            return $this->fileStorageService->exists($path);
        } catch (\Exception $e) {
            // Fallback to direct check
            return Storage::disk(self::DISK)->exists($path);
        }
    }

    /**
     * Get legacy variant path format.
     * For backward compatibility with old file structure.
     * 
     * @param string $originalPath
     * @param string $size
     * @return string
     */
    protected function getLegacyVariantPath(string $originalPath, string $size): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];

        return "{$directory}/{$size}/{$filename}.webp";
    }

    /**
     * Delete legacy format files.
     * For backward compatibility.
     * 
     * @param string $path
     * @return bool
     */
    protected function deleteLegacyFiles(string $path): bool
    {
        $deleted = false;

        // Delete original
        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
            $deleted = true;
        }

        // Delete legacy variants
        $sizes = ['thumbnail', 'medium', 'large'];
        foreach ($sizes as $size) {
            $variantPath = $this->getLegacyVariantPath($path, $size);
            if (Storage::disk(self::DISK)->exists($variantPath)) {
                Storage::disk(self::DISK)->delete($variantPath);
            }
            // Clear legacy cache
            Cache::forget('product_img_' . md5($variantPath));
        }

        return $deleted;
    }
}
