<div class="space-y-6" wire:init="init">
    {{-- Header --}}
    <x-layout.page-header
        title="Manajemen Stok"
        description="Kelola stok produk, varian, dan riwayat penyesuaian."
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if(count($selectedProducts) > 0)
                    <div class="flex items-center gap-2 mr-4">
                        <span class="text-sm text-gray-600">{{ count($selectedProducts) }} dipilih</span>
                        <x-ui.button variant="primary" size="sm" wire:click="openBulkModal" icon="adjustments-horizontal">Bulk Adjust</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" wire:click="clearSelection" icon="x-mark"></x-ui.button>
                    </div>
                @endif

                <x-ui.button 
                    variant="primary" 
                    x-data 
                    @click="$dispatch('openProcurementModal')" 
                    icon="shopping-cart"
                >
                    Pengadaan Baru
                </x-ui.button>
            </div>
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
            :value="'Rp ' . number_format($this->stats['total_cost'], 0, ',', '.')"
            icon="banknotes"
            iconColor="bg-gray-100"
            iconTextColor="text-gray-600"
        />
        <x-layout.stat-card
            label="Nilai Jual"
            :value="'Rp ' . number_format($this->stats['total_value'], 0, ',', '.')"
            icon="currency-dollar"
            iconColor="bg-blue-100"
            iconTextColor="text-blue-600"
        />
        <x-layout.stat-card
            label="Potensi Profit"
            :value="'Rp ' . number_format($this->stats['potential_profit'], 0, ',', '.')"
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
                @else
                    <select wire:model.live="historyType" class="text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                        <option value="all">Semua Tipe</option>
                        <option value="in">Masuk (+)</option>
                        <option value="out">Keluar (-)</option>
                    </select>
                @endif
            </div>
        </div>

        {{-- Products Table --}}
        @if($activeTab === 'products')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" wire:click="selectAllVisible"
                                    @checked(count($selectedProducts) > 0 && count($selectedProducts) === $this->products->filter(fn($p) => !$p->has_variants)->count())
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-right">Harga Jual</th>
                            <th class="px-4 py-3 text-right">Nilai Aset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->products as $product)
                            <tr wire:key="p-{{ $product->id }}" class="hover:bg-gray-50 transition-colors {{ in_array($product->id, $expandedProducts) ? 'bg-gray-50' : '' }}">
                                <td class="px-4 py-3">
                                    @if(!$product->has_variants)
                                        <input type="checkbox" wire:click="toggleProductSelection({{ $product->id }})"
                                            @checked(in_array($product->id, $selectedProducts))
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-ui.product-image :src="$product->image_thumbnail_url" :alt="$product->name" size="w-10 h-10" />
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $product->sku ?? '-' }}</div>
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
                                        <td class="px-4 py-2"></td>
                                        <td class="px-4 py-2 pl-12">
                                            <div class="flex items-center gap-2">
                                                <x-ui.icon name="tag" class="w-4 h-4 text-gray-400" />
                                                <span class="text-sm text-gray-600">{{ $variant->variant_name }}</span>
                                                <span class="text-xs text-gray-400">({{ $variant->sku }})</span>
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
                    <div class="p-4 flex items-center gap-4 hover:bg-gray-50">
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
                    </div>
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

    {{-- Adjust Modal --}}
    <x-ui.modal wire:model="showAdjustModal" title="Penyesuaian Stok">
        @if($this->selectedProduct)
            <div class="space-y-4">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <x-ui.product-image :src="$this->selectedProduct->image_thumbnail_url" class="w-12 h-12" />
                    <div>
                        <div class="font-medium">{{ $this->selectedProduct->name }}</div>
                        @if($this->selectedVariant)
                            <div class="text-sm text-gray-600">Varian: {{ $this->selectedVariant->variant_name }}</div>
                            <div class="text-xs text-gray-500">Stok Saat Ini: {{ $this->selectedVariant->stock }}</div>
                        @else
                            <div class="text-xs text-gray-500">Stok Saat Ini: {{ $this->selectedProduct->stock }}</div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" wire:click="$set('adjustType', 'in')" @class([
                        'p-3 rounded-lg border text-center font-medium transition-colors',
                        'bg-green-50 border-green-200 text-green-700' => $adjustType === 'in',
                        'hover:bg-gray-50' => $adjustType !== 'in',
                    ])>
                        Masuk (+)
                    </button>
                    <button type="button" wire:click="$set('adjustType', 'out')" @class([
                        'p-3 rounded-lg border text-center font-medium transition-colors',
                        'bg-red-50 border-red-200 text-red-700' => $adjustType === 'out',
                        'hover:bg-gray-50' => $adjustType !== 'out',
                    ])>
                        Keluar (-)
                    </button>
                </div>

                <x-ui.input type="number" label="Jumlah" wire:model="adjustQuantity" min="1" class="text-lg font-bold" />
                
                <x-ui.textarea label="Alasan Penyesuaian" wire:model="adjustReason" placeholder="Contoh: Barang rusak, stok opname, restock..." />

                <div class="flex justify-end gap-2 pt-4">
                    <x-ui.button variant="secondary" wire:click="closeAdjustModal">Batal</x-ui.button>
                    <x-ui.button variant="primary" wire:click="saveAdjustment">Simpan</x-ui.button>
                </div>
            </div>
        @endif
    </x-ui.modal>

    {{-- Bulk Modal --}}
    <x-ui.modal wire:model="showBulkModal" title="Bulk Adjustment">
        <div class="space-y-4">
            <p class="text-sm text-gray-600">Anda akan melakukan penyesuaian stok untuk <strong>{{ count($selectedProducts) }} produk</strong> terpilih.</p>
            
            <div class="grid grid-cols-2 gap-3">
                <button type="button" wire:click="$set('bulkType', 'in')" @class([
                    'p-3 rounded-lg border text-center font-medium transition-colors',
                    'bg-green-50 border-green-200 text-green-700' => $bulkType === 'in',
                    'hover:bg-gray-50' => $bulkType !== 'in',
                ])>
                    Masuk (+)
                </button>
                <button type="button" wire:click="$set('bulkType', 'out')" @class([
                    'p-3 rounded-lg border text-center font-medium transition-colors',
                    'bg-red-50 border-red-200 text-red-700' => $bulkType === 'out',
                    'hover:bg-gray-50' => $bulkType !== 'out',
                ])>
                    Keluar (-)
                </button>
            </div>

            <x-ui.input type="number" label="Jumlah (per produk)" wire:model="adjustQuantity" min="1" />
            <x-ui.textarea label="Alasan" wire:model="bulkReason" />

            <div class="flex justify-end gap-2 pt-4">
                <x-ui.button variant="secondary" wire:click="closeBulkModal">Batal</x-ui.button>
                <x-ui.button variant="primary" wire:click="saveBulkAdjustment">Simpan</x-ui.button>
            </div>
        </div>
    </x-ui.modal>

    {{-- Procurement Modal --}}
    <livewire:stock.procurement-modal />
</div>
