<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\Attributes\{Title, On};
use App\Models\{Product, Sale, SaleItem};
use Illuminate\Support\Facades\DB;

#[Title('Point of Sale - Kasir')]
class TransactionForm extends Component
{
    public $searchProduct = '';
    public $cart = [];
    public $paymentMethod = 'cash';
    public $paymentAmount = 0;
    public $notes = '';
    public $showPaymentModal = false;
    
    protected $rules = [
        'paymentMethod' => 'required|in:cash,transfer,qris',
        'paymentAmount' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->cart = [];
    }

    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId);
        
        if (!$product->canSell(1)) {
            session()->flash('error', 'Produk tidak tersedia atau stok habis');
            return;
        }

        $cartKey = 'product_' . $productId;
        
        if (isset($this->cart[$cartKey])) {
            $newQty = $this->cart[$cartKey]['quantity'] + 1;
            
            if (!$product->canSell($newQty)) {
                session()->flash('error', 'Stok tidak mencukupi');
                return;
            }
            
            $this->cart[$cartKey]['quantity'] = $newQty;
            $this->cart[$cartKey]['subtotal'] = $newQty * $product->price;
        } else {
            $this->cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price,
                'stock' => $product->stock,
            ];
        }
        
        $this->searchProduct = '';
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity <= 0) {
            unset($this->cart[$cartKey]);
            return;
        }

        if (isset($this->cart[$cartKey])) {
            $product = Product::find($this->cart[$cartKey]['product_id']);
            
            if (!$product || !$product->canSell($quantity)) {
                session()->flash('error', 'Stok tidak mencukupi');
                return;
            }
            
            $this->cart[$cartKey]['quantity'] = $quantity;
            $this->cart[$cartKey]['subtotal'] = $quantity * $this->cart[$cartKey]['price'];
        }
    }

    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->reset(['paymentAmount', 'paymentMethod', 'notes']);
    }

    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang kosong');
            return;
        }
        
        $this->paymentAmount = $this->getTotal();
        $this->showPaymentModal = true;
    }

    public function processPayment()
    {
        $this->validate();

        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang kosong');
            return;
        }

        $total = $this->getTotal();
        
        if ($this->paymentMethod === 'cash' && $this->paymentAmount < $total) {
            session()->flash('error', 'Jumlah pembayaran kurang');
            return;
        }

        DB::beginTransaction();
        
        try {
            // Create sale
            $sale = Sale::create([
                'cashier_id' => auth()->id(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'date' => now()->toDateString(),
                'total_amount' => $total,
                'payment_method' => $this->paymentMethod,
                'payment_amount' => $this->paymentAmount,
                'change_amount' => max(0, $this->paymentAmount - $total),
                'notes' => $this->notes,
            ]);

            // Create sale items and update stock
            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Decrease product stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decreaseStock($item['quantity']);
                }
            }

            DB::commit();
            
            session()->flash('success', 'Transaksi berhasil! Invoice: ' . $sale->invoice_number);
            session()->flash('sale_id', $sale->id);
            
            $this->clearCart();
            $this->showPaymentModal = false;
            
            $this->dispatch('sale-completed', saleId: $sale->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }

    public function getTotal()
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function getSearchResultsProperty()
    {
        if (strlen($this->searchProduct) < 2) {
            return collect();
        }

        return Product::active()
            ->inStock()
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->searchProduct . '%')
                      ->orWhere('sku', 'like', '%' . $this->searchProduct . '%');
            })
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.cashier.transaction-form', [
            'searchResults' => $this->searchResults,
            'total' => $this->getTotal(),
            'change' => max(0, $this->paymentAmount - $this->getTotal()),
        ])->layout('layouts.app');
    }
}
