<?php

namespace App\Livewire\Product;

use Livewire\Component;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

#[Layout('layouts.app')]
#[Title('Edit Produk')]
class EditProduct extends Component
{
    public Product $product;

    public $name = '';
    public $sku = '';
    public $price = '';
    public $stock = 0;
    public $min_stock = 5;
    public $category = '';
    public $description = '';
    public $status = 'active';

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->price = $product->price;
        $this->stock = $product->stock;
        $this->min_stock = $product->min_stock;
        $this->category = $product->category;
        $this->description = $product->description;
        $this->status = $product->status;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($this->product->id)],
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function save()
    {
        $this->validate();

        $this->product->update([
            'name' => $this->name,
            'sku' => $this->sku ?: null,
            'price' => $this->price,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'category' => $this->category,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Produk berhasil diperbarui.');

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.product.edit-product');
    }
}
