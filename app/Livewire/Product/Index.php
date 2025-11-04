<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Product, ProductCategory};

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $stockFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteProduct($id)
    {
        Product::find($id)?->delete();
        $this->dispatch('alert', type: 'success', message: 'Produk berhasil dihapus');
    }

    public function render()
    {
        $products = Product::query()
            ->with('category')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('sku', 'like', '%' . $this->search . '%')
                ->orWhere('barcode', 'like', '%' . $this->search . '%'))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->stockFilter === 'low', fn($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->when($this->stockFilter === 'out', fn($q) => $q->where('stock', 0))
            ->orderBy('name')
            ->paginate(20);

        $categories = ProductCategory::all();

        return view('livewire.product.index', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('layouts.app')->title('Daftar Produk');
    }
}
