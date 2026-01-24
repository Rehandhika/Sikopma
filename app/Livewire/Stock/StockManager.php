<?php

namespace App\Livewire\Stock;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Title, Computed, Url};
use App\Models\{Product, ProductVariant, StockAdjustment};
use App\Services\ProductService;
use Illuminate\Support\Facades\{DB, Cache};

#[Title('Manajemen Stok')]
class StockManager extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'products';
    
    #[Url(except: '')]
    public string $search = '';
    
    #[Url(except: 'all')]
    public string $stockFilter = 'all';
    
    public string $categoryFilter = '';
    public string $historySearch = '';
    public string $historyType = 'all';
    
    // Modal state
    public bool $showAdjustModal = false;
    public ?int $selectedProductId = null;
    public ?int $selectedVariantId = null;
    public string $adjustType = 'in';
    public int $adjustQuantity = 1;
    public string $adjustReason = '';
    
    // Expanded variants
    public array $expandedProducts = [];
    
    // Bulk
    public array $selectedProducts = [];
    public bool $showBulkModal = false;
    public string $bulkType = 'in';
    public string $bulkReason = '';

    protected function rules(): array
    {
        return [
            'adjustQuantity' => 'required|integer|min:1|max:99999',
            'adjustReason' => 'required|string|min:2|max:255',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStockFilter(): void { $this->resetPage(); }
    public function updatingHistorySearch(): void { $this->resetPage('historyPage'); }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * Toggle expanded state for variant products
     */
    public function toggleExpand(int $productId): void
    {
        if (in_array($productId, $this->expandedProducts)) {
            $this->expandedProducts = array_values(array_diff($this->expandedProducts, [$productId]));
        } else {
            $this->expandedProducts[] = $productId;
        }
    }

    /**
     * Open adjust modal - for product or variant
     */
    public function quickAdjust(int $productId, string $type = 'in', ?int $variantId = null): void
    {
        $this->selectedProductId = $productId;
        $this->selectedVariantId = $variantId;
        $this->adjustType = $type;
        $this->adjustQuantity = 1;
        $this->adjustReason = '';
        $this->showAdjustModal = true;
    }

    /**
     * Open adjust modal for a specific variant
     */
    public function adjustVariant(int $variantId, string $type = 'in'): void
    {
        $variant = ProductVariant::find($variantId);
        if ($variant) {
            $this->quickAdjust($variant->product_id, $type, $variantId);
        }
    }

    public function closeAdjustModal(): void
    {
        $this->showAdjustModal = false;
        $this->reset(['selectedProductId', 'selectedVariantId', 'adjustQuantity', 'adjustReason']);
        $this->resetValidation();
    }

    public function saveAdjustment(): void
    {
        $this->validate();
        
        try {
            $product = Product::find($this->selectedProductId);
            
            if (!$product) {
                throw new \Exception('Produk tidak ditemukan.');
            }

            // If product has variants, variant must be selected
            if ($product->has_variants && !$this->selectedVariantId) {
                throw new \Exception('Pilih varian terlebih dahulu.');
            }

            app(ProductService::class)->adjustStock(
                $this->selectedProductId,
                $this->adjustType,
                $this->adjustQuantity,
                $this->adjustReason,
                $this->selectedVariantId
            );
            
            $this->closeAdjustModal();
            Cache::forget('stock:stats');
            $this->dispatch('notify', type: 'success', message: 'Stok berhasil diperbarui');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    /**
     * Quick increment for non-variant products only
     */
    public function quickIncrement(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;
        
        if ($product->has_variants) {
            // Expand to show variants instead
            if (!in_array($productId, $this->expandedProducts)) {
                $this->expandedProducts[] = $productId;
            }
            $this->dispatch('notify', type: 'info', message: 'Pilih varian untuk adjust stok');
            return;
        }
        
        $this->doQuickAdjust($productId, 'in', 1, 'Quick +1');
    }

    /**
     * Quick decrement for non-variant products only
     */
    public function quickDecrement(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;
        
        if ($product->has_variants) {
            if (!in_array($productId, $this->expandedProducts)) {
                $this->expandedProducts[] = $productId;
            }
            $this->dispatch('notify', type: 'info', message: 'Pilih varian untuk adjust stok');
            return;
        }
        
        if ($product->stock <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Stok sudah habis');
            return;
        }
        
        $this->doQuickAdjust($productId, 'out', 1, 'Quick -1');
    }

    /**
     * Quick increment for variant
     */
    public function quickIncrementVariant(int $variantId): void
    {
        $variant = ProductVariant::find($variantId);
        if (!$variant) return;
        
        $this->doQuickAdjust($variant->product_id, 'in', 1, 'Quick +1', $variantId);
    }

    /**
     * Quick decrement for variant
     */
    public function quickDecrementVariant(int $variantId): void
    {
        $variant = ProductVariant::find($variantId);
        if (!$variant) return;
        
        if ($variant->stock <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Stok varian sudah habis');
            return;
        }
        
        $this->doQuickAdjust($variant->product_id, 'out', 1, 'Quick -1', $variantId);
    }

    private function doQuickAdjust(int $productId, string $type, int $qty, string $reason, ?int $variantId = null): void
    {
        try {
            app(ProductService::class)->adjustStock($productId, $type, $qty, $reason, $variantId);
            Cache::forget('stock:stats');
            $this->dispatch('notify', type: 'success', message: 'Stok diperbarui');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    // Bulk operations
    public function toggleProductSelection(int $productId): void
    {
        $product = Product::find($productId);
        // Skip variant products for bulk selection
        if ($product && $product->has_variants) {
            $this->dispatch('notify', type: 'info', message: 'Produk varian tidak dapat dipilih untuk bulk adjust');
            return;
        }
        
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_values(array_diff($this->selectedProducts, [$productId]));
        } else {
            $this->selectedProducts[] = $productId;
        }
    }

    public function selectAllVisible(): void
    {
        // Only select non-variant products
        $ids = $this->products->filter(fn($p) => !$p->has_variants)->pluck('id')->toArray();
        $this->selectedProducts = count($this->selectedProducts) === count($ids) ? [] : $ids;
    }

    public function clearSelection(): void { $this->selectedProducts = []; }

    public function openBulkModal(): void
    {
        if (empty($this->selectedProducts)) return;
        $this->showBulkModal = true;
    }

    public function closeBulkModal(): void
    {
        $this->showBulkModal = false;
        $this->reset(['bulkReason']);
    }

    public function saveBulkAdjustment(): void
    {
        $this->validate([
            'adjustQuantity' => 'required|integer|min:1',
            'bulkReason' => 'required|string|min:2|max:255',
        ]);

        $service = app(ProductService::class);
        $success = 0;

        DB::transaction(function () use ($service, &$success) {
            foreach ($this->selectedProducts as $id) {
                try {
                    $service->adjustStock($id, $this->bulkType, $this->adjustQuantity, $this->bulkReason);
                    $success++;
                } catch (\Exception $e) {}
            }
        });

        $this->closeBulkModal();
        $this->clearSelection();
        Cache::forget('stock:stats');
        $this->dispatch('notify', type: 'success', message: "{$success} produk diperbarui");
    }

    #[Computed(persist: true)]
    public function stats(): array
    {
        return Cache::remember('stock:stats', 120, function () {
            $r = Product::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN stock <= min_stock AND stock > 0 THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN stock > min_stock THEN 1 ELSE 0 END) as normal_stock,
                COALESCE(SUM(stock * price), 0) as stock_value,
                COALESCE(SUM(stock * cost_price), 0) as stock_cost,
                COALESCE(SUM(stock * (price - cost_price)), 0) as stock_profit
            ')->first();
            
            return [
                'total' => (int) $r->total,
                'low' => (int) $r->low_stock,
                'out' => (int) $r->out_of_stock,
                'normal' => (int) $r->normal_stock,
                'value' => (float) $r->stock_value,
                'cost' => (float) $r->stock_cost,
                'profit' => (float) $r->stock_profit,
            ];
        });
    }

    #[Computed]
    public function categories(): array
    {
        return Cache::remember('product:categories', 300, fn() => 
            Product::whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->toArray()
        );
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->select(['id', 'name', 'sku', 'category', 'stock', 'min_stock', 'price', 'cost_price', 'image', 'has_variants'])
            ->with(['variants' => fn($q) => $q->where('is_active', true)->orderBy('variant_name')])
            ->when($this->search, fn($q) => $q->where(fn($sub) => 
                $sub->where('name', 'like', "%{$this->search}%")->orWhere('sku', 'like', "%{$this->search}%")
            ))
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->when($this->stockFilter === 'low', fn($q) => $q->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0))
            ->when($this->stockFilter === 'out', fn($q) => $q->where('stock', '<=', 0))
            ->when($this->stockFilter === 'normal', fn($q) => $q->whereColumn('stock', '>', 'min_stock'))
            ->orderByRaw('CASE WHEN stock <= 0 THEN 0 WHEN stock <= min_stock THEN 1 ELSE 2 END')
            ->orderBy('name')
            ->paginate(20);
    }

    #[Computed]
    public function selectedProduct(): ?Product
    {
        return $this->selectedProductId ? Product::with(['variants' => fn($q) => $q->where('is_active', true)])->find($this->selectedProductId) : null;
    }

    #[Computed]
    public function selectedVariant(): ?ProductVariant
    {
        return $this->selectedVariantId ? ProductVariant::find($this->selectedVariantId) : null;
    }

    #[Computed]
    public function adjustmentTarget(): ?object
    {
        if ($this->selectedVariantId) {
            return $this->selectedVariant;
        }
        return $this->selectedProduct;
    }

    #[Computed]
    public function adjustments()
    {
        return StockAdjustment::query()
            ->with(['product:id,name,sku', 'user:id,name', 'variant:id,variant_name'])
            ->when($this->historySearch, fn($q) => $q->whereHas('product', fn($sub) => 
                $sub->where('name', 'like', "%{$this->historySearch}%")
            ))
            ->when($this->historyType !== 'all', fn($q) => $q->where('type', $this->historyType))
            ->recent()
            ->paginate(15, pageName: 'historyPage');
    }

    public function render()
    {
        return view('livewire.stock.stock-manager');
    }
}
