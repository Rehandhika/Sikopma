<?php

namespace App\Livewire\Product;

use Livewire\Component;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Tambah Produk')]
class CreateProduct extends Component
{
    public $name = '';
    public $sku = '';
    public $price = '';
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

    public function save()
    {
        $this->validate();

        Product::create([
            'name' => $this->name,
            'sku' => $this->sku ?: null,
            'price' => $this->price,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'category' => $this->category,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Produk berhasil ditambahkan.');

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.product.create-product');
    }
}
