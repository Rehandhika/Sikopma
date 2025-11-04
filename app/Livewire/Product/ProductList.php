<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

#[Title('Manajemen Produk')]
class ProductList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $categoryFilter = 'all';
    public $stockFilter = 'all';
    
    // Form properties
    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $sku = '';
    public $price = 0;
    public $stock = 0;
    public $min_stock = 5;
    public $category = '';
    public $description = '';
    public $status = 'active';

    protected $rules = [
        'name' => 'required|string|max:255',
        'sku' => 'nullable|string|max:50|unique:products,sku',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'min_stock' => 'required|integer|min:0',
        'category' => 'nullable|string|max:100',
        'description' => 'nullable|string',
        'status' => 'required|in:active,inactive',
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

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->editingId = $id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->price = $product->price;
        $this->stock = $product->stock;
        $this->min_stock = $product->min_stock;
        $this->category = $product->category;
        $this->description = $product->description;
        $this->status = $product->status;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->editingId) {
            $this->rules['sku'] = 'nullable|string|max:50|unique:products,sku,' . $this->editingId;
        }

        $validated = $this->validate();

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $product->update($validated);
            session()->flash('success', 'Produk berhasil diperbarui');
        } else {
            Product::create($validated);
            session()->flash('success', 'Produk berhasil ditambahkan');
        }

        $this->resetForm();
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        
        // Check if product has sales
        if ($product->saleItems()->count() > 0) {
            session()->flash('error', 'Tidak dapat menghapus produk yang sudah memiliki transaksi');
            return;
        }
        
        $product->delete();
        session()->flash('success', 'Produk berhasil dihapus');
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();
        
        session()->flash('success', 'Status produk berhasil diubah');
    }

    private function resetForm()
    {
        $this->reset([
            'editingId', 'name', 'sku', 'price', 'stock', 
            'min_stock', 'category', 'description', 'status', 'showModal'
        ]);
        $this->resetValidation();
    }

    public function getCategoriesProperty()
    {
        return Product::distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->sort();
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('sku', 'like', '%' . $this->search . '%')
                          ->orWhere('category', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter !== 'all', fn($q) => $q->where('category', $this->categoryFilter))
            ->when($this->stockFilter === 'low', fn($q) => $q->lowStock())
            ->when($this->stockFilter === 'out', fn($q) => $q->outOfStock())
            ->when($this->stockFilter === 'available', fn($q) => $q->inStock())
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Product::count(),
            'active' => Product::active()->count(),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
        ];

        return view('livewire.product.product-list', [
            'products' => $products,
            'stats' => $stats,
            'categories' => $this->categories,
        ])->layout('layouts.app');
    }
}
