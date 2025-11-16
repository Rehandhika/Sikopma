<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Services\POSService;
use App\Services\PaymentService;
use App\Exceptions\BusinessException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnhancedPOS extends Component
{
    use WithPagination;

    public $cart = [];
    public $subtotal = 0;
    public $tax = 0;
    public $discount = 0;
    public $total = 0;
    public $paymentMethod = 'cash';
    public $amountPaid = 0;
    public $change = 0;
    public $customerName = '';
    public $customerPhone = '';
    public $notes = '';
    public $searchProduct = '';
    public $products = [];
    public $selectedCustomer = null;
    public $showPaymentModal = false;
    public $showReceiptModal = false;
    public $currentSale = null;
    public $printerAvailable = false;
    public $paymentMethods = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer Bank',
        'ewallet' => 'E-Wallet',
        'card' => 'Kartu Kredit/Debit',
    ];

    protected $posService;
    protected $paymentService;

    protected $listeners = [
        'productScanned' => 'handleProductScan',
        'paymentProcessed' => 'handlePaymentProcessed',
        'printReceipt' => 'handlePrintReceipt',
    ];

    protected $rules = [
        'cart.*.quantity' => 'required|integer|min:1',
        'paymentMethod' => 'required|in:cash,transfer,ewallet,card',
        'amountPaid' => 'required|numeric|min:0',
        'customerName' => 'nullable|string|max:255',
        'customerPhone' => 'nullable|string|max:20',
        'notes' => 'nullable|string|max:500',
    ];

    public function boot(POSService $posService, PaymentService $paymentService)
    {
        $this->posService = $posService;
        $this->paymentService = $paymentService;
    }

    public function mount()
    {
        $this->initializeCart();
        $this->loadProducts();
        $this->checkPrinterAvailability();
    }

    public function render()
    {
        return view('livewire.cashier.enhanced-pos', [
            'cart' => $this->cart,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'products' => $this->products,
            'paymentMethods' => $this->paymentMethods,
            'change' => $this->change,
        ])->layout('layouts.app')->title('Point of Sale - Enhanced');
    }

    protected function initializeCart()
    {
        $this->cart = [];
        $this->recalculateTotals();
    }

    protected function loadProducts()
    {
        $query = Product::where('status', 'active')
            ->where('stock', '>', 0);

        if ($this->searchProduct) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchProduct . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchProduct . '%')
                  ->orWhere('barcode', 'like', '%' . $this->searchProduct . '%');
            });
        }

        $this->products = $query->orderBy('name')->get();
    }

    protected function checkPrinterAvailability()
    {
        // Check if thermal printer is available
        $this->printerAvailable = $this->posService->isPrinterAvailable();
    }

    public function updatedSearchProduct()
    {
        $this->loadProducts();
    }

    public function addToCart($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            
            if ($product->stock <= 0) {
                $this->dispatch('error', 'Stok produk tidak tersedia');
                return;
            }

            // Check if product already in cart
            $existingIndex = collect($this->cart)->search(function ($item) use ($productId) {
                return $item['id'] === $productId;
            });

            if ($existingIndex !== false) {
                // Update quantity
                $newQuantity = $this->cart[$existingIndex]['quantity'] + 1;
                
                if ($newQuantity > $product->stock) {
                    $this->dispatch('error', 'Stok tidak mencukupi');
                    return;
                }
                
                $this->cart[$existingIndex]['quantity'] = $newQuantity;
                $this->cart[$existingIndex]['subtotal'] = $newQuantity * $product->price;
            } else {
                // Add new item
                $this->cart[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => 1,
                    'subtotal' => $product->price,
                    'stock' => $product->stock,
                ];
            }

            $this->recalculateTotals();
            $this->dispatch('productAdded', $product->name);

        } catch (\Exception $e) {
            Log::error('Failed to add product to cart', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Gagal menambahkan produk ke keranjang');
        }
    }

    public function updateCartItemQuantity($index, $quantity)
    {
        try {
            $quantity = (int) $quantity;
            
            if ($quantity <= 0) {
                $this->removeFromCart($index);
                return;
            }

            $product = Product::findOrFail($this->cart[$index]['id']);
            
            if ($quantity > $product->stock) {
                $this->dispatch('error', 'Stok tidak mencukupi');
                // Reset to max available stock
                $this->cart[$index]['quantity'] = $product->stock;
                $this->cart[$index]['subtotal'] = $product->stock * $this->cart[$index]['price'];
            } else {
                $this->cart[$index]['quantity'] = $quantity;
                $this->cart[$index]['subtotal'] = $quantity * $this->cart[$index]['price'];
            }

            $this->recalculateTotals();

        } catch (\Exception $e) {
            Log::error('Failed to update cart item quantity', [
                'index' => $index,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // Re-index array
        $this->recalculateTotals();
    }

    public function clearCart()
    {
        $this->initializeCart();
        $this->reset(['customerName', 'customerPhone', 'notes', 'selectedCustomer']);
    }

    protected function recalculateTotals()
    {
        $this->subtotal = collect($this->cart)->sum('subtotal');
        
        // Calculate tax (10%)
        $this->tax = round($this->subtotal * 0.1, 2);
        
        $this->total = $this->subtotal + $this->tax - $this->discount;
        
        // Calculate change for cash payments
        if ($this->paymentMethod === 'cash' && $this->amountPaid > 0) {
            $this->change = max(0, $this->amountPaid - $this->total);
        } else {
            $this->change = 0;
        }
    }

    public function updatedAmountPaid()
    {
        $this->recalculateTotals();
    }

    public function updatedPaymentMethod()
    {
        $this->recalculateTotals();
    }

    public function applyDiscount($amount)
    {
        $this->discount = min($amount, $this->subtotal);
        $this->recalculateTotals();
    }

    public function processPayment()
    {
        try {
            $this->validate();

            if (empty($this->cart)) {
                $this->dispatch('error', 'Keranjang belanja kosong');
                return;
            }

            if ($this->paymentMethod === 'cash' && $this->amountPaid < $this->total) {
                $this->dispatch('error', 'Pembayaran tunai tidak mencukupi');
                return;
            }

            // Process payment based on method
            $paymentResult = $this->paymentService->processPayment([
                'method' => $this->paymentMethod,
                'amount' => $this->total,
                'amount_paid' => $this->amountPaid,
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
            ]);

            if (!$paymentResult['success']) {
                $this->dispatch('error', $paymentResult['message']);
                return;
            }

            // Create sale
            $sale = $this->createSale($paymentResult);

            $this->currentSale = $sale;
            $this->showPaymentModal = false;
            $this->showReceiptModal = true;

            $this->dispatch('paymentSuccess', 'Pembayaran berhasil diproses');

        } catch (BusinessException $e) {
            $this->dispatch('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'cart' => $this->cart,
                'payment_method' => $this->paymentMethod,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('error', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }

    protected function createSale(array $paymentResult): Sale
    {
        try {
            return DB::transaction(function () use ($paymentResult) {
                // Create sale record
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'customer_name' => $this->customerName,
                    'customer_phone' => $this->customerPhone,
                    'subtotal' => $this->subtotal,
                    'tax' => $this->tax,
                    'discount' => $this->discount,
                    'total_amount' => $this->total,
                    'payment_method' => $this->paymentMethod,
                    'amount_paid' => $this->amountPaid,
                    'change' => $this->change,
                    'payment_reference' => $paymentResult['reference'] ?? null,
                    'status' => 'completed',
                    'notes' => $this->notes,
                ]);

                // Create sale items and update stock
                foreach ($this->cart as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Update product stock
                    Product::where('id', $item['id'])->decrement('stock', $item['quantity']);
                }

                Log::info('Sale created successfully', [
                    'sale_id' => $sale->id,
                    'total_amount' => $sale->total_amount,
                    'payment_method' => $sale->payment_method,
                ]);

                return $sale;
            });

        } catch (\Exception $e) {
            Log::error('Failed to create sale', [
                'cart' => $this->cart,
                'payment_result' => $paymentResult,
                'error' => $e->getMessage()
            ]);
            throw new BusinessException('Gagal membuat transaksi', 'SALE_CREATION_FAILED');
        }
    }

    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            $this->dispatch('error', 'Keranjang belanja kosong');
            return;
        }

        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
    }

    public function handleProductScan($barcode)
    {
        try {
            $product = Product::where('barcode', $barcode)
                ->where('status', 'active')
                ->where('stock', '>', 0)
                ->first();

            if ($product) {
                $this->addToCart($product->id);
                $this->dispatch('success', "Produk {$product->name} ditambahkan");
            } else {
                $this->dispatch('error', 'Produk tidak ditemukan atau stok habis');
            }

        } catch (\Exception $e) {
            Log::error('Failed to process scanned product', [
                'barcode' => $barcode,
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Gagal memproses barcode');
        }
    }

    public function handlePrintReceipt()
    {
        if (!$this->currentSale) {
            $this->dispatch('error', 'Tidak ada transaksi untuk dicetak');
            return;
        }

        try {
            $receiptData = $this->posService->generateReceiptData($this->currentSale);
            $printResult = $this->posService->printReceipt($receiptData);

            if ($printResult) {
                $this->dispatch('success', 'Struk berhasil dicetak');
            } else {
                $this->dispatch('error', 'Gagal mencetak struk');
            }

        } catch (\Exception $e) {
            Log::error('Failed to print receipt', [
                'sale_id' => $this->currentSale->id,
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Gagal mencetak struk');
        }
    }

    public function downloadReceipt()
    {
        if (!$this->currentSale) {
            $this->dispatch('error', 'Tidak ada transaksi untuk diunduh');
            return;
        }

        try {
            $receiptData = $this->posService->generateReceiptData($this->currentSale);
            $pdfPath = $this->posService->generateReceiptPDF($receiptData);

            $this->dispatch('receiptDownloaded', $pdfPath);

        } catch (\Exception $e) {
            Log::error('Failed to download receipt', [
                'sale_id' => $this->currentSale->id,
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Gagal mengunduh struk');
        }
    }

    public function emailReceipt()
    {
        if (!$this->currentSale) {
            $this->dispatch('error', 'Tidak ada transaksi untuk dikirim');
            return;
        }

        if (!$this->customerPhone && !filter_var($this->customerPhone, FILTER_VALIDATE_EMAIL)) {
            $this->dispatch('error', 'Email atau nomor telepon customer diperlukan');
            return;
        }

        try {
            $receiptData = $this->posService->generateReceiptData($this->currentSale);
            $sent = $this->posService->emailReceipt($receiptData, $this->customerPhone);

            if ($sent) {
                $this->dispatch('success', 'Struk berhasil dikirim');
            } else {
                $this->dispatch('error', 'Gagal mengirim struk');
            }

        } catch (\Exception $e) {
            Log::error('Failed to email receipt', [
                'sale_id' => $this->currentSale->id,
                'customer_contact' => $this->customerPhone,
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Gagal mengirim struk');
        }
    }

    public function newTransaction()
    {
        $this->showReceiptModal = false;
        $this->currentSale = null;
        $this->clearCart();
    }

    public function getDailySales()
    {
        try {
            return $this->posService->getDailySales(today());
        } catch (\Exception $e) {
            Log::error('Failed to get daily sales', [
                'error' => $e->getMessage()
            ]);
            return [
                'total_sales' => 0,
                'total_amount' => 0,
                'transactions' => 0,
            ];
        }
    }

    public function getLowStockProducts()
    {
        try {
            return Product::where('stock', '<=', \DB::raw('min_stock'))
                ->where('status', 'active')
                ->orderBy('stock', 'asc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to get low stock products', [
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    public function quickCashPayment()
    {
        $this->amountPaid = $this->total;
        $this->processPayment();
    }

    public function getPopularProducts()
    {
        try {
            return $this->posService->getPopularProducts(30); // Last 30 days
        } catch (\Exception $e) {
            Log::error('Failed to get popular products', [
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }
}
