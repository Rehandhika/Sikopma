<?php

namespace App\Services;

use App\Models\Banner;
use App\Services\Storage\FileStorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

/**
 * BannerService - Service untuk mengelola banner.
 * 
 * Sekarang mendelegasikan operasi file ke FileStorageService untuk konsistensi.
 * Mempertahankan API yang sama untuk backward compatibility.
 */
class BannerService
{
    public function __construct(
        protected FileStorageServiceInterface $fileStorageService
    ) {}

    /**
     * Store a new banner.
     *
     * @param array $data
     * @param UploadedFile $image
     * @return Banner
     */
    public function store(array $data, UploadedFile $image): Banner
    {
        return DB::transaction(function () use ($data, $image) {
            // Process the image using FileStorageService
            $result = $this->fileStorageService->upload($image, 'banner');
            
            // Create banner record
            $banner = Banner::create([
                'title' => $data['title'] ?? null,
                'image_path' => $result->path,
                'priority' => $data['priority'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            return $banner;
        });
    }

    /**
     * Update an existing banner.
     *
     * @param Banner $banner
     * @param array $data
     * @param UploadedFile|null $image
     * @return Banner
     */
    public function update(Banner $banner, array $data, ?UploadedFile $image = null): Banner
    {
        return DB::transaction(function () use ($banner, $data, $image) {
            $updateData = [
                'title' => $data['title'] ?? $banner->title,
                'priority' => $data['priority'] ?? $banner->priority,
                'is_active' => $data['is_active'] ?? $banner->is_active,
            ];

            // If new image is provided, process it and delete old images
            if ($image) {
                // Upload new image (FileStorageService will handle old file deletion)
                $result = $this->fileStorageService->upload($image, 'banner', [
                    'old_path' => $banner->image_path,
                ]);
                $updateData['image_path'] = $result->path;
            }

            $banner->update($updateData);

            return $banner->fresh();
        });
    }

    /**
     * Delete a banner and its associated images.
     *
     * @param Banner $banner
     * @return bool
     */
    public function delete(Banner $banner): bool
    {
        return DB::transaction(function () use ($banner) {
            // Delete image files using FileStorageService
            if ($banner->image_path) {
                try {
                    $this->fileStorageService->delete($banner->image_path);
                } catch (\Exception $e) {
                    // Fallback to legacy delete
                    $this->deleteImageFilesLegacy($banner->image_path);
                }
            }
            
            // Delete banner record
            return $banner->delete();
        });
    }

    /**
     * Toggle banner active status.
     *
     * @param Banner $banner
     * @return Banner
     */
    public function toggleStatus(Banner $banner): Banner
    {
        $banner->update([
            'is_active' => !$banner->is_active,
        ]);

        return $banner->fresh();
    }

    /**
     * Process uploaded image - delegates to FileStorageService.
     * Kept for backward compatibility.
     *
     * @param UploadedFile $image
     * @return array Array with paths to different image sizes
     */
    public function processImage(UploadedFile $image): array
    {
        $result = $this->fileStorageService->upload($image, 'banner');
        
        // Return in legacy format for backward compatibility
        $paths = [
            'main' => $result->path,
        ];

        // Add variant paths if available
        foreach ($result->variants as $key => $variant) {
            $paths[$key] = $variant['path'] ?? $variant;
        }

        return $paths;
    }

    /**
     * Get active banners ordered by priority.
     *
     * @return Collection
     */
    public function getActiveBanners(): Collection
    {
        return Banner::active()->ordered()->get();
    }

    /**
     * Get banner image URL.
     * 
     * @param string|null $path
     * @param string|null $size Variant size (desktop, tablet, mobile)
     * @return string|null
     */
    public function getImageUrl(?string $path, ?string $size = null): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            return $this->fileStorageService->getUrl($path, $size);
        } catch (\Exception $e) {
            // Fallback to direct URL
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
            return null;
        }
    }

    /**
     * Delete image files for a given main image path (legacy method).
     * Kept for backward compatibility with old file structure.
     *
     * @param string $mainImagePath
     * @return void
     */
    protected function deleteImageFilesLegacy(string $mainImagePath): void
    {
        // Extract UUID from main image path
        $pathInfo = pathinfo($mainImagePath);
        $filename = $pathInfo['filename'];
        
        // Extract UUID (everything before the last underscore)
        $lastUnderscorePos = strrpos($filename, '_');
        if ($lastUnderscorePos !== false) {
            $uuid = substr($filename, 0, $lastUnderscorePos);
            
            // Delete all variants
            $sizes = [1920, 768, 480];
            foreach ($sizes as $size) {
                $filePath = "banners/{$uuid}_{$size}.jpg";
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        }

        // Also try to delete the main path directly
        if (Storage::disk('public')->exists($mainImagePath)) {
            Storage::disk('public')->delete($mainImagePath);
        }
    }
}
