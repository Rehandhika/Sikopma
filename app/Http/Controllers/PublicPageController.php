<?php

namespace App\Http\Controllers;

use App\Services\PublicDataService;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function home(PublicDataService $publicDataService): View
    {
        $initial = [
            'banners' => $publicDataService->banners(),
            'categories' => $publicDataService->categories(),
            'products' => $publicDataService->products('', '', 1, 12)->toArray(),
        ];

        return view('public.react', [
            'page' => 'home',
            'initial' => $initial,
        ]);
    }

    public function about(PublicDataService $publicDataService): View
    {
        return view('public.react', [
            'page' => 'about',
            'initial' => [
                'about' => $publicDataService->about(),
            ],
        ]);
    }

    public function product(string $slug, PublicDataService $publicDataService): View
    {
        return view('public.react', [
            'page' => 'product',
            'slug' => $slug,
            'initial' => [
                'product' => $publicDataService->product($slug),
                'about' => $publicDataService->about(),
            ],
        ]);
    }
}
