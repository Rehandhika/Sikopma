<div class="h-screen flex flex-col bg-gray-100">
    <div class="flex-1 flex overflow-hidden">
        <!-- Products Section -->
        <div class="flex-1 overflow-y-auto p-4">
            <!-- Search -->
            <div class="mb-4">
                <input type="text" wire:model.live="search" placeholder="Cari produk atau scan barcode..." 
                       class="w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($products as $product)
                    <button wire:click="addToCart({{ $product->id }})" 
                            class="bg-white rounded-lg p-4 hover:shadow-lg transition-shadow text-left">
                        <div class="aspect-square bg-gray-200 rounded-lg mb-2 flex items-center justify-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-lg">
                            @else
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            @endif
                        </div>
                        <h3 class="font-medium text-gray-900 text-sm mb-1 truncate">{{ $product->name }}</h3>
                        <p class="text-indigo-600 font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">Stok: {{ $product->stock }}</p>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Cart Section -->
        <div class="w-96 bg-white border-l border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Keranjang</h2>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                @forelse($cart as $key => $item)
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-medium text-gray-900 text-sm flex-1">{{ $item['name'] }}</h3>
                            <button wire:click="removeFromCart('{{ $key }}')" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" 
                                        class="w-8 h-8 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <span class="w-12 text-center font-medium">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" 
                                        class="w-8 h-8 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">@ Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                                <div class="font-bold text-gray-900">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <p>Keranjang kosong</p>
                    </div>
                @endforelse
            </div>

            <!-- Summary & Payment -->
            @if(!empty($cart))
                <div class="border-t border-gray-200 p-4 space-y-4">
                    <!-- Discount -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Diskon Anggota (%)</label>
                        <input type="number" wire:model.live="memberDiscount" min="0" max="100" 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Diskon</span>
                                <span class="font-medium text-red-600">- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total</span>
                            <span class="text-indigo-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Metode Pembayaran</label>
                        <select wire:model="paymentMethod" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <!-- Payment Amount -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Jumlah Bayar</label>
                        <input type="number" wire:model.live="paymentAmount" 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-lg font-bold">
                    </div>

                    <!-- Change -->
                    @if($paymentAmount > 0)
                        <div class="bg-green-50 rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-green-800">Kembalian</span>
                                <span class="text-xl font-bold text-green-600">Rp {{ number_format($change, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Process Button -->
                    <button wire:click="processPayment" 
                            class="w-full btn btn-primary btn-lg"
                            :disabled="$paymentAmount < $total">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Proses Pembayaran
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
