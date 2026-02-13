<div class="space-y-6" wire:init="init">
    {{-- Header --}}
    <x-layout.page-header
        title="Manajemen Stok"
        description="Kelola stok produk, varian, dan riwayat penyesuaian."
    >
        <x-slot:actions>
            <x-ui.button 
                variant="primary" 
                x-data 
                @click="$dispatch('openProcurementModal')" 
                icon="shopping-cart"
            >
                Pengadaan Baru
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Stats Grid (Lazy Loaded) --}}
    <x-layout.grid cols="4" gap="4">
        <x-layout.stat-card
            label="Total Produk"
            :value="$this->stats['total']"
            icon="cube"
            iconColor="bg-primary-100"
            iconTextColor="text-primary-600"
        />
        <x-layout.stat-card
            label="Nilai Modal"
            :value="'Rp ' . number_format($this->stats['total_cost'] ?? 0, 0, ',', '.')"
            icon="banknotes"
            iconColor="bg-gray-100"
            iconTextColor="text-gray-600"
        />
        <x-layout.stat-card
            label="Nilai Jual"
            :value="'Rp ' . number_format($this->stats['total_value'] ?? 0, 0, ',', '.')"
            icon="currency-dollar"
            iconColor="bg-blue-100"
            iconTextColor="text-blue-600"
        />
        <x-layout.stat-card
            label="Potensi Profit"
            :value="'Rp ' . number_format($this->stats['potential_profit'] ?? 0, 0, ',', '.')"
            icon="arrow-trending-up"
            iconColor="bg-success-100"
            iconTextColor="text-success-600"
        />
    </x-layout.grid>

    {{-- Filter Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button wire:click="$set('stockFilter', 'normal')" class="text-left">
            <x-ui.card padding="true" class="h-full border-l-4 border-success-500 hover:bg-gray-50 transition-colors {{ $stockFilter === 'normal' ? 'ring-2 ring-success-500' : '' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Stok Normal</p>
                        <p class="text-2xl font-bold text-success-600">{{ $this->stats['normal_stock'] }}</p>
                    </div>
                    <x-ui.icon name="check-circle" class="w-8 h-8 text-success-200" />
                </div>
            </x-ui.card>
        </button>
        <button wire:click="$set('stockFilter', 'low')" class="text-left">
            <x-ui.card padding="true" class="h-full border-l-4 border-warning-500 hover:bg-gray-50 transition-colors {{ $stockFilter === 'low' ? 'ring-2 ring-warning-500' : '' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Stok Rendah</p>
                        <p class="text-2xl font-bold text-warning-600">{{ $this->stats['low_stock'] }}</p>
                    </div>
                    <x-ui.icon name="exclamation-triangle" class="w-8 h-8 text-warning-200" />
                </div>
            </x-ui.card>
        </button>
        <button wire:click="$set('stockFilter', 'out')" class="text-left">
            <x-ui.card padding="true" class="h-full border-l-4 border-danger-500 hover:bg-gray-50 transition-colors {{ $stockFilter === 'out' ? 'ring-2 ring-danger-500' : '' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Stok Habis</p>
                        <p class="text-2xl font-bold text-danger-600">{{ $this->stats['out_of_stock'] }}</p>
                    </div>
                    <x-ui.icon name="x-circle" class="w-8 h-8 text-danger-200" />
                </div>
            </x-ui.card>
        </button>
    </div>

    {{-- Main Content --}}
    <x-ui.card class="overflow-hidden">
        {{-- Tabs & Search --}}
        <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
            <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
                <button wire:click="setTab('products')" class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ $activeTab === 'products' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                    Produk
                </button>
                <button wire:click="setTab('history')" class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ $activeTab === 'history' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                    Riwayat
                </button>
            </div>

            <div class="flex gap-2 flex-1 max-w-md">
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="{{ $activeTab === 'products' ? 'search' : 'historySearch' }}"
                    placeholder="Cari..."
                    icon="search"
                    class="w-full"
                />
                @if($activeTab === 'products')
                    <select wire:model.live="categoryFilter" class="text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Semua Kategori</option>
                        @foreach($this->categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    <x-ui.button variant="secondary" wire:click="exportProducts" icon="arrow-down-tray">
                        Ekspor
                    </x-ui.button>
                @else
                    <x-ui.button variant="secondary" wire:click="exportHistory" icon="arrow-down-tray">
                        Ekspor
                    </x-ui.button>
                @endif
            </div>
        </div>

        {{-- Products Table --}}
        @if($activeTab === 'products')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-right">Harga Beli</th>
                            <th class="px-4 py-3 text-right">Harga Jual</th>
                            <th class="px-4 py-3 text-right">Nilai Aset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->products as $product)
                            <tr wire:key="p-{{ $product->id }}" class="hover:bg-gray-50 transition-colors {{ in_array($product->id, $expandedProducts) ? 'bg-gray-50' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-ui.product-image :src="$product->image_thumbnail_url" :alt="$product->name" size="w-10 h-10" />
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                            @if($product->has_variants)
                                                <button wire:click="toggleExpand({{ $product->id }})" class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1 mt-1">
                                                    <x-ui.icon :name="in_array($product->id, $expandedProducts) ? 'chevron-down' : 'chevron-right'" class="w-3 h-3" />
                                                    {{ $product->variants->count() }} Varian
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($product->has_variants)
                                        <span class="font-bold text-gray-700">{{ $product->total_stock }}</span>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <span @class([
                                                'font-bold',
                                                'text-danger-600' => $product->stock <= 0,
                                                'text-warning-600' => $product->stock > 0 && $product->stock <= $product->min_stock,
                                                'text-gray-900' => $product->stock > $product->min_stock,
                                            ])>{{ $product->stock }}</span>
                                            @if($product->stock <= $product->min_stock)
                                                <span class="text-[10px] text-red-500">Min: {{ $product->min_stock }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="text-sm font-medium text-gray-600">
                                        @if($product->has_variants)
                                            @php
                                                $minCost = $product->variants->min('cost_price');
                                                $maxCost = $product->variants->max('cost_price');
                                            @endphp
                                            @if($minCost == $maxCost)
                                                Rp {{ number_format($minCost, 0, ',', '.') }}
                                            @else
                                                Rp {{ number_format($minCost, 0, ',', '.') }} - {{ number_format($maxCost, 0, ',', '.') }}
                                            @endif
                                        @else
                                            Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="text-sm font-medium">{{ $product->display_price }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-700">
                                    Rp {{ number_format($product->has_variants ? $product->variants->sum(fn($v) => $v->stock * $v->cost_price) : $product->stock * $product->cost_price, 0, ',', '.') }}
                                </td>
                            </tr>

                            {{-- Variant Expansion --}}
                            @if($product->has_variants && in_array($product->id, $expandedProducts))
                                @foreach($product->variants as $variant)
                                    <tr wire:key="v-{{ $variant->id }}" class="bg-gray-50/50">
                                        <td class="px-4 py-2 pl-12">
                                            <div class="flex items-center gap-2">
                                                <x-ui.icon name="tag" class="w-4 h-4 text-gray-400" />
                                                <span class="text-sm text-gray-600">{{ $variant->variant_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <span @class([
                                                'font-medium',
                                                'text-danger-600' => $variant->stock <= 0,
                                                'text-warning-600' => $variant->stock > 0 && $variant->stock <= $variant->min_stock,
                                            ])>{{ $variant->stock }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-right text-sm text-gray-600">
                                            Rp {{ number_format($variant->cost_price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-right text-sm text-gray-600">
                                            Rp {{ number_format($variant->price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-right text-sm text-gray-600">
                                            Rp {{ number_format($variant->stock * $variant->cost_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    <x-layout.empty-state
                                        icon="cube"
                                        title="Tidak ada produk"
                                        description="Tidak ada produk yang sesuai dengan pencarian."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t">
                {{ $this->products->links() }}
            </div>
        @endif

        {{-- History Tab --}}
        @if($activeTab === 'history')
            <div class="divide-y divide-gray-100">
                @forelse($this->adjustments as $adj)
                    <button type="button" wire:click="showDetail({{ $adj->id }})" class="w-full text-left focus:outline-none focus:bg-gray-50">
                        <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                            <div @class([
                                'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0',
                                'bg-green-100 text-green-600' => $adj->type === 'in',
                                'bg-red-100 text-red-600' => $adj->type === 'out',
                            ])>
                                <x-ui.icon :name="$adj->type === 'in' ? 'arrow-up' : 'arrow-down'" class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">{{ $adj->product->name ?? 'Produk Dihapus' }}</span>
                                    @if($adj->variant)
                                        <x-ui.badge variant="gray" size="sm">{{ $adj->variant->variant_name }}</x-ui.badge>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500">{{ $adj->reason }}</p>
                                <p class="text-xs text-gray-400 mt-1">Oleh: {{ $adj->user->name ?? 'System' }} • {{ $adj->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <span @class([
                                    'text-lg font-bold',
                                    'text-green-600' => $adj->type === 'in',
                                    'text-red-600' => $adj->type === 'out',
                                ])>
                                    {{ $adj->type === 'in' ? '+' : '-' }}{{ $adj->quantity }}
                                </span>
                                <div class="text-xs text-gray-500">Stok: {{ $adj->previous_stock }} → {{ $adj->new_stock }}</div>
                            </div>
                            <div class="ml-2">
                                <x-ui.icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="p-8 text-center">
                        <x-layout.empty-state
                            icon="clock"
                            title="Belum ada riwayat"
                            description="Belum ada penyesuaian stok yang tercatat."
                        />
                    </div>
                @endforelse
            </div>
            <div class="px-4 py-3 border-t">
                {{ $this->adjustments->links() }}
            </div>
        @endif
    </x-ui.card>

    {{-- Detail Modal --}}
    <div
        x-data="{ show: @entangle('showDetailModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            wire:click="closeDetailModal"
        ></div>

        <!-- Modal Panel -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
            >
                @if($selectedAdjustment)
                    <!-- Header -->
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold leading-6 text-gray-900">Detail Riwayat Stok</h3>
                            <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-500">
                                <x-ui.icon name="x-mark" class="h-6 w-6" />
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-6">
                            {{-- Header Status --}}
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <div @class([
                                    'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0',
                                    'bg-green-100 text-green-600' => $selectedAdjustment->type === 'in',
                                    'bg-red-100 text-red-600' => $selectedAdjustment->type === 'out',
                                ])>
                                    <x-ui.icon :name="$selectedAdjustment->type === 'in' ? 'arrow-up' : 'arrow-down'" class="w-6 h-6" />
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 uppercase tracking-wider font-semibold">
                                        {{ $selectedAdjustment->type === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ $selectedAdjustment->quantity }} Unit
                                    </div>
                                </div>
                            </div>

                            {{-- Product Info --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 uppercase font-medium mb-1">Produk</label>
                                    <div class="font-medium text-gray-900">{{ $selectedAdjustment->product->name ?? 'Produk Dihapus' }}</div>
                                    @if($selectedAdjustment->variant)
                                        <div class="text-sm text-gray-600 mt-1">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded text-xs border">
                                                {{ $selectedAdjustment->variant->variant_name }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 uppercase font-medium mb-1">Perubahan Stok</label>
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="text-gray-500">{{ $selectedAdjustment->previous_stock }}</span>
                                        <x-ui.icon name="arrow-right" class="w-3 h-3 text-gray-400" />
                                        <span class="font-bold text-gray-900">{{ $selectedAdjustment->new_stock }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 uppercase font-medium mb-1">Tanggal</label>
                                    <div class="text-sm text-gray-900">{{ $selectedAdjustment->created_at->format('d F Y, H:i') }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 uppercase font-medium mb-1">Oleh</label>
                                    <div class="text-sm text-gray-900">{{ $selectedAdjustment->user->name ?? 'System' }}</div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs text-gray-500 uppercase font-medium mb-1">Keterangan / Referensi</label>
                                <div class="p-3 bg-gray-50 rounded-md text-sm text-gray-700 border border-gray-200">
                                    {{ $selectedAdjustment->reason }}
                                </div>
                            </div>

                            {{-- Related Procurement Info --}}
                            @if($selectedProcurement)
                                <div class="border-t pt-4 mt-4">
                                    <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                        <x-ui.icon name="document-text" class="w-4 h-4 text-primary-600" />
                                        Detail Pengadaan
                                    </h4>
                                    
                                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">No. Invoice</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $selectedProcurement->invoice_number }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Supplier</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $selectedProcurement->supplier_name ?: '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Total Item</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $selectedProcurement->items->sum('quantity') }} unit</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Total Biaya</span>
                                            <span class="text-sm font-bold text-primary-700">Rp {{ number_format($selectedProcurement->total_amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <x-ui.button variant="secondary" wire:click="closeDetailModal" class="w-full sm:w-auto">
                            Tutup
                        </x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Procurement Modal --}}
    <livewire:stock.procurement-modal />
</div>
