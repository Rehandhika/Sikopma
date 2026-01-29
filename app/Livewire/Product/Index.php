<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Services\ActivityLogService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Product Index Component
 *
 * Displays and manages product list with filtering and search
 */
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $categoryFilter = '';

    public $stockFilter = '';

    /**
     * Reset pagination when search changes
     *
     * @return void
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Delete a product
     *
     * @param  int  $id
     * @return void
     */
    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if ($product) {
            $productName = $product->name;
            $product->delete();

            // Log activity
            ActivityLogService::logProductDeleted($productName);

            $this->dispatch('toast', message: 'Produk berhasil dihapus', type: 'success');
        }
    }

    /**
     * Render product list
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('sku', 'like', '%'.$this->search.'%'))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('stock', 0))
            ->orderBy('name')
            ->paginate(20);

        // Get unique categories from existing products
        $categories = Product::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('livewire.product.index', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('layouts.app')->title('Daftar Produk');
    }
}
