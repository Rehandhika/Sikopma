<?php

namespace App\Livewire\Public;

use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;

    public ?int $selectedVariantId = null;

    /**
     * Mount the component with product slug
     */
    public function mount(string $slug): void
    {
        // Query product by slug with public visibility check, optimized with caching and select
        $cacheKey = "product:detail:slug:{$slug}:v2";

        $this->product = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($slug) {
            return Product::select([
                'id', 'name', 'slug', 'sku', 'price', 'stock', 'min_stock',
                'category', 'description', 'image', 'is_featured', 'status', 'is_public', 'has_variants',
            ])
                ->with(['activeVariants' => function ($query) {
                    $query->select([
                        'id', 'product_id', 'sku', 'variant_name', 'price', 'stock', 'min_stock', 'option_values', 'is_active',
                    ])->orderBy('price');
                }])
                ->where('slug', $slug)
                ->where('is_public', true)
                ->firstOrFail();
        });

        // Auto-select first available variant
        if ($this->product->has_variants && $this->product->activeVariants->isNotEmpty()) {
            $firstAvailable = $this->product->activeVariants->first(fn ($v) => $v->stock > 0);
            $this->selectedVariantId = $firstAvailable?->id ?? $this->product->activeVariants->first()->id;
        }
    }

    /**
     * Select a variant
     */
    public function selectVariant(int $variantId): void
    {
        $variant = $this->product->activeVariants->find($variantId);
        if ($variant && $variant->stock > 0) {
            $this->selectedVariantId = $variantId;
        }
    }

    /**
     * Get the currently selected variant
     */
    public function getSelectedVariantProperty()
    {
        if (! $this->selectedVariantId) {
            return null;
        }

        return $this->product->activeVariants->find($this->selectedVariantId);
    }

    /**
     * Get variants grouped by option type
     * Requirements: 5.3
     */
    public function getGroupedVariantsProperty(): Collection
    {
        if (! $this->product->has_variants) {
            return collect();
        }

        $variants = $this->product->activeVariants;
        $grouped = collect();

        // Extract all unique option types and their values
        foreach ($variants as $variant) {
            if (empty($variant->option_values)) {
                continue;
            }

            foreach ($variant->option_values as $key => $optionData) {
                $optionName = $optionData['option_name'] ?? $key;

                if (! $grouped->has($optionName)) {
                    $grouped[$optionName] = collect();
                }

                $value = $optionData['value'] ?? '';
                if (! $grouped[$optionName]->contains('value', $value)) {
                    $grouped[$optionName]->push([
                        'value' => $value,
                        'option_id' => $optionData['option_id'] ?? null,
                    ]);
                }
            }
        }

        return $grouped;
    }

    /**
     * Get display price (selected variant or base price)
     */
    public function getDisplayPriceProperty(): float
    {
        if ($this->product->has_variants && $this->selectedVariant) {
            return (float) $this->selectedVariant->price;
        }

        return (float) $this->product->price;
    }

    /**
     * Get display stock (selected variant or total stock)
     */
    public function getDisplayStockProperty(): int
    {
        if ($this->product->has_variants && $this->selectedVariant) {
            return (int) $this->selectedVariant->stock;
        }

        return $this->product->has_variants ? $this->product->total_stock : (int) $this->product->stock;
    }

    /**
     * Get display SKU (selected variant or product SKU)
     */
    public function getDisplaySkuProperty(): ?string
    {
        if ($this->product->has_variants && $this->selectedVariant) {
            return $this->selectedVariant->sku;
        }

        return $this->product->sku;
    }

    #[Layout('layouts.public')]
    #[Title('Detail Produk')]
    public function render()
    {
        return view('livewire.public.product-detail');
    }
}
