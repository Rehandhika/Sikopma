<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use App\Models\{Product, Sale, SaleItem};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

#[Title('Point of Sale')]
class Pos extends Component
{
    // Search & Filter
    public string $search = '';
    public string $category = '';
    
    // Cart - using array for better performance
    public array $cart = [];
    
    // Payment
    public string $paymentMethod = 'cash';
    public int $paymentAmount = 0;
    
    // UI State
    public bool $showCart = false;
    public bool $showPayment = false;
    
    // Quick amounts - locked to prevent tampering
    #[Locked]
    public array $quickAmounts = [10000, 20000, 50000, 100000];

    protected $listeners = ['barcode-scanned' => 'handleBarcode'];

    #[Computed]
    public function products()
    {
        return Product::query()
            ->select(['id', 'name', 'sku', 'price', 'stock', 'category', 'image'])
            ->where('stock', '>', 0)
            ->where('status', 'active')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%");
                });
            })
            ->when($this->category, fn($q) => $q->where('category', $this->category))
            ->orderBy('name')
            ->limit(30)
            ->get();
    }

    #[Computed(persist: true)]
    public function categories()
    {
        return Cache::remember('pos_categories', 300, function () {
            return Product::query()
                ->where('stock', '>', 0)
                ->where('status', 'active')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category')
                ->sort()
                ->values();
        });
    }

    #[Computed]
    public function cartTotal(): int
    {
        return (int) collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    #[Computed]
    public function cartItemsCount(): int
    {
        return (int) collect($this->cart)->sum('quantity');
    }

    #[Computed]
    public function change(): int
    {
        return max(0, $this->paymentAmount - $this->cartTotal);
    }

    public function handleBarcode(string $barcode): void
    {
        $product = Product::where('sku', trim($barcode))->first();
        
        if ($product) {
            $this->addToCart($product->id);
        } else {
            $this->dispatch('alert', type: 'error', message: 'Produk tidak ditemukan');
        }
    }

    public function addToCart(int $productId): void
    {
        $product = Product::select(['id', 'name', 'price', 'stock', 'image'])->find($productId);
        
        if (!$product || $product->stock < 1) {
            $this->dispatch('alert', type: 'error', message: 'Stok tidak tersedia');
            return;
        }

        $cartKey = (string) $productId;
        $currentQty = $this->cart[$cartKey]['quantity'] ?? 0;
        
        if ($currentQty >= $product->stock) {
            $this->dispatch('alert', type: 'error', message: 'Stok tidak mencukupi');
            return;
        }

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (int) $product->price,
                'quantity' => 1,
                'stock' => $product->stock,
                'image' => $product->image_thumbnail_url,
            ];
        }
        
        $this->dispatch('item-added');
    }

    public function incrementQty(string $cartKey): void
    {
        if (!isset($this->cart[$cartKey])) return;
        
        if ($this->cart[$cartKey]['quantity'] >= $this->cart[$cartKey]['stock']) {
            $this->dispatch('alert', type: 'error', message: 'Stok tidak mencukupi');
            return;
        }
        
        $this->cart[$cartKey]['quantity']++;
    }

    public function decrementQty(string $cartKey): void
    {
        if (!isset($this->cart[$cartKey])) return;
        
        if ($this->cart[$cartKey]['quantity'] <= 1) {
            $this->removeFromCart($cartKey);
        } else {
            $this->cart[$cartKey]['quantity']--;
        }
    }

    public function updateQuantity(string $cartKey, int $quantity): void
    {
        if (!isset($this->cart[$cartKey])) return;
        
        if ($quantity < 1) {
            $this->removeFromCart($cartKey);
            return;
        }
        
        if ($quantity > $this->cart[$cartKey]['stock']) {
            $this->dispatch('alert', type: 'error', message: 'Stok tidak mencukupi');
            return;
        }
        
        $this->cart[$cartKey]['quantity'] = $quantity;
    }

    public function removeFromCart(string $cartKey): void
    {
        unset($this->cart[$cartKey]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->paymentAmount = 0;
        $this->showPayment = false;
    }

    public function setQuickAmount(int $amount): void
    {
        $this->paymentAmount = $amount;
    }

    public function setExactAmount(): void
    {
        $this->paymentAmount = $this->cartTotal;
    }

    public function openPayment(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', type: 'error', message: 'Keranjang kosong');
            return;
        }
        
        $this->paymentAmount = 0;
        $this->showPayment = true;
    }

    public function closePayment(): void
    {
        $this->showPayment = false;
    }

    public function toggleCart(): void
    {
        $this->showCart = !$this->showCart;
    }

    public function processPayment(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', type: 'error', message: 'Keranjang kosong');
            return;
        }

        $total = $this->cartTotal;

        // For QRIS, set payment amount to exact total
        if ($this->paymentMethod === 'qris') {
            $this->paymentAmount = $total;
        }

        if ($this->paymentAmount < $total) {
            $this->dispatch('alert', type: 'error', message: 'Pembayaran kurang dari total');
            return;
        }

        try {
            DB::transaction(function () use ($total) {
                // Create sale record
                $sale = Sale::create([
                    'cashier_id' => auth()->id(),
                    'invoice_number' => Sale::generateInvoiceNumber(),
                    'date' => now()->toDateString(),
                    'total_amount' => $total,
                    'payment_method' => $this->paymentMethod,
                    'payment_amount' => $this->paymentAmount,
                    'change_amount' => $this->paymentAmount - $total,
                ]);

                $saleItems = [];
                $stockUpdates = [];
                $now = now();
                
                foreach ($this->cart as $item) {
                    $saleItems[] = [
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['quantity'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $stockUpdates[$item['product_id']] = ($stockUpdates[$item['product_id']] ?? 0) + $item['quantity'];
                }
                
                // Bulk insert sale items
                SaleItem::insert($saleItems);
                
                // Bulk update stock using single query per product
                foreach ($stockUpdates as $productId => $quantity) {
                    Product::where('id', $productId)->decrement('stock', $quantity);
                }

                // Dispatch success event
                $this->dispatch('payment-success', [
                    'invoice' => $sale->invoice_number,
                    'total' => $total,
                    'payment' => $this->paymentAmount,
                    'change' => $this->paymentAmount - $total,
                    'method' => $this->paymentMethod,
                ]);
            });

            $this->dispatch('alert', type: 'success', message: 'Transaksi berhasil!');
            
            // Reset state
            $this->reset(['cart', 'paymentAmount', 'showPayment', 'showCart']);
            $this->paymentMethod = 'cash';
            
            // Clear categories cache to reflect stock changes
            Cache::forget('pos_categories');
            
        } catch (\Exception $e) {
            report($e);
            $this->dispatch('alert', type: 'error', message: 'Gagal memproses transaksi');
        }
    }

    public function render()
    {
        return view('livewire.cashier.pos')->layout('layouts.pos');
    }
}
