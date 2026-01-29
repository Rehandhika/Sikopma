<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductVariantService
{
    /**
     * Cache TTL in minutes for product stats
     */
    protected const CACHE_TTL_MINUTES = 5;

    /**
     * Create a new variant for a product
     */
    public function createVariant(Product $product, array $data): ProductVariant
    {
        return DB::transaction(function () use ($product, $data) {
            // Generate SKU if not provided
            $sku = $data['sku'] ?? $this->generateSku($product, $data['option_values'] ?? []);

            // Build variant name from option values
            $variantName = $data['variant_name'] ?? $this->buildVariantName($product, $data['option_values'] ?? []);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'variant_name' => $variantName,
                'price' => $data['price'] ?? $product->price,
                'cost_price' => $data['cost_price'] ?? $product->cost_price ?? 0,
                'stock' => $data['stock'] ?? 0,
                'min_stock' => $data['min_stock'] ?? 5,
                'option_values' => $data['option_values'] ?? [],
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Ensure product is marked as has_variants
            if (! $product->has_variants) {
                $product->update(['has_variants' => true]);
            }

            // Sync product total stock after creating variant
            $this->syncProductTotalStock($product);

            return $variant;
        });
    }

    /**
     * Update an existing variant
     */
    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);

        return $variant->fresh();
    }

    /**
     * Generate unique SKU for variant
     */
    public function generateSku(Product $product, array $optionValues): string
    {
        // Base SKU from product
        $baseSku = $product->sku ?? Str::upper(Str::slug($product->name, ''));
        $baseSku = Str::limit($baseSku, 10, '');

        // Add option value codes
        $optionCodes = collect($optionValues)
            ->map(fn ($opt) => Str::upper(Str::limit($opt['value'] ?? '', 3, '')))
            ->implode('-');

        $sku = $baseSku.'-'.$optionCodes;

        // Ensure uniqueness
        $originalSku = $sku;
        $counter = 1;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSku.'-'.$counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Build variant name from option values
     */
    public function buildVariantName(Product $product, array $optionValues): string
    {
        $optionString = collect($optionValues)
            ->map(fn ($opt) => $opt['value'] ?? '')
            ->filter()
            ->implode(' / ');

        return $product->name.($optionString ? ' - '.$optionString : '');
    }

    /**
     * Get available variants for a product
     */
    public function getAvailableVariants(Product $product): Collection
    {
        return $product->activeVariants()
            ->where('stock', '>', 0)
            ->orderBy('variant_name')
            ->get();
    }

    /**
     * Get all variants grouped by option
     */
    public function getVariantsGroupedByOption(Product $product): array
    {
        $variants = $product->activeVariants()->get();
        $grouped = [];

        foreach ($variants as $variant) {
            foreach ($variant->option_values as $optionSlug => $optionData) {
                if (! isset($grouped[$optionSlug])) {
                    $grouped[$optionSlug] = [
                        'option_name' => $optionData['option_name'] ?? ucfirst($optionSlug),
                        'values' => [],
                    ];
                }

                $valueKey = $optionData['value'] ?? '';
                if (! isset($grouped[$optionSlug]['values'][$valueKey])) {
                    $grouped[$optionSlug]['values'][$valueKey] = [
                        'value' => $valueKey,
                        'value_id' => $optionData['value_id'] ?? null,
                        'in_stock' => false,
                    ];
                }

                if ($variant->stock > 0) {
                    $grouped[$optionSlug]['values'][$valueKey]['in_stock'] = true;
                }
            }
        }

        return $grouped;
    }

    /**
     * Decrease stock for a variant
     */
    public function decreaseStock(ProductVariant $variant, int $quantity): void
    {
        if ($variant->stock < $quantity) {
            throw new BusinessException(
                "Stok varian tidak mencukupi. Tersedia: {$variant->stock}, Diminta: {$quantity}",
                'INSUFFICIENT_STOCK'
            );
        }

        $variant->decreaseStock($quantity);

        // Auto-sync product total stock
        $this->syncProductTotalStock($variant->product);
    }

    /**
     * Increase stock for a variant
     */
    public function increaseStock(ProductVariant $variant, int $quantity): void
    {
        $variant->increaseStock($quantity);

        // Auto-sync product total stock
        $this->syncProductTotalStock($variant->product);
    }

    /**
     * Get variant by option values
     */
    public function findVariantByOptions(Product $product, array $selectedOptions): ?ProductVariant
    {
        return $product->activeVariants()
            ->get()
            ->first(function ($variant) use ($selectedOptions) {
                foreach ($selectedOptions as $optionSlug => $valueId) {
                    $variantOption = $variant->option_values[$optionSlug] ?? null;
                    if (! $variantOption || ($variantOption['value_id'] ?? null) != $valueId) {
                        return false;
                    }
                }

                return true;
            });
    }

    /**
     * Bulk create variants from option combinations
     */
    public function createVariantsFromCombinations(Product $product, array $optionIds, array $baseData = []): Collection
    {
        $options = VariantOption::with('values')
            ->whereIn('id', $optionIds)
            ->get();

        $combinations = $this->generateCombinations($options);
        $variants = collect();

        foreach ($combinations as $combination) {
            $optionValues = [];
            foreach ($combination as $optionSlug => $valueData) {
                $optionValues[$optionSlug] = $valueData;
            }

            $variant = $this->createVariant($product, array_merge($baseData, [
                'option_values' => $optionValues,
            ]));

            $variants->push($variant);
        }

        return $variants;
    }

    /**
     * Generate all combinations of option values
     * Returns array of combinations where each combination is an associative array
     * keyed by option slug containing option/value data
     *
     * Requirements: 4.1
     *
     * @param  Collection  $options  Collection of VariantOption with loaded values
     * @return array Array of combinations
     */
    public function generateCombinations(Collection $options): array
    {
        $result = [[]];

        foreach ($options as $option) {
            $newResult = [];
            foreach ($result as $combination) {
                foreach ($option->values as $value) {
                    $newCombination = $combination;
                    $newCombination[$option->slug] = [
                        'option_id' => $option->id,
                        'option_name' => $option->name,
                        'value_id' => $value->id,
                        'value' => $value->value,
                    ];
                    $newResult[] = $newCombination;
                }
            }
            $result = $newResult;
        }

        return $result;
    }

    /**
     * Generate all variant combinations from selected option value IDs
     * This method generates combinations from specific value selections per option
     *
     * Requirements: 4.1
     *
     * @param  array  $selectedValuesByOption  Array keyed by option_id containing arrays of value_ids
     *                                         e.g., [1 => [1, 2], 2 => [5, 6, 7]]
     * @return array Array of combinations with full option/value data
     */
    public function generateCombinationsFromSelectedValues(array $selectedValuesByOption): array
    {
        if (empty($selectedValuesByOption)) {
            return [];
        }

        // Load options with their values
        $optionIds = array_keys($selectedValuesByOption);
        $options = VariantOption::with('values')
            ->whereIn('id', $optionIds)
            ->orderBy('display_order')
            ->get();

        $result = [[]];

        foreach ($options as $option) {
            $selectedValueIds = $selectedValuesByOption[$option->id] ?? [];
            if (empty($selectedValueIds)) {
                continue;
            }

            // Filter to only selected values
            $selectedValues = $option->values->whereIn('id', $selectedValueIds);

            $newResult = [];
            foreach ($result as $combination) {
                foreach ($selectedValues as $value) {
                    $newCombination = $combination;
                    $newCombination[$option->slug] = [
                        'option_id' => $option->id,
                        'option_name' => $option->name,
                        'value_id' => $value->id,
                        'value' => $value->value,
                    ];
                    $newResult[] = $newCombination;
                }
            }
            $result = $newResult;
        }

        return $result;
    }

    /**
     * Bulk generate variants from option combinations with default price and stock
     * Creates all possible combinations from selected options and values
     *
     * Requirements: 4.1
     *
     * @param  Product  $product  The product to create variants for
     * @param  array  $selectedValuesByOption  Array keyed by option_id containing arrays of value_ids
     * @param  float  $defaultPrice  Default price for all generated variants
     * @param  float  $defaultCostPrice  Default cost price for all generated variants
     * @param  int  $defaultStock  Default stock for all generated variants
     * @param  int  $defaultMinStock  Default minimum stock for all generated variants
     * @return Collection Collection of created ProductVariant models
     */
    public function bulkGenerateVariants(
        Product $product,
        array $selectedValuesByOption,
        float $defaultPrice,
        float $defaultCostPrice = 0,
        int $defaultStock = 0,
        int $defaultMinStock = 5
    ): Collection {
        return DB::transaction(function () use ($product, $selectedValuesByOption, $defaultPrice, $defaultCostPrice, $defaultStock, $defaultMinStock) {
            $combinations = $this->generateCombinationsFromSelectedValues($selectedValuesByOption);
            $variants = collect();

            foreach ($combinations as $combination) {
                // Check if variant with same option values already exists
                $existingVariant = $this->findExistingVariantByCombination($product, $combination);

                if ($existingVariant) {
                    // Skip existing variants
                    $variants->push($existingVariant);

                    continue;
                }

                $variant = $this->createVariant($product, [
                    'option_values' => $combination,
                    'price' => $defaultPrice,
                    'cost_price' => $defaultCostPrice,
                    'stock' => $defaultStock,
                    'min_stock' => $defaultMinStock,
                    'is_active' => true,
                ]);

                $variants->push($variant);
            }

            // Ensure product is marked as has_variants
            if (! $product->has_variants) {
                $product->update(['has_variants' => true]);
            }

            // Sync variant options to product
            $optionIds = array_keys($selectedValuesByOption);
            $product->variantOptions()->syncWithoutDetaching($optionIds);

            // Sync product total stock
            $this->syncProductTotalStock($product);

            return $variants;
        });
    }

    /**
     * Find existing variant by combination of option values
     */
    protected function findExistingVariantByCombination(Product $product, array $combination): ?ProductVariant
    {
        return $product->variants()
            ->get()
            ->first(function ($variant) use ($combination) {
                $variantOptions = $variant->option_values ?? [];

                // Check if all combination options match
                foreach ($combination as $optionSlug => $valueData) {
                    $variantOption = $variantOptions[$optionSlug] ?? null;
                    if (! $variantOption || ($variantOption['value_id'] ?? null) != $valueData['value_id']) {
                        return false;
                    }
                }

                // Also check that variant doesn't have extra options
                return count($variantOptions) === count($combination);
            });
    }

    /**
     * Preview variant combinations without creating them
     * Useful for showing user what will be generated
     *
     * Requirements: 4.1
     *
     * @param  array  $selectedValuesByOption  Array keyed by option_id containing arrays of value_ids
     * @return array Array with 'count' and 'combinations' keys
     */
    public function previewCombinations(array $selectedValuesByOption): array
    {
        $combinations = $this->generateCombinationsFromSelectedValues($selectedValuesByOption);

        return [
            'count' => count($combinations),
            'combinations' => collect($combinations)->map(function ($combination) {
                return [
                    'name' => collect($combination)->pluck('value')->implode(' / '),
                    'options' => $combination,
                ];
            })->toArray(),
        ];
    }

    /**
     * Calculate expected combination count from selected values
     * Uses multiplication of value counts per option
     *
     * Requirements: 4.1
     *
     * @param  array  $selectedValuesByOption  Array keyed by option_id containing arrays of value_ids
     * @return int Expected number of combinations
     */
    public function calculateCombinationCount(array $selectedValuesByOption): int
    {
        if (empty($selectedValuesByOption)) {
            return 0;
        }

        $count = 1;
        foreach ($selectedValuesByOption as $valueIds) {
            $valueCount = count($valueIds);
            if ($valueCount > 0) {
                $count *= $valueCount;
            }
        }

        return $count;
    }

    /**
     * Get low stock variants for a product
     */
    public function getLowStockVariants(Product $product): Collection
    {
        return $product->variants()
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->get();
    }

    /**
     * Check if product can be activated (has at least one variant if has_variants is true)
     *
     * Requirements: 4.4
     */
    public function canActivate(Product $product): bool
    {
        if (! $product->has_variants) {
            return true;
        }

        return $product->activeVariants()->count() > 0;
    }

    /**
     * Validate product can be activated and return detailed result
     *
     * Requirements: 4.4
     *
     * @return array{can_activate: bool, message: string|null}
     */
    public function validateActivation(Product $product): array
    {
        if (! $product->has_variants) {
            return [
                'can_activate' => true,
                'message' => null,
            ];
        }

        $activeVariantCount = $product->activeVariants()->count();

        if ($activeVariantCount === 0) {
            return [
                'can_activate' => false,
                'message' => 'Produk dengan varian harus memiliki minimal 1 varian aktif sebelum dapat diaktifkan.',
            ];
        }

        return [
            'can_activate' => true,
            'message' => null,
        ];
    }

    /**
     * Sync total stock dari variants ke product
     * Dipanggil setelah setiap perubahan stock variant
     *
     * Requirements: 2.1, 6.3
     */
    public function syncProductTotalStock(Product $product): void
    {
        if (! $product->has_variants) {
            return;
        }

        // Calculate total stock from all active variants
        $totalStock = $product->activeVariants()->sum('stock');

        // Update product stock (for backward compatibility and quick access)
        $product->update(['stock' => $totalStock]);

        // Invalidate cache for this product
        $this->invalidateProductCache($product);
    }

    /**
     * Invalidate all cached data for a product
     */
    protected function invalidateProductCache(Product $product): void
    {
        Cache::forget("product:{$product->id}:total_stock");
        Cache::forget("product:{$product->id}:price_range");
        Cache::forget("product:{$product->id}:variant_count");
        Cache::forget("product:{$product->id}:stats");

        // Also clear general product caches
        CacheService::clearProducts();
    }

    /**
     * Validate stock sebelum sale
     * Returns true if stock is sufficient, throws exception otherwise
     *
     * Requirements: 3.5, 3.6
     *
     * @throws BusinessException
     */
    public function validateStockForSale(int $variantId, int $quantity): bool
    {
        $variant = ProductVariant::find($variantId);

        if (! $variant) {
            throw new BusinessException('Varian produk tidak ditemukan.');
        }

        if (! $variant->is_active) {
            throw new BusinessException("Varian '{$variant->variant_name}' tidak aktif.");
        }

        if ($variant->stock < $quantity) {
            throw new BusinessException(
                "Stok varian '{$variant->variant_name}' tidak mencukupi. ".
                "Tersedia: {$variant->stock}, Diminta: {$quantity}"
            );
        }

        return true;
    }

    /**
     * Get detailed stock validation result (non-throwing version)
     *
     * @return array{valid: bool, message: string|null, available_stock: int|null}
     */
    public function checkStockAvailability(int $variantId, int $quantity): array
    {
        $variant = ProductVariant::find($variantId);

        if (! $variant) {
            return [
                'valid' => false,
                'message' => 'Varian produk tidak ditemukan.',
                'available_stock' => null,
            ];
        }

        if (! $variant->is_active) {
            return [
                'valid' => false,
                'message' => "Varian '{$variant->variant_name}' tidak aktif.",
                'available_stock' => 0,
            ];
        }

        if ($variant->stock < $quantity) {
            return [
                'valid' => false,
                'message' => "Stok tidak mencukupi. Tersedia: {$variant->stock}",
                'available_stock' => $variant->stock,
            ];
        }

        return [
            'valid' => true,
            'message' => null,
            'available_stock' => $variant->stock,
        ];
    }

    /**
     * Bulk update stock untuk multiple variants dalam single transaction
     * Auto-sync product total stock setelah bulk update
     *
     * Requirements: 4.3
     *
     * @param  array  $variantStockData  Array of ['variant_id' => int, 'stock' => int]
     * @return Collection Updated variants
     */
    public function bulkUpdateStock(array $variantStockData): Collection
    {
        return DB::transaction(function () use ($variantStockData) {
            $updatedVariants = collect();
            $affectedProductIds = [];

            foreach ($variantStockData as $data) {
                $variantId = $data['variant_id'] ?? null;
                $newStock = $data['stock'] ?? null;

                if ($variantId === null || $newStock === null) {
                    continue;
                }

                // Validate stock is non-negative
                if ($newStock < 0) {
                    throw new BusinessException(
                        "Stok tidak boleh negatif untuk variant ID: {$variantId}"
                    );
                }

                $variant = ProductVariant::find($variantId);
                if (! $variant) {
                    continue;
                }

                $variant->update(['stock' => $newStock]);
                $updatedVariants->push($variant->fresh());

                // Track affected products for sync
                $affectedProductIds[$variant->product_id] = true;
            }

            // Sync total stock for all affected products
            foreach (array_keys($affectedProductIds) as $productId) {
                $product = Product::find($productId);
                if ($product) {
                    $this->syncProductTotalStock($product);
                }
            }

            return $updatedVariants;
        });
    }

    /**
     * Get variants dengan eager loading dan caching
     */
    public function getVariantsOptimized(Product $product): Collection
    {
        $cacheKey = "product:{$product->id}:variants";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($product) {
            return $product->activeVariants()
                ->orderBy('variant_name')
                ->get();
        });
    }

    /**
     * Get cached total stock for a product
     */
    public function getCachedTotalStock(Product $product): int
    {
        if (! $product->has_variants) {
            return (int) $product->stock;
        }

        $cacheKey = "product:{$product->id}:total_stock";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($product) {
            return (int) $product->activeVariants()->sum('stock');
        });
    }

    /**
     * Get cached price range for a product
     */
    public function getCachedPriceRange(Product $product): array
    {
        if (! $product->has_variants) {
            return ['min' => (float) $product->price, 'max' => (float) $product->price];
        }

        $cacheKey = "product:{$product->id}:price_range";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($product) {
            $variants = $product->activeVariants();

            return [
                'min' => (float) ($variants->min('price') ?? $product->price),
                'max' => (float) ($variants->max('price') ?? $product->price),
            ];
        });
    }
}
