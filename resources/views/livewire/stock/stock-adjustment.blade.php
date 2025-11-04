<div class="p-6">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
        {{ session('success') }}
    </div>
    @endif
    
    @if (session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Penyesuaian Stok</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola penambahan dan pengurangan stok produk</p>
        </div>
        <button wire:click="create" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Penyesuaian Baru</span>
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Penyesuaian</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_adjustments']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Penambahan</p>
                    <p class="text-2xl font-bold text-green-600">+{{ number_format($stats['total_additions']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Pengurangan</p>
                    <p class="text-2xl font-bold text-red-600">-{{ number_format($stats['total_reductions']) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Stok Menipis</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['low_stock_products'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Produk</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nama produk..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                <select wire:model.live="typeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="all">Semua Jenis</option>
                    <option value="addition">Penambahan</option>
                    <option value="reduction">Pengurangan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Produk</label>
                <select wire:model.live="productFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="all">Semua Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Adjustments Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oleh</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($adjustments as $adjustment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $adjustment->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $adjustment->product->name }}</div>
                            @if($adjustment->product->sku)
                            <div class="text-xs text-gray-500">SKU: {{ $adjustment->product->sku }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span @class([
                                'px-3 py-1 text-xs font-semibold rounded-full',
                                'bg-green-100 text-green-800' => $adjustment->type === 'addition',
                                'bg-red-100 text-red-800' => $adjustment->type === 'reduction',
                            ])>
                                {{ $adjustment->type === 'addition' ? 'Penambahan' : 'Pengurangan' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span @class([
                                'font-bold text-lg',
                                'text-green-600' => $adjustment->type === 'addition',
                                'text-red-600' => $adjustment->type === 'reduction',
                            ])>
                                {{ $adjustment->type === 'addition' ? '+' : '-' }}{{ $adjustment->quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                            <div class="flex items-center justify-center space-x-2">
                                <span class="text-gray-400">{{ $adjustment->previous_stock }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                                <span class="font-semibold text-gray-900">{{ $adjustment->new_stock }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-xs truncate">
                                {{ $adjustment->reason }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $adjustment->user->name }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-gray-500 font-medium">Belum ada penyesuaian stok</p>
                            <p class="text-sm text-gray-400 mt-1">Klik tombol "Penyesuaian Baru" untuk menambah</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $adjustments->links() }}
        </div>
    </div>

    {{-- Modal Form --}}
    @if($showModal)
    <div class="fixed inset-0 bg-gray-900/75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Penyesuaian Stok Baru</h3>

                <form wire:submit="save">
                    <div class="space-y-4">
                        {{-- Product --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Produk *</label>
                            <select wire:model.live="product_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Pilih Produk</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }} (Stok: {{ $product->stock }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Penyesuaian *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" wire:click="$set('type', 'addition')" 
                                        @class([
                                            'py-3 px-4 border-2 rounded-lg font-medium transition',
                                            'border-green-600 bg-green-50 text-green-600' => $type === 'addition',
                                            'border-gray-300 text-gray-700 hover:border-gray-400' => $type !== 'addition',
                                        ])>
                                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                    </svg>
                                    Penambahan
                                </button>
                                <button type="button" wire:click="$set('type', 'reduction')" 
                                        @class([
                                            'py-3 px-4 border-2 rounded-lg font-medium transition',
                                            'border-red-600 bg-red-50 text-red-600' => $type === 'reduction',
                                            'border-gray-300 text-gray-700 hover:border-gray-400' => $type !== 'reduction',
                                        ])>
                                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                    </svg>
                                    Pengurangan
                                </button>
                            </div>
                            @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Quantity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah *</label>
                            <input wire:model="quantity" type="number" min="1" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Reason --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan *</label>
                            <textarea wire:model="reason" rows="3" 
                                placeholder="Contoh: Rusak, Hilang, Penerimaan barang, dll"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                        <button type="button" wire:click="$set('showModal', false)" 
                            class="px-6 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">
                            Batal
                        </button>
                        <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Simpan Penyesuaian</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Loading State --}}
    <div wire:loading class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-40">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-700 font-medium">Memuat...</p>
        </div>
    </div>
</div>
