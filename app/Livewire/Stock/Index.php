<?php

namespace App\Livewire\Stock;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $stockFilter = 'all'; // all, low, out

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('sku', 'like', '%' . $this->search . '%'))
            ->when($this->stockFilter === 'low', fn($q) => $q->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0))
            ->when($this->stockFilter === 'out', fn($q) => $q->where('stock', 0))
            ->with('category')
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total_products' => Product::count(),
            'low_stock' => Product::whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count(),
            'out_of_stock' => Product::where('stock', 0)->count(),
            'total_value' => Product::selectRaw('SUM(stock * cost) as total')->value('total') ?? 0,
        ];

        return view('livewire.stock.index', [
            'products' => $products,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Manajemen Stok');
    }
}
