<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Product;
use App\Services\ProductService;
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

    /**
     * Handle route-based modal opening for create/edit
     */
    public function mount($id = null)
    {
        // If visiting products.create, open create modal immediately
        if (request()->routeIs('products.create')) {
            $this->create();
        }

        // If visiting products.edit with ID, load product and open edit modal
        if ($id && request()->routeIs('products.edit')) {
            $this->edit($id);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Open create modal
     *
     * @return void
     */
    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->dispatch('modal-opened');
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

    /**
     * Save product (create or update)
     *
     * @return void
     */
    public function save()
    {
        if ($this->editingId) {
            $this->rules['sku'] = 'nullable|string|max:50|unique:products,sku,' . $this->editingId;
        }

        $validated = $this->validate();
        $productService = app(ProductService::class);

        try {
            if ($this->editingId) {
                $productService->update($this->editingId, $validated);
                $this->dispatch('alert', type: 'success', message: 'Produk berhasil diperbarui');
            } else {
                // Generate SKU if not provided
                if (empty($validated['sku'])) {
                    $validated['sku'] = $productService->generateSKU($validated['name'], $validated['category'] ?? null);
                }
                $productService->create($validated);
                $this->dispatch('alert', type: 'success', message: 'Produk berhasil ditambahkan');
            }
            $this->resetForm();

            // If this component is accessed via create/edit routes, navigate back to list
            if (request()->routeIs('products.create') || request()->routeIs('products.edit')) {
                return $this->redirectRoute('products.list', navigate: true);
            }
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: $e->getMessage());
        }
    }

    /**
     * Delete product
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $productService = app(ProductService::class);
        
        try {
            $productService->delete($id);
            $this->dispatch('alert', type: 'success', message: 'Produk berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: $e->getMessage());
        }
    }

    /**
     * Toggle product status
     *
     * @param int $id
     * @return void
     */
    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        
        $productService = app(ProductService::class);
        $productService->update($id, ['status' => $newStatus]);
        
        $this->dispatch('alert', type: 'success', message: 'Status produk berhasil diubah');
    }

    private function resetForm()
    {
        $this->reset([
            'editingId', 'name', 'sku', 'price', 'stock',
            'min_stock', 'category', 'description', 'status'
        ]);

        // Set sensible defaults for create form
        $this->price = 0;
        $this->stock = 0;
        $this->min_stock = 5;
        $this->status = 'active';
        $this->showModal = false;

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
