<div class="min-h-screen flex flex-col lg:flex-row bg-gray-50 dark:bg-gray-900">
    
    {{-- LEFT: Products Section --}}
    <div class="flex-1 flex flex-col lg:h-screen {{ $showCart ? 'hidden lg:flex' : '' }}">
        
        {{-- Header --}}
        <header class="sticky top-0 z-10 flex items-center justify-between px-4 lg:px-6 py-3 lg:py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 lg:gap-4">
                <a href="{{ route('admin.dashboard') }}" class="p-2 -ml-2 text-gray-500 dark:text-gray-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-lg lg:text-xl font-bold text-gray-900 dark:text-white">Kasir</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">{{ auth()->user()->name }}</p>
                </div>
            </div>
            
            {{-- Mobile Cart Button --}}
            <button wire:click="toggleCart" class="lg:hidden relative p-2.5 text-gray-600 dark:text-gray-300 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @if($this->cartItemsCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 bg-primary-600 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ $this->cartItemsCount }}</span>
                @endif
            </button>
        </header>

        {{-- Search & Categories --}}
        <div class="sticky top-[57px] lg:top-[65px] z-10 px-4 lg:px-6 py-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 space-y-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..." 
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500">
            
            @if($this->categories->isNotEmpty())
                <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 lg:mx-0 lg:px-0">
                    <button wire:click="$set('category', '')" 
                        class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 {{ $category === '' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Semua
                    </button>
                    @foreach($this->categories as $cat)
                        <button wire:click="$set('category', '{{ $cat }}')" 
                            class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap flex-shrink-0 {{ $category === $cat ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Products Grid (Scrollable) --}}
        <div class="flex-1 p-3 lg:p-4 lg:overflow-y-auto {{ $this->cartItemsCount > 0 ? 'pb-20 lg:pb-4' : '' }}">
            @if($this->products->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                    <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="font-medium">Tidak ada produk</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 lg:gap-3">
                    @foreach($this->products as $product)
                        <button wire:click="addToCart({{ $product->id }})" wire:key="product-{{ $product->id }}"
                            class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 text-left active:scale-[0.97] active:bg-gray-50 dark:active:bg-gray-700">
                            
                            <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative">
                                @if($product->image_thumbnail_url)
                                    <img src="{{ $product->image_thumbnail_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                @if($product->has_variants)
                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded bg-primary-500 text-white">Varian</span>
                                    <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded bg-black/60 text-white">{{ $product->activeVariants->sum('stock') }}</span>
                                @else
                                    <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded {{ $product->stock <= 5 ? 'bg-red-500 text-white' : 'bg-black/60 text-white' }}">{{ $product->stock }}</span>
                                @endif
                            </div>
                            
                            <div class="p-2 lg:p-2.5">
                                <h3 class="font-medium text-gray-900 dark:text-white text-xs lg:text-sm line-clamp-2 leading-tight min-h-[2rem]">{{ $product->name }}</h3>
                                @if($product->has_variants)
                                    <p class="text-primary-600 dark:text-primary-400 font-bold text-xs lg:text-sm mt-1">{{ $product->display_price }}</p>
                                @else
                                    <p class="text-primary-600 dark:text-primary-400 font-bold text-xs lg:text-sm mt-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Mobile Cart Bar (Fixed) --}}
        @if($this->cartItemsCount > 0)
            <div class="lg:hidden fixed bottom-0 left-0 right-0 z-20 px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="toggleCart" class="w-full flex items-center justify-between px-4 py-3 bg-primary-600 text-white rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center text-sm font-bold">{{ $this->cartItemsCount }}</span>
                        <span class="font-semibold">Lihat Keranjang</span>
                    </div>
                    <span class="font-bold">Rp {{ number_format($this->cartTotal, 0, ',', '.') }}</span>
                </button>
            </div>
        @endif
    </div>


    {{-- RIGHT: Cart Sidebar --}}
    <aside class="lg:w-80 xl:w-96 lg:h-screen flex flex-col bg-white dark:bg-gray-800 lg:border-l border-gray-200 dark:border-gray-700
        {{ $showCart ? 'fixed inset-0 z-30' : 'hidden lg:flex' }}">
        
        {{-- Cart Header --}}
        <header class="sticky top-0 flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-2">
                <button wire:click="toggleCart" class="lg:hidden p-1.5 -ml-1.5 text-gray-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Keranjang</h2>
                @if($this->cartItemsCount > 0)
                    <span class="px-2 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-xs font-semibold rounded-full">{{ $this->cartItemsCount }}</span>
                @endif
            </div>
            @if(count($cart) > 0)
                <button wire:click="clearCart" wire:confirm="Hapus semua?" class="text-xs text-red-600 font-medium px-2 py-1 rounded">Hapus</button>
            @endif
        </header>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto">
            @if(empty($cart))
                <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4">
                    <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-sm font-medium">Keranjang Kosong</p>
                </div>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($cart as $key => $item)
                        @php
                            $isVariant = !empty($item['variant_id']);
                            $stockRemaining = $item['stock'] - $item['quantity'];
                            $isLowStock = $stockRemaining <= 3 && $stockRemaining > 0;
                            $isNearLimit = $item['quantity'] >= $item['stock'];
                        @endphp
                        <li wire:key="cart-{{ $key }}" class="p-3 flex gap-2.5">
                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden flex-shrink-0 relative">
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                {{-- Variant badge --}}
                                @if($isVariant)
                                    <span class="absolute bottom-0 left-0 right-0 bg-primary-600/90 text-white text-[8px] font-bold text-center py-0.5">VARIAN</span>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-1">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1">{{ $item['name'] }}</h4>
                                        {{-- Stock remaining indicator --}}
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            @if($isNearLimit)
                                                <span class="text-[10px] text-red-500 dark:text-red-400 font-medium">
                                                    Maks tercapai
                                                </span>
                                            @elseif($isLowStock)
                                                <span class="text-[10px] text-amber-500 dark:text-amber-400 font-medium">
                                                    Sisa {{ $stockRemaining }} lagi
                                                </span>
                                            @else
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                                    Stok: {{ $item['stock'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <button wire:click="removeFromCart('{{ $key }}')" class="p-1 text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="flex items-center justify-between mt-1.5">
                                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        <button wire:click="decrementQty('{{ $key }}')" class="w-7 h-7 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-primary-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>
                                        <span class="w-8 text-center text-sm font-semibold text-gray-900 dark:text-white">{{ $item['quantity'] }}</span>
                                        <button wire:click="incrementQty('{{ $key }}')" 
                                            class="w-7 h-7 flex items-center justify-center {{ $isNearLimit ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'text-gray-600 dark:text-gray-300 hover:text-primary-600' }}"
                                            @if($isNearLimit) disabled @endif>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Cart Footer --}}
        @if(count($cart) > 0)
            <footer class="border-t border-gray-200 dark:border-gray-700 p-3 lg:p-4 space-y-3 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total ({{ $this->cartItemsCount }})</span>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($this->cartTotal, 0, ',', '.') }}</span>
                </div>
                <button wire:click="openPayment" class="w-full py-3 bg-primary-600 text-white font-bold rounded-xl">Bayar</button>
            </footer>
        @endif
    </aside>


    {{-- Payment Modal --}}
    @if($showPayment)
        <div class="fixed inset-0 z-50 flex items-end lg:items-center lg:justify-center p-0 lg:p-4">
            <div wire:click="closePayment" class="absolute inset-0 bg-black/50"></div>
            
            <div class="relative w-full lg:max-w-md lg:mx-auto bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl max-h-[85vh] lg:max-h-[80vh] flex flex-col animate-slide-up lg:animate-fade-in" @click.stop>
                
                <div class="lg:hidden flex justify-center pt-2 pb-1">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>
                
                <header class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pembayaran</h3>
                    <button wire:click="closePayment" class="p-1.5 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <div class="text-center py-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                        <p class="text-sm text-primary-600 dark:text-primary-400 font-medium">Total</p>
                        <p class="text-2xl font-bold text-primary-700 dark:text-primary-300">Rp {{ number_format($this->cartTotal, 0, ',', '.') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Metode</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="$set('paymentMethod', 'cash')" 
                                class="p-3 rounded-xl border-2 flex items-center justify-center gap-2 {{ $paymentMethod === 'cash' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700' }}">
                                <span class="font-semibold text-sm {{ $paymentMethod === 'cash' ? 'text-primary-700' : 'text-gray-700 dark:text-gray-300' }}">Tunai</span>
                            </button>
                            <button wire:click="$set('paymentMethod', 'qris')" 
                                class="p-3 rounded-xl border-2 flex items-center justify-center gap-2 {{ $paymentMethod === 'qris' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700' }}">
                                <span class="font-semibold text-sm {{ $paymentMethod === 'qris' ? 'text-primary-700' : 'text-gray-700 dark:text-gray-300' }}">QRIS</span>
                            </button>
                        </div>
                    </div>

                    @if($paymentMethod === 'cash')
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jumlah Bayar</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rp</span>
                                    <input type="number" wire:model.live="paymentAmount" 
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-lg font-bold text-right focus:ring-2 focus:ring-primary-500"
                                        placeholder="0" inputmode="numeric">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-4 gap-1.5">
                                @foreach($quickAmounts as $amount)
                                    <button wire:click="setQuickAmount({{ $amount }})" class="py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg">{{ number_format($amount / 1000) }}rb</button>
                                @endforeach
                            </div>
                            
                            <button wire:click="setExactAmount" class="w-full py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg">Uang Pas</button>

                            @if($paymentAmount >= $this->cartTotal && $paymentAmount > 0)
                                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl flex justify-between items-center">
                                    <span class="text-green-700 dark:text-green-400 font-medium text-sm">Kembalian</span>
                                    <span class="text-lg font-bold text-green-700 dark:text-green-400">Rp {{ number_format($this->change, 0, ',', '.') }}</span>
                                </div>
                            @elseif($paymentAmount > 0 && $paymentAmount < $this->cartTotal)
                                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                                    <p class="text-red-600 font-medium text-sm">Kurang Rp {{ number_format($this->cartTotal - $paymentAmount, 0, ',', '.') }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <p class="text-blue-700 dark:text-blue-400 text-sm">Pembayaran QRIS dengan jumlah pas.</p>
                        </div>
                    @endif
                </div>

                <footer class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <button wire:click="processPayment" 
                        wire:loading.attr="disabled"
                        @if($paymentMethod === 'cash' && $paymentAmount < $this->cartTotal) disabled @endif
                        class="w-full py-3.5 bg-green-600 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white font-bold rounded-xl">
                        <span wire:loading.remove wire:target="processPayment">Proses Pembayaran</span>
                        <span wire:loading wire:target="processPayment">Memproses...</span>
                    </button>
                </footer>
            </div>
        </div>
    @endif

    {{-- Variant Selection Modal --}}
    @if($showVariantModal)
        <div class="fixed inset-0 z-50 flex items-end lg:items-center lg:justify-center p-0 lg:p-4">
            <div wire:click="closeVariantModal" class="absolute inset-0 bg-black/50"></div>
            
            <div class="relative w-full lg:max-w-md lg:mx-auto bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl max-h-[85vh] lg:max-h-[80vh] flex flex-col animate-slide-up lg:animate-fade-in" @click.stop>
                
                <div class="lg:hidden flex justify-center pt-2 pb-1">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>
                
                <header class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pilih Varian</h3>
                        @if($selectedProductName)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedProductName }}</p>
                        @endif
                    </div>
                    <button wire:click="closeVariantModal" class="p-1.5 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto p-4">
                    @if(empty($productVariants))
                        <div class="text-center py-8 text-gray-500">
                            <p>Tidak ada varian tersedia</p>
                        </div>
                    @else
                        {{-- Grouped Options Display --}}
                        @if(!empty($groupedVariants))
                            <div class="mb-4 space-y-3">
                                @foreach($groupedVariants as $optionSlug => $optionData)
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            {{ $optionData['option_name'] }}
                                        </h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($optionData['values'] as $valueData)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm
                                                    {{ $valueData['total_stock'] > 0 
                                                        ? 'bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-500' 
                                                        : 'bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-500 line-through' }}">
                                                    {{ $valueData['value'] }}
                                                    @if($valueData['total_stock'] > 0)
                                                        <span class="text-xs px-1.5 py-0.5 rounded {{ $valueData['total_stock'] <= 5 ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' }}">
                                                            {{ $valueData['total_stock'] }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-400">Habis</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mb-2">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih Varian:</h4>
                            </div>
                        @endif

                        {{-- Variant List --}}
                        <div class="space-y-2">
                            @foreach($productVariants as $variant)
                                @php
                                    $isOutOfStock = $variant['stock'] < 1;
                                    $isLowStock = $variant['stock'] > 0 && $variant['stock'] <= 5;
                                @endphp
                                <button wire:click="addVariantToCart({{ $variant['id'] }})" 
                                    class="w-full p-3 rounded-xl flex items-center justify-between transition-colors
                                        {{ $isOutOfStock 
                                            ? 'bg-gray-100 dark:bg-gray-800 opacity-50 cursor-not-allowed' 
                                            : 'bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600' }}"
                                    @if($isOutOfStock) disabled @endif>
                                    <div class="text-left flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white {{ $isOutOfStock ? 'line-through' : '' }}">
                                            @php
                                                $optionValues = collect($variant['option_values'])->pluck('value')->implode(' / ');
                                            @endphp
                                            {{ $optionValues ?: $variant['variant_name'] }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1">
                                            {{-- Stock Indicator --}}
                                            @if($isOutOfStock)
                                                <span class="inline-flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Habis
                                                </span>
                                            @elseif($isLowStock)
                                                <span class="inline-flex items-center gap-1 text-xs text-amber-500 dark:text-amber-400">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Stok: {{ $variant['stock'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-xs text-green-500 dark:text-green-400">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Stok: {{ $variant['stock'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right ml-3">
                                        <p class="font-bold text-primary-600 dark:text-primary-400">
                                            Rp {{ number_format($variant['price'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Animation Styles --}}
    <style>
        @keyframes slide-up {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fade-in {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
    </style>
</div>
