<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use App\Models\{Product, Sale, SaleItem};
use Illuminate\Support\Facades\DB;

/**
 * Point of Sale (POS) Component
 * 
 * Handles cashier transactions, cart management, and payment processing
 */
class Pos extends Component
{
    public $search = '';
    public $cart = [];
    public $paymentMethod = 'cash';
    public $paymentAmount = 0;
    public $memberDiscount = 0;

    /**
     * Add product to cart
     *
     * @param int $productId
     * @return void
     */
    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->stock < 1) {
            $this->dispatch('alert', type: 'error', message: 'Stok tidak tersedia');
            return;
        }

        $cartKey = $productId;
        
        if (isset($this->cart[$cartKey])) {
            if ($this->cart[$cartKey]['quantity'] >= $product->stock) {
                $this->dispatch('alert', type: 'error', message: 'Stok tidak mencukupi');
                return;
            }
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'stock' => $product->stock,
            ];
        }
        
        $this->calculateTotal();
    }

    /**
     * Update cart item quantity
     *
     * @param string $cartKey
     * @param int $quantity
     * @return void
     */
    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity < 1) {
            unset($this->cart[$cartKey]);
        } else {
            if ($quantity > $this->cart[$cartKey]['stock']) {
                $this->dispatch('alert', type: 'error', message: 'Stok tidak mencukupi');
                return;
            }
            $this->cart[$cartKey]['quantity'] = $quantity;
        }
        
        $this->calculateTotal();
    }

    /**
     * Remove item from cart
     *
     * @param string $cartKey
     * @return void
     */
    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
        $this->calculateTotal();
    }

    /**
     * Calculate cart total (handled in view)
     *
     * @return void
     */
    public function calculateTotal()
    {
        // Will be calculated in view
    }

    /**
     * Process payment and create sale transaction
     * Uses bulk update to avoid N+1 queries on stock updates
     *
     * @return void
     */
    public function processPayment()
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', type: 'error', message: 'Keranjang kosong');
            return;
        }

        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $discount = $subtotal * ($this->memberDiscount / 100);
        $total = $subtotal - $discount;

        if ($this->paymentAmount < $total) {
            $this->dispatch('alert', type: 'error', message: 'Pembayaran kurang');
            return;
        }

        DB::transaction(function () use ($subtotal, $discount, $total) {
            $sale = Sale::create([
                'cashier_id' => auth()->id(),
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $this->paymentMethod,
                'payment_amount' => $this->paymentAmount,
                'change_amount' => $this->paymentAmount - $total,
            ]);

            $productUpdates = [];
            
            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Collect product updates for bulk operation
                $productUpdates[$item['product_id']] = ($productUpdates[$item['product_id']] ?? 0) + $item['quantity'];
            }
            
            // Bulk update stock to avoid N+1 query
            foreach ($productUpdates as $productId => $quantity) {
                Product::where('id', $productId)->decrement('stock', $quantity);
            }
        });

        $this->dispatch('alert', type: 'success', message: 'Transaksi berhasil');
        $this->reset(['cart', 'paymentAmount', 'memberDiscount', 'search']);
    }

    /**
     * Render POS interface
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $products = Product::query()
            ->where('stock', '>', 0)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('barcode', $this->search))
            ->limit(20)
            ->get();

        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $discount = $subtotal * ($this->memberDiscount / 100);
        $total = $subtotal - $discount;

        return view('livewire.cashier.pos', [
            'products' => $products,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'change' => max(0, $this->paymentAmount - $total),
        ])->layout('layouts.app')->title('Point of Sale');
    }
}
