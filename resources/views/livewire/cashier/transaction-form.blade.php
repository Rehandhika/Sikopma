<div class="h-screen bg-gray-100 flex flex-col">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         class="fixed top-4 right-4 z-50 max-w-md">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if (session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         class="fixed top-4 right-4 z-50 max-w-md">
        <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <div class="flex-1 flex overflow-hidden">
        {{-- Left Panel - Product Search & Selection --}}
        <div class="flex-1 flex flex-col bg-white">
            {{-- Header --}}
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Point of Sale</h2>
                <p class="text-sm text-gray-600">Kasir: {{ auth()->user()->name }}</p>
            </div>

            {{-- Product Search --}}
            <div class="p-4 border-b border-gray-200">
                <div class="relative">
                    <input wire:model.live.debounce.300ms="searchProduct" 
                           type="text" 
                           placeholder="Cari produk (nama atau SKU)..."
                           class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Product List --}}
            <div class="flex-1 overflow-y-auto p-4">
                @if($searchResults->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach($searchResults as $product)
                        <button wire:click="addToCart({{ $product->id }})" 
                                @class([
                                    'p-4 rounded-lg border-2 text-left transition hover:shadow-md',
                                    'border-gray-200 hover:border-blue-500' => !$product->isLowStock(),
                                    'border-yellow-300 bg-yellow-50' => $product->isLowStock(),
                                ])>
                            <h3 class="font-semibold text-gray-900 text-sm mb-1 truncate">
                                {{ $product->name }}
                            </h3>
                            <p class="text-lg font-bold text-blue-600 mb-2">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </p>
                            <div class="flex items-center justify-between text-xs">
                                <span @class([
                                    'px-2 py-1 rounded-full font-medium',
                                    'bg-green-100 text-green-800' => $product->stock > $product->min_stock,
                                    'bg-yellow-100 text-yellow-800' => $product->isLowStock(),
                                ])>
                                    Stok: {{ $product->stock }}
                                </span>
                            </div>
                        </button>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-lg font-medium">{{ $searchProduct ? 'Produk tidak ditemukan' : 'Ketik untuk mencari produk' }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Panel - Shopping Cart --}}
        <div class="w-96 bg-gray-50 border-l border-gray-200 flex flex-col">
            {{-- Cart Header --}}
            <div class="p-4 bg-blue-600 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold">Keranjang</h3>
                    @if(count($cart) > 0)
                    <button wire:click="clearCart" 
                            wire:confirm="Kosongkan keranjang?"
                            class="text-sm px-3 py-1 bg-white/20 hover:bg-white/30 rounded transition">
                        Kosongkan
                    </button>
                    @endif
                </div>
                <p class="text-sm text-blue-100 mt-1">{{ count($cart) }} item</p>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                @forelse($cart as $key => $item)
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 text-sm">{{ $item['name'] }}</h4>
                            <p class="text-sm text-gray-600">
                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </p>
                        </div>
                        <button wire:click="removeFromCart('{{ $key }}')" 
                                class="text-red-500 hover:text-red-700 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" 
                                    class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded flex items-center justify-center transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <input type="number" 
                                   wire:model.blur="cart.{{ $key }}.quantity"
                                   wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                                   min="1"
                                   max="{{ $item['stock'] }}"
                                   class="w-16 text-center border border-gray-300 rounded py-1">
                            <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" 
                                    @if($item['quantity'] >= $item['stock']) disabled @endif
                                    class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded flex items-center justify-center transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                        <div class="font-bold text-blue-600">
                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p class="font-medium">Keranjang kosong</p>
                    <p class="text-sm mt-1">Pilih produk untuk memulai transaksi</p>
                </div>
                @endforelse
            </div>

            {{-- Cart Summary & Checkout --}}
            <div class="p-4 bg-white border-t border-gray-200 space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-lg">
                        <span class="font-semibold text-gray-700">Total:</span>
                        <span class="text-2xl font-bold text-blue-600">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <button wire:click="openPaymentModal" 
                        @if(count($cart) === 0) disabled @endif
                        class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Proses Pembayaran</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-gray-900/75 flex items-center justify-center z-50" x-data="{ show: @entangle('showPaymentModal') }">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Proses Pembayaran</h3>

                {{-- Payment Method --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button wire:click="$set('paymentMethod', 'cash')" 
                                @class([
                                    'py-3 px-4 border-2 rounded-lg font-medium transition',
                                    'border-blue-600 bg-blue-50 text-blue-600' => $paymentMethod === 'cash',
                                    'border-gray-300 text-gray-700 hover:border-gray-400' => $paymentMethod !== 'cash',
                                ])>
                            Cash
                        </button>
                        <button wire:click="$set('paymentMethod', 'transfer')" 
                                @class([
                                    'py-3 px-4 border-2 rounded-lg font-medium transition',
                                    'border-blue-600 bg-blue-50 text-blue-600' => $paymentMethod === 'transfer',
                                    'border-gray-300 text-gray-700 hover:border-gray-400' => $paymentMethod !== 'transfer',
                                ])>
                            Transfer
                        </button>
                        <button wire:click="$set('paymentMethod', 'qris')" 
                                @class([
                                    'py-3 px-4 border-2 rounded-lg font-medium transition',
                                    'border-blue-600 bg-blue-50 text-blue-600' => $paymentMethod === 'qris',
                                    'border-gray-300 text-gray-700 hover:border-gray-400' => $paymentMethod !== 'qris',
                                ])>
                            QRIS
                        </button>
                    </div>
                    @error('paymentMethod') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Total --}}
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between text-lg font-semibold">
                        <span class="text-gray-700">Total Bayar:</span>
                        <span class="text-2xl text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Payment Amount --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Dibayar
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                        <input wire:model.live="paymentAmount" 
                               type="number" 
                               min="0"
                               step="1000"
                               class="w-full pl-12 pr-4 py-3 text-lg font-semibold border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @error('paymentAmount') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Change --}}
                @if($paymentMethod === 'cash' && $paymentAmount > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <span class="text-green-800 font-medium">Kembalian:</span>
                        <span class="text-2xl font-bold text-green-600">
                            Rp {{ number_format($change, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                @endif

                {{-- Notes --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea wire:model="notes" 
                              rows="2" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Tambahkan catatan..."></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex space-x-3">
                    <button wire:click="$set('showPaymentModal', false)" 
                            class="flex-1 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button wire:click="processPayment" 
                            wire:loading.attr="disabled"
                            class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="processPayment">Bayar</span>
                        <span wire:loading wire:target="processPayment">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Loading Overlay --}}
    <div wire:loading.flex class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-40">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-700 font-medium">Memproses...</p>
        </div>
    </div>
</div>
