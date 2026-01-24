<?php

namespace App\Livewire\Public;

use App\Models\Product;
use App\Models\Banner;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class Catalog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $page = $this->getPage();
        $search = $this->search;
        $category = $this->category;
        $cacheKey = "products:public:page:{$page}:search:{$search}:category:{$category}";
        
        $products = Cache::remember($cacheKey, 300, function () use ($search, $category, $page) {
            return Product::query()
                ->select([
                    'id', 'name', 'slug', 'price', 'stock', 'min_stock', 
                    'category', 'image', 'is_featured', 'status', 'is_public', 'display_order', 'has_variants'
                ])
                ->withVariantStats() // Eager load variant statistics untuk performa
                ->with(['activeVariants' => function ($q) {
                    $q->select(['id', 'product_id', 'price', 'stock', 'is_active']);
                }])
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
                ->paginate(12, ['*'], 'page', $page);
        });

        $categories = Cache::remember('products:categories', 300, function () {
            return Product::query()
                ->public()
                ->active()
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->sort()
                ->values();
        });

        // Cache banner query for performance (5 minutes)
        $banners = Cache::remember('banners:active', 300, function () {
            return Banner::query()
                ->active()
                ->ordered()
                ->get();
        });

        return view('livewire.public.catalog', [
            'products' => $products,
            'categories' => $categories,
            'banners' => $banners,
        ])->layout('layouts.public');
    }
}
