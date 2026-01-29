<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Services\DateTimeSettingsService;
use App\Services\PublicDataService;
use App\Services\StoreStatusService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    private function respondCachedJson(Request $request, array $payload, int $maxAge, int $staleWhileRevalidate): Response
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $etag = '"'.sha1((string) $json).'"';

        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Cache-Control' => "public, max-age={$maxAge}, stale-while-revalidate={$staleWhileRevalidate}",
            'ETag' => $etag,
            'Vary' => 'Accept, Accept-Encoding',
        ];

        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304)->withHeaders($headers);
        }

        return response($json, 200)->withHeaders($headers);
    }

    public function about(Request $request, PublicDataService $publicDataService): Response
    {
        return $this->respondCachedJson($request, [
            'data' => $publicDataService->about(),
        ], 60, 300);
    }

    public function banners(Request $request, PublicDataService $publicDataService): Response
    {
        return $this->respondCachedJson($request, [
            'data' => $publicDataService->banners(),
        ], 60, 300);
    }

    public function categories(Request $request, PublicDataService $publicDataService): Response
    {
        return $this->respondCachedJson($request, [
            'data' => $publicDataService->categories(),
        ], 60, 300);
    }

    public function products(Request $request, PublicDataService $publicDataService): Response
    {
        $search = (string) $request->query('search', '');
        $category = (string) $request->query('category', '');
        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min(48, $perPage));
        $page = (int) $request->query('page', 1);
        $page = max(1, $page);

        $products = $publicDataService->products($search, $category, $page, $perPage);

        return $this->respondCachedJson($request, $products->toArray(), 60, 300);
    }

    public function product(Request $request, string $slug, PublicDataService $publicDataService): Response
    {
        return $this->respondCachedJson($request, [
            'data' => $publicDataService->product($slug),
        ], 60, 300);
    }

    public function storeStatus(Request $request, StoreStatusService $storeStatusService): Response
    {
        return $this->respondCachedJson($request, [
            'data' => $storeStatusService->getStatus(),
        ], 5, 10);
    }

    public function dateTimeSettings(Request $request, DateTimeSettingsService $dateTimeService): Response
    {
        return $this->respondCachedJson($request, [
            'data' => $dateTimeService->getForFrontend(),
        ], 60, 300);
    }

    public function news(Request $request, PublicDataService $publicDataService): Response
    {
        return $this->respondCachedJson($request, [
            'data' => $publicDataService->news(),
        ], 60, 300);
    }
}
