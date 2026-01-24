<div class="p-6">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <x-ui.alert variant="success" dismissible="true" class="mb-6">
        {{ session('success') }}
    </x-ui.alert>
    @endif
    
    @if (session()->has('error'))
    <x-ui.alert variant="danger" dismissible="true" class="mb-6">
        {{ session('error') }}
    </x-ui.alert>
    @endif

    {{-- Header --}}
    <x-layout.page-header 
        title="Penyesuaian Stok"
        description="Kelola penambahan dan pengurangan stok produk"
        class="mb-6"
    >
        <x-slot:actions>
            <x-ui.button 
                variant="primary" 
                wire:click="create"
                icon="plus"
            >
                Penyesuaian Baru
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Stats Cards --}}
    <x-layout.grid cols="4" gap="4" class="mb-6">
        <x-layout.stat-card
            label="Total Penyesuaian"
            :value="number_format($stats['total_adjustments'])"
            icon="clipboard-list"
            iconColor="bg-primary-100"
            iconTextColor="text-primary-600"
        />
        <x-layout.stat-card
            label="Total Penambahan"
            :value="'+' . number_format($stats['total_additions'])"
            icon="arrow-up"
            iconColor="bg-success-100"
            iconTextColor="text-success-600"
        />
        <x-layout.stat-card
            label="Total Pengurangan"
            :value="'-' . number_format($stats['total_reductions'])"
            icon="arrow-down"
            iconColor="bg-danger-100"
            iconTextColor="text-danger-600"
        />
        <x-layout.stat-card
            label="Stok Menipis"
            :value="$stats['low_stock_products']"
            icon="exclamation-triangle"
            iconColor="bg-warning-100"
            iconTextColor="text-warning-600"
        />
    </x-layout.grid>

    {{-- Filters --}}
    <x-ui.card padding="true" class="mb-6">
        <x-layout.grid cols="3" gap="4">
            <x-ui.input 
                label="Cari Produk"
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Nama produk..." 
                icon="search"
            />
            <x-ui.select 
                label="Jenis"
                wire:model.live="typeFilter"
            >
                <option value="all">Semua Jenis</option>
                <option value="in">Penambahan</option>
                <option value="out">Pengurangan</option>
            </x-ui.select>
            <x-ui.select 
                label="Produk"
                wire:model.live="productFilter"
            >
                <option value="all">Semua Produk</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </x-ui.select>
        </x-layout.grid>
    </x-ui.card>

    {{-- Adjustments Table --}}
    <x-data.table 
        :headers="['Tanggal', 'Produk', 'Jenis', 'Jumlah', 'Stok', 'Alasan', 'Oleh']"
        striped="true"
        hoverable="true"
    >
        @forelse ($adjustments as $adjustment)
            <x-data.table-row>
                <x-data.table-cell class="whitespace-nowrap">
                    {{ $adjustment->created_at->format('d/m/Y H:i') }}
                </x-data.table-cell>
                <x-data.table-cell>
                    <div class="font-medium text-gray-900">{{ $adjustment->product->name }}</div>
                    @if($adjustment->isVariantAdjustment() && $adjustment->variant)
                        <div class="text-xs text-primary-600 font-medium">
                            Varian: {{ $adjustment->variant->variant_name }}
                        </div>
                    @endif
                    @if($adjustment->product->sku)
                    <div class="text-xs text-gray-500">SKU: {{ $adjustment->product->sku }}</div>
                    @endif
                </x-data.table-cell>
                <x-data.table-cell class="text-center">
                    <x-ui.badge 
                        :variant="$adjustment->type === 'in' ? 'success' : 'danger'"
                        size="sm"
                    >
                        {{ $adjustment->type === 'in' ? 'Penambahan' : 'Pengurangan' }}
                    </x-ui.badge>
                </x-data.table-cell>
                <x-data.table-cell class="text-center">
                    <span @class([
                        'font-bold text-lg',
                        'text-success-600' => $adjustment->type === 'in',
                        'text-danger-600' => $adjustment->type === 'out',
                    ])>
                        {{ $adjustment->type === 'in' ? '+' : '-' }}{{ $adjustment->quantity }}
                    </span>
                </x-data.table-cell>
                <x-data.table-cell class="text-center">
                    <div class="flex items-center justify-center space-x-2">
                        <span class="text-gray-400">{{ $adjustment->previous_stock }}</span>
                        <x-ui.icon name="arrow-right" class="w-4 h-4 text-gray-400" />
                        <span class="font-semibold text-gray-900">{{ $adjustment->new_stock }}</span>
                    </div>
                </x-data.table-cell>
                <x-data.table-cell>
                    <div class="text-sm text-gray-600 max-w-xs truncate">
                        {{ $adjustment->reason }}
                    </div>
                </x-data.table-cell>
                <x-data.table-cell class="whitespace-nowrap">
                    {{ $adjustment->user->name }}
                </x-data.table-cell>
            </x-data.table-row>
        @empty
            <x-data.table-row>
                <x-data.table-cell colspan="7">
                    <x-layout.empty-state
                        icon="clipboard-list"
                        title="Belum ada penyesuaian stok"
                        description="Klik tombol 'Penyesuaian Baru' untuk menambah"
                    >
                        <x-slot:action>
                            <x-ui.button variant="primary" wire:click="create">
                                Penyesuaian Baru
                            </x-ui.button>
                        </x-slot:action>
                    </x-layout.empty-state>
                </x-data.table-cell>
            </x-data.table-row>
        @endforelse
    </x-data.table>

    <div class="mt-4">
        {{ $adjustments->links() }}
    </div>

    {{-- Modal Form --}}
    @if($showModal)
    <x-ui.modal 
        name="stock-adjustment" 
        title="Penyesuaian Stok Baru"
        maxWidth="lg"
        x-data="{ show: @entangle('showModal') }"
        x-show="show"
    >
        <form wire:submit="save">
            <div class="space-y-4">
                {{-- Product --}}
                <x-ui.select 
                    label="Produk"
                    wire:model.live="product_id"
                    required="true"
                    :error="$errors->first('product_id')"
                >
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }} (Stok: {{ $product->stock }})
                        </option>
                    @endforeach
                </x-ui.select>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Penyesuaian *</label>
                    <x-layout.grid cols="2" gap="3">
                        <x-ui.button 
                            type="button" 
                            wire:click="$set('type', 'in')"
                            :variant="$type === 'in' ? 'success' : 'outline'"
                            class="w-full justify-center"
                        >
                            <x-ui.icon name="arrow-up" class="w-5 h-5 mr-1" />
                            Penambahan
                        </x-ui.button>
                        <x-ui.button 
                            type="button" 
                            wire:click="$set('type', 'out')"
                            :variant="$type === 'out' ? 'danger' : 'outline'"
                            class="w-full justify-center"
                        >
                            <x-ui.icon name="arrow-down" class="w-5 h-5 mr-1" />
                            Pengurangan
                        </x-ui.button>
                    </x-layout.grid>
                    @error('type') 
                        <p class="text-danger-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Quantity --}}
                <x-ui.input 
                    label="Jumlah"
                    type="number"
                    wire:model="quantity"
                    min="1"
                    required="true"
                    :error="$errors->first('quantity')"
                />

                {{-- Reason --}}
                <x-ui.textarea 
                    label="Alasan"
                    wire:model="reason"
                    rows="3"
                    placeholder="Contoh: Rusak, Hilang, Penerimaan barang, dll"
                    required="true"
                    :error="$errors->first('reason')"
                />
            </div>

            <x-slot:footer>
                <x-ui.button 
                    type="button" 
                    variant="white" 
                    wire:click="$set('showModal', false)"
                >
                    Batal
                </x-ui.button>
                <x-ui.button 
                    type="submit" 
                    variant="primary"
                    :loading="$wire->loading('save')"
                >
                    Simpan Penyesuaian
                </x-ui.button>
            </x-slot:footer>
        </form>
    </x-ui.modal>
    @endif

    {{-- Loading State --}}
    <div wire:loading class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-40">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <x-ui.spinner size="lg" class="mx-auto" />
            <p class="mt-4 text-gray-700 font-medium">Memuat...</p>
        </div>
    </div>
</div>
