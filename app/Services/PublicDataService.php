<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\Storage\FileStorageServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PublicDataService
{
    public function __construct(
        protected ?FileStorageServiceInterface $fileStorageService = null
    ) {}

    public function about(): array
    {
        $storeSetting = Cache::remember('api:public:store_settings:about', 300, function () {
            return StoreSetting::query()->first();
        });

        if (!$storeSetting) {
            return [
                'about_text' => null,
                'contact_phone' => null,
                'contact_email' => null,
                'contact_whatsapp' => null,
                'contact_address' => null,
                'operating_hours' => null,
            ];
        }

        return [
            'about_text' => $storeSetting->about_text,
            'contact_phone' => $storeSetting->contact_phone,
            'contact_email' => $storeSetting->contact_email,
            'contact_whatsapp' => $storeSetting->contact_whatsapp,
            'contact_address' => $storeSetting->contact_address,
            'operating_hours' => $storeSetting->operating_hours,
        ];
    }

    public function banners(): array
    {
        $banners = Cache::remember('api:public:banners:active', 300, function () {
            return Banner::query()
                ->active()
                ->ordered()
                ->get(['id', 'title', 'image_path', 'priority']);
        });

        $fileStorageService = $this->fileStorageService;

        return $banners->map(function (Banner $banner) use ($fileStorageService) {
            $imagePath = $banner->image_path;

            // Default fallback URL - use relative path for cross-device compatibility
            $defaultUrl = $imagePath
                ? '/storage/' . ltrim($imagePath, '/')
                : null;

            $images = [
                'default' => $defaultUrl,
                '480' => null,
                '768' => null,
                '1920' => null,
            ];

            if ($imagePath && $fileStorageService) {
                // Try to use FileStorageService for new path format
                try {
                    // Get URLs using FileStorageService (handles new structure)
                    $images['default'] = $fileStorageService->getUrl($imagePath) ?? $defaultUrl;
                    $images['480'] = $fileStorageService->getUrl($imagePath, 'mobile');
                    $images['768'] = $fileStorageService->getUrl($imagePath, 'tablet');
                    $images['1920'] = $fileStorageService->getUrl($imagePath, 'desktop');
                } catch (\Exception $e) {
                    // Fallback to legacy URL generation
                    $images = $this->generateLegacyBannerUrls($imagePath, $defaultUrl);
                }
            } elseif ($imagePath) {
                // Fallback to legacy URL generation
                $images = $this->generateLegacyBannerUrls($imagePath, $defaultUrl);
            }

            return [
                'id' => $banner->id,
                'title' => $banner->title,
                'images' => $images,
            ];
        })->values()->toArray();
    }

    /**
     * Generate legacy banner URLs for old file structure.
     */
    protected function generateLegacyBannerUrls(string $imagePath, ?string $defaultUrl): array
    {
        $images = [
            'default' => $defaultUrl,
            '480' => null,
            '768' => null,
            '1920' => null,
        ];

        $pathInfo = pathinfo($imagePath);
        $filename = $pathInfo['filename'] ?? '';
        $directory = $pathInfo['dirname'] ?? '';
        $extension = $pathInfo['extension'] ?? 'jpg';

        $lastUnderscorePos = strrpos($filename, '_');
        $uuid = $lastUnderscorePos !== false ? substr($filename, 0, $lastUnderscorePos) : $filename;

        // Use relative URLs for cross-device compatibility
        $images['480'] = "/storage/{$directory}/{$uuid}_480.{$extension}";
        $images['768'] = "/storage/{$directory}/{$uuid}_768.{$extension}";
        $images['1920'] = "/storage/{$directory}/{$uuid}_1920.{$extension}";

        return $images;
    }

    public function categories(): array
    {
        $categories = Cache::remember('api:public:product_categories', 300, function () {
            return Product::query()
                ->public()
                ->active()
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->sort()
                ->values();
        });

        return $categories->toArray();
    }

    public function products(string $search = '', string $category = '', int $page = 1, int $perPage = 12): LengthAwarePaginator
    {
        $perPage = max(1, min(48, $perPage));
        $page = max(1, $page);

        $cacheKey = sprintf(
            'api:public:products:page:%s:per:%s:search:%s:category:%s',
            (string) $page,
            (string) $perPage,
            $search,
            $category
        );

        return Cache::remember($cacheKey, 300, function () use ($search, $category, $perPage, $page) {
            return Product::query()
                ->select([
                    'id',
                    'name',
                    'slug',
                    'price',
                    'stock',
                    'min_stock',
                    'category',
                    'image',
                    'is_featured',
                    'status',
                    'is_public',
                    'display_order',
                ])
                ->public()
                ->active()
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    });
                })
                ->when($category, function ($query) use ($category) {
                    $query->where('category', $category);
                })
                ->ordered()
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    public function product(string $slug): array
    {
        $cacheKey = "api:public:product:slug:{$slug}";

        $product = Cache::remember($cacheKey, 300, function () use ($slug) {
            return Product::query()
                ->select([
                    'id',
                    'name',
                    'slug',
                    'sku',
                    'price',
                    'stock',
                    'min_stock',
                    'category',
                    'description',
                    'image',
                    'is_featured',
                ])
                ->where('is_public', true)
                ->active()
                ->where('slug', $slug)
                ->firstOrFail();
        });

        return array_merge($product->toArray(), [
            'image_large_url' => $product->image_large_url,
        ]);
    }
}

