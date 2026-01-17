<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PublicDataService
{
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

        return $banners->map(function (Banner $banner) {
            $imagePath = $banner->image_path;

            $defaultUrl = $imagePath
                ? Storage::disk('public')->url($imagePath)
                : null;

            $images = [
                'default' => $defaultUrl,
                '480' => null,
                '768' => null,
                '1920' => null,
            ];

            if ($imagePath) {
                $pathInfo = pathinfo($imagePath);
                $filename = $pathInfo['filename'] ?? '';
                $directory = $pathInfo['dirname'] ?? '';
                $extension = $pathInfo['extension'] ?? 'jpg';

                $lastUnderscorePos = strrpos($filename, '_');
                $uuid = $lastUnderscorePos !== false ? substr($filename, 0, $lastUnderscorePos) : $filename;

                $images['480'] = Storage::disk('public')->url("{$directory}/{$uuid}_480.{$extension}");
                $images['768'] = Storage::disk('public')->url("{$directory}/{$uuid}_768.{$extension}");
                $images['1920'] = Storage::disk('public')->url("{$directory}/{$uuid}_1920.{$extension}");
            }

            return [
                'id' => $banner->id,
                'title' => $banner->title,
                'images' => $images,
            ];
        })->values()->toArray();
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

