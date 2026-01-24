<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use App\Models\{Product, ProductVariant, Sale, SaleItem};
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
    
    // Variant Selection
    public bool $showVariantModal = false;
    public ?int $selectedProductId = null;
    public array $productVariants = [];
    public array $groupedVariants = [];
    public ?string $selectedProductName = null;
    
    // Quick amounts - locked to prevent tampering
    #[Locked]
    public array $quickAmounts = [10000, 20000, 50000, 100000];

    protected $listeners = ['barcode-scanned' => 'handleBarcode'];

    #[Computed]
    public function products()
    {
        return Product::query()
            ->select(['id', 'name', 'sku', 'price', 'stock', 'category', 'image', 'has_variants'])
            ->with(['activeVariants' => function ($q) {
                $q->select(['id', 'product_id', 'variant_name', 'price', 'stock', 'option_values'])
                    ->where('stock', '>', 0);
            }])
            ->where('status', 'active')
            ->where(function ($q) {
                // Products without variants must have stock > 0
                // Products with variants must have at least one variant with stock > 0
                $q->where(function ($query) {
                    $query->where('has_variants', false)->where('stock', '>', 0);
                })->orWhere(function ($query) {
                    $query->where('has_variants', true)
                        ->whereHas('activeVariants', fn($v) => $v->where('stock', '>', 0));
                });
            })
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
        $product = Product::select(['id', 'name', 'price', 'stock', 'image', 'has_variants'])
            ->with(['activeVariants' => fn($q) => $q->where('stock', '>', 0)])
            ->find($productId);
        
        if (!$product) {
            $this->dispatch('alert', type: 'error', message: 'Produk tidak ditemukan');
            return;
        }

        // If product has variants, show variant selection modal
        if ($product->has_variants) {
            $this->selectedProductId = $productId;
            $this->selectedProductName = $product->name;
            $this->productVariants = $product->activeVariants->toArray();
            $this->groupedVariants = $this->getGroupedVariantsData($this->productVariants);
            $this->showVariantModal = true;
            return;
        }

        // Non-variant product - add directly
        if ($product->stock < 1) {
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
                'variant_id' => null,
                'name' => $product->name,
                'price' => (int) $product->price,
                'quantity' => 1,
                'stock' => $product->stock,
                'image' => $product->image_thumbnail_url,
            ];
        }
        
        $this->dispatch('item-added');
    }

    public function addVariantToCart(int $variantId): void
    {
        $variant = ProductVariant::with('product')->find($variantId);
        
        if (!$variant || $variant->stock < 1) {
            $this->dispatch('alert', type: 'error', message: 'Stok varian tidak tersedia');
            return;
        }

        // Use variant_id as cart key for variant products
        $cartKey = 'v_' . $variantId;
        $currentQty = $this->cart[$cartKey]['quantity'] ?? 0;
        
        // Real-time stock validation - check current stock from database
        $freshStock = ProductVariant::where('id', $variantId)->value('stock');
        
        if ($currentQty >= $freshStock) {
            $this->dispatch('alert', type: 'error', message: "Stok tidak mencukupi. Tersedia: {$freshStock}");
            return;
        }

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
            $this->cart[$cartKey]['stock'] = $freshStock; // Update stock info
        } else {
            $this->cart[$cartKey] = [
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'name' => $variant->variant_name,
                'price' => (int) $variant->price,
                'quantity' => 1,
                'stock' => $freshStock,
                'image' => $variant->product->image_thumbnail_url ?? null,
            ];
        }
        
        $this->closeVariantModal();
        $this->dispatch('item-added');
    }

    public function closeVariantModal(): void
    {
        $this->showVariantModal = false;
        $this->selectedProductId = null;
        $this->productVariants = [];
        $this->groupedVariants = [];
        $this->selectedProductName = null;
    }

    /**
     * Group variants by option type for better display
     * Requirements: 3.1, 3.2
     */
    protected function getGroupedVariantsData(array $variants): array
    {
        $grouped = [];
        
        foreach ($variants as $variant) {
            $optionValues = $variant['option_values'] ?? [];
            
            // Build group key from option values
            foreach ($optionValues as $optionSlug => $optionData) {
                $optionName = $optionData['option_name'] ?? ucfirst($optionSlug);
                
                if (!isset($grouped[$optionSlug])) {
                    $grouped[$optionSlug] = [
                        'option_name' => $optionName,
                        'values' => [],
                    ];
                }
                
                $value = $optionData['value'] ?? '';
                if (!isset($grouped[$optionSlug]['values'][$value])) {
                    $grouped[$optionSlug]['values'][$value] = [
                        'value' => $value,
                        'variants' => [],
                        'total_stock' => 0,
                        'min_price' => PHP_INT_MAX,
                        'max_price' => 0,
                    ];
                }
                
                // Add variant to this value group
                $grouped[$optionSlug]['values'][$value]['variants'][] = $variant;
                $grouped[$optionSlug]['values'][$value]['total_stock'] += $variant['stock'];
                $grouped[$optionSlug]['values'][$value]['min_price'] = min(
                    $grouped[$optionSlug]['values'][$value]['min_price'],
                    $variant['price']
                );
                $grouped[$optionSlug]['values'][$value]['max_price'] = max(
                    $grouped[$optionSlug]['values'][$value]['max_price'],
                    $variant['price']
                );
            }
        }
        
        // Convert values to indexed array and sort
        foreach ($grouped as $optionSlug => &$optionData) {
            $optionData['values'] = array_values($optionData['values']);
            // Sort values alphabetically
            usort($optionData['values'], fn($a, $b) => strcmp($a['value'], $b['value']));
        }
        
        return $grouped;
    }

    public function incrementQty(string $cartKey): void
    {
        if (!isset($this->cart[$cartKey])) return;
        
        // Get fresh stock from database for real-time validation
        $item = $this->cart[$cartKey];
        if (!empty($item['variant_id'])) {
            $currentStock = ProductVariant::where('id', $item['variant_id'])->value('stock') ?? 0;
        } else {
            $currentStock = Product::where('id', $item['product_id'])->value('stock') ?? 0;
        }
        
        // Update stock in cart
        $this->cart[$cartKey]['stock'] = $currentStock;
        
        if ($this->cart[$cartKey]['quantity'] >= $currentStock) {
            $this->dispatch('alert', type: 'error', message: "Stok tidak mencukupi. Tersedia: {$currentStock}");
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
        
        // Validate stock availability before opening payment
        $stockIssues = $this->validateCartStock();
        if (!empty($stockIssues)) {
            $this->dispatch('alert', type: 'error', message: $stockIssues[0]);
            return;
        }
        
        $this->paymentAmount = 0;
        $this->showPayment = true;
    }

    /**
     * Validate stock availability for all cart items
     * Requirements: 3.5, 3.6
     * 
     * @return array List of stock issues (empty if all valid)
     */
    public function validateCartStock(): array
    {
        $issues = [];
        
        foreach ($this->cart as $cartKey => $item) {
            if (!empty($item['variant_id'])) {
                // Variant product - check variant stock
                $currentStock = ProductVariant::where('id', $item['variant_id'])->value('stock');
                
                if ($currentStock === null) {
                    $issues[] = "Varian '{$item['name']}' tidak ditemukan";
                    continue;
                }
                
                if ($currentStock < $item['quantity']) {
                    $issues[] = "Stok '{$item['name']}' tidak mencukupi. Tersedia: {$currentStock}, Di keranjang: {$item['quantity']}";
                    // Update cart with current stock
                    $this->cart[$cartKey]['stock'] = $currentStock;
                }
            } else {
                // Non-variant product - check product stock
                $currentStock = Product::where('id', $item['product_id'])->value('stock');
                
                if ($currentStock === null) {
                    $issues[] = "Produk '{$item['name']}' tidak ditemukan";
                    continue;
                }
                
                if ($currentStock < $item['quantity']) {
                    $issues[] = "Stok '{$item['name']}' tidak mencukupi. Tersedia: {$currentStock}, Di keranjang: {$item['quantity']}";
                    // Update cart with current stock
                    $this->cart[$cartKey]['stock'] = $currentStock;
                }
            }
        }
        
        return $issues;
    }

    /**
     * Refresh stock information for all cart items
     */
    public function refreshCartStock(): void
    {
        foreach ($this->cart as $cartKey => $item) {
            if (!empty($item['variant_id'])) {
                $currentStock = ProductVariant::where('id', $item['variant_id'])->value('stock') ?? 0;
            } else {
                $currentStock = Product::where('id', $item['product_id'])->value('stock') ?? 0;
            }
            
            $this->cart[$cartKey]['stock'] = $currentStock;
            
            // Auto-adjust quantity if exceeds stock
            if ($this->cart[$cartKey]['quantity'] > $currentStock) {
                if ($currentStock > 0) {
                    $this->cart[$cartKey]['quantity'] = $currentStock;
                    $this->dispatch('alert', type: 'warning', message: "Kuantitas '{$item['name']}' disesuaikan ke {$currentStock}");
                } else {
                    unset($this->cart[$cartKey]);
                    $this->dispatch('alert', type: 'warning', message: "'{$item['name']}' dihapus karena stok habis");
                }
            }
        }
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

        // Final stock validation before processing - Requirements: 3.5, 3.6
        $stockIssues = $this->validateCartStock();
        if (!empty($stockIssues)) {
            $this->dispatch('alert', type: 'error', message: 'Stok berubah: ' . $stockIssues[0]);
            $this->showPayment = false;
            return;
        }

        try {
            DB::transaction(function () use ($total) {
                // Re-validate stock with row locking to prevent race conditions
                foreach ($this->cart as $item) {
                    if (!empty($item['variant_id'])) {
                        $variant = ProductVariant::lockForUpdate()->find($item['variant_id']);
                        if (!$variant || $variant->stock < $item['quantity']) {
                            throw new \Exception("Stok varian '{$item['name']}' tidak mencukupi");
                        }
                    } else {
                        $product = Product::lockForUpdate()->find($item['product_id']);
                        if (!$product || $product->stock < $item['quantity']) {
                            throw new \Exception("Stok produk '{$item['name']}' tidak mencukupi");
                        }
                    }
                }

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
                $variantStockUpdates = [];
                $now = now();
                
                foreach ($this->cart as $item) {
                    $saleItems[] = [
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'] ?? null,
                        'product_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['quantity'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // Track stock updates separately for products and variants
                    if (!empty($item['variant_id'])) {
                        $variantStockUpdates[$item['variant_id']] = ($variantStockUpdates[$item['variant_id']] ?? 0) + $item['quantity'];
                    } else {
                        $stockUpdates[$item['product_id']] = ($stockUpdates[$item['product_id']] ?? 0) + $item['quantity'];
                    }
                }
                
                // Bulk insert sale items
                SaleItem::insert($saleItems);
                
                // Bulk update product stock
                foreach ($stockUpdates as $productId => $quantity) {
                    Product::where('id', $productId)->decrement('stock', $quantity);
                }

                // Bulk update variant stock
                foreach ($variantStockUpdates as $variantId => $quantity) {
                    ProductVariant::where('id', $variantId)->decrement('stock', $quantity);
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
            $this->dispatch('alert', type: 'error', message: $e->getMessage() ?: 'Gagal memproses transaksi');
        }
    }

    public function render()
    {
        return view('livewire.cashier.pos')->layout('layouts.pos');
    }
}
