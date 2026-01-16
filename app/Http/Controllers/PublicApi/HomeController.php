<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\StoreStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function about(): JsonResponse
    {
        $storeSetting = Cache::remember('api:public:store_settings:about', 300, function () {
            return StoreSetting::query()->first();
        });

        if (!$storeSetting) {
            return response()->json([
                'data' => [
                    'about_text' => null,
                    'contact_phone' => null,
                    'contact_email' => null,
                    'contact_whatsapp' => null,
                    'contact_address' => null,
                    'operating_hours' => null,
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'about_text' => $storeSetting->about_text,
                'contact_phone' => $storeSetting->contact_phone,
                'contact_email' => $storeSetting->contact_email,
                'contact_whatsapp' => $storeSetting->contact_whatsapp,
                'contact_address' => $storeSetting->contact_address,
                'operating_hours' => $storeSetting->operating_hours,
            ],
        ]);
    }

    public function banners(): JsonResponse
    {
        $banners = Cache::remember('api:public:banners:active', 300, function () {
            return Banner::query()
                ->active()
                ->ordered()
                ->get(['id', 'title', 'image_path', 'priority']);
        });

        $payload = $banners->map(function (Banner $banner) {
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
        })->values();

        return response()->json([
            'data' => $payload,
        ]);
    }

    public function categories(): JsonResponse
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

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $search = (string) $request->query('search', '');
        $category = (string) $request->query('category', '');
        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min(48, $perPage));

        $cacheKey = sprintf(
            'api:public:products:page:%s:per:%s:search:%s:category:%s',
            (string) $request->query('page', 1),
            (string) $perPage,
            $search,
            $category
        );

        $products = Cache::remember($cacheKey, 300, function () use ($search, $category, $perPage) {
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
                ->paginate($perPage);
        });

        return response()->json($products);
    }

    public function product(string $slug): JsonResponse
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

        return response()->json([
            'data' => array_merge($product->toArray(), [
                'image_large_url' => $product->image_large_url,
            ]),
        ]);
    }

    public function storeStatus(StoreStatusService $storeStatusService): JsonResponse
    {
        return response()->json([
            'data' => $storeStatusService->getStatus(),
        ]);
    }
}
