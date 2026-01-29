<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Stock Management Index Component
 *
 * Displays product stock levels and statistics
 */
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $stockFilter = 'all'; // all, low, out

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
     * Render stock management view
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('sku', 'like', '%'.$this->search.'%'))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('stock', 0))
            ->orderBy('name')
            ->paginate(20);

        // Calculate comprehensive stats
        $stockStats = Product::selectRaw('
            COUNT(*) as total_products,
            SUM(CASE WHEN stock <= min_stock AND stock > 0 THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(stock * price) as total_stock_value,
            SUM(stock * cost_price) as total_stock_cost,
            SUM(stock * (price - cost_price)) as potential_profit
        ')->first();

        $stats = [
            'total_products' => (int) $stockStats->total_products,
            'low_stock' => (int) $stockStats->low_stock,
            'out_of_stock' => (int) $stockStats->out_of_stock,
            'total_stock_value' => (float) ($stockStats->total_stock_value ?? 0),
            'total_stock_cost' => (float) ($stockStats->total_stock_cost ?? 0),
            'potential_profit' => (float) ($stockStats->potential_profit ?? 0),
        ];

        return view('livewire.stock.index', [
            'products' => $products,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Manajemen Stok');
    }
}
