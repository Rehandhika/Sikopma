<?php

namespace App\Livewire\Stock;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\{Product, StockAdjustment as StockAdjustmentModel};
use App\Services\ProductService;
use Illuminate\Support\Facades\DB;

#[Title('Penyesuaian Stok')]
class StockAdjustment extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = 'all';
    public $productFilter = 'all';
    
    // Form properties
    public $showModal = false;
    public $product_id = '';
    public $type = 'in';
    public $quantity = 0;
    public $reason = '';

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'type' => 'required|in:in,out',
        'quantity' => 'required|integer|min:1',
        'reason' => 'required|string|max:500',
    ];

    protected $messages = [
        'product_id.required' => 'Produk wajib dipilih',
        'product_id.exists' => 'Produk tidak valid',
        'type.required' => 'Tipe penyesuaian wajib dipilih',
        'quantity.required' => 'Jumlah wajib diisi',
        'quantity.min' => 'Jumlah minimal 1',
        'reason.required' => 'Alasan wajib diisi',
        'reason.max' => 'Alasan maksimal 500 karakter',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    /**
     * Save stock adjustment
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        $productService = app(ProductService::class);
        
        try {
            $productService->adjustStock(
                $this->product_id,
                $this->type,
                $this->quantity,
                $this->reason
            );
            
            $this->dispatch('alert', type: 'success', message: 'Penyesuaian stok berhasil disimpan');
            $this->resetForm();
            
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->reset(['product_id', 'type', 'quantity', 'reason', 'showModal']);
        $this->resetValidation();
    }

    public function getProductsProperty()
    {
        return Product::active()
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $adjustments = StockAdjustmentModel::query()
            ->with(['product', 'user', 'variant'])
            ->when($this->search, function($q) {
                $q->whereHas('product', function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->typeFilter !== 'all', fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->productFilter !== 'all', fn($q) => $q->where('product_id', $this->productFilter))
            ->recent()
            ->paginate(20);

        $stats = [
            'total_adjustments' => StockAdjustmentModel::count(),
            'total_additions' => StockAdjustmentModel::additions()->sum('quantity'),
            'total_reductions' => StockAdjustmentModel::reductions()->sum('quantity'),
            'low_stock_products' => Product::lowStock()->count(),
        ];

        return view('livewire.stock.stock-adjustment', [
            'adjustments' => $adjustments,
            'stats' => $stats,
            'products' => $this->products,
        ])->layout('layouts.app');
    }
}
