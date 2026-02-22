<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Services\ProductService;
use Livewire\Component;

class ProductForm extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form properties
    public $name = '';
    public $sku = '';
    public $price = 0;
    public $stock = 0;
    public $min_stock = 5;
    public $category = '';
    public $description = '';
    public $status = 'active';

    protected $listeners = ['openProductModal' => 'open'];

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

    public function open($params = [])
    {
        $this->resetForm();
        
        if (isset($params['id'])) {
            $this->loadProduct($params['id']);
        }
        
        $this->showModal = true;
    }

    public function loadProduct($id)
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
    }

    public function save()
    {
        if ($this->editingId) {
            $this->rules['sku'] = 'nullable|string|max:50|unique:products,sku,'.$this->editingId;
        }

        $validated = $this->validate();
        $productService = app(ProductService::class);

        try {
            if ($this->editingId) {
                $productService->update($this->editingId, $validated);
                $this->dispatch('toast', message: 'Produk berhasil diperbarui', type: 'success');
            } else {
                if (empty($validated['sku'])) {
                    $validated['sku'] = $productService->generateSKU($validated['name'], $validated['category'] ?? null);
                }
                $productService->create($validated);
                $this->dispatch('toast', message: 'Produk berhasil ditambahkan', type: 'success');
            }
            
            $this->showModal = false;
            $this->dispatch('product-saved'); // To refresh parent list if needed
            
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    private function resetForm()
    {
        $this->reset([
            'editingId', 'name', 'sku', 'price', 'stock',
            'min_stock', 'category', 'description', 'status',
        ]);

        $this->price = 0;
        $this->stock = 0;
        $this->min_stock = 5;
        $this->status = 'active';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.product.product-form');
    }
}
