<?php

namespace App\Livewire\Stock;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\{Product, StockAdjustment as StockAdjustmentModel};
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
    public $type = 'addition';
    public $quantity = 0;
    public $reason = '';

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'type' => 'required|in:addition,reduction',
        'quantity' => 'required|integer|min:1',
        'reason' => 'required|string|max:500',
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

    public function save()
    {
        $this->validate();

        $product = Product::findOrFail($this->product_id);
        
        // Check if reduction is valid
        if ($this->type === 'reduction' && $product->stock < $this->quantity) {
            session()->flash('error', 'Stok tidak mencukupi untuk pengurangan');
            return;
        }

        DB::beginTransaction();
        
        try {
            $previousStock = $product->stock;
            
            // Update product stock
            if ($this->type === 'addition') {
                $product->increaseStock($this->quantity);
            } else {
                $product->decreaseStock($this->quantity);
            }
            
            $newStock = $product->fresh()->stock;

            // Create adjustment record
            StockAdjustmentModel::create([
                'user_id' => auth()->id(),
                'product_id' => $this->product_id,
                'type' => $this->type,
                'quantity' => $this->quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $this->reason,
            ]);

            DB::commit();
            
            session()->flash('success', 'Penyesuaian stok berhasil disimpan');
            $this->resetForm();
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
            ->with(['product', 'user'])
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
