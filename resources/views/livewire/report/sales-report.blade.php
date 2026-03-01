<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Penjualan</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }}
                @if($dateFrom !== $dateTo) - {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }} @endif
            </p>
        </div>
        <div class="flex gap-2">
            <x-ui.button variant="secondary" wire:click="exportSales" wire:loading.attr="disabled">
                <x-ui.icon name="arrow-down-tray" class="w-4 h-4 mr-2" />
                <span wire:loading.remove wire:target="exportSales">Export Transaksi</span>
                <span wire:loading wire:target="exportSales">Mengunduh...</span>
            </x-ui.button>
            <x-ui.button variant="secondary" wire:click="exportSaleItems" wire:loading.attr="disabled">
                <x-ui.icon name="arrow-down-tray" class="w-4 h-4 mr-2" />
                <span wire:loading.remove wire:target="exportSaleItems">Export Item</span>
                <span wire:loading wire:target="exportSaleItems">Mengunduh...</span>
            </x-ui.button>
        </div>
    </div>

    {{-- Date Presets --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['today' => 'Hari Ini', 'yesterday' => 'Kemarin', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini'] as $key => $label)
            <button wire:click="setPeriod('{{ $key }}')" 
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $period === $key ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Pendapatan</p>
                    <p class="text-lg sm:text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 truncate">{{ format_currency($this->reportData->revenue) }}</p>
                </div>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-ui.icon name="banknotes" class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Rata-rata</p>
                    <p class="text-lg sm:text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 truncate">{{ format_currency($this->reportData->avg_amount) }}</p>
                </div>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-ui.icon name="calculator" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Terbesar</p>
                    <p class="text-lg sm:text-2xl font-bold text-violet-600 dark:text-violet-400 mt-1 truncate">{{ format_currency($this->reportData->max_amount) }}</p>
                </div>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-ui.icon name="arrow-trending-up" class="w-4 h-4 sm:w-5 sm:h-5 text-violet-600 dark:text-violet-400" />
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Transaksi</p>
                    <p class="text-lg sm:text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1 truncate">{{ number_format($this->reportData->total) }}</p>
                </div>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-ui.icon name="shopping-bag" class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.input type="date" name="dateFrom" label="Dari Tanggal" wire:model.live.debounce.500ms="dateFrom" />
            <x-ui.input type="date" name="dateTo" label="Sampai Tanggal" wire:model.live.debounce.500ms="dateTo" />
        </div>
    </x-ui.card>

    {{-- Charts & Payment --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" x-data="salesCharts()" wire:ignore>
        
        {{-- Revenue Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-xs sm:text-sm text-gray-900 dark:text-white mb-3">Grafik Penjualan</h3>
            <div x-ref="revenueChart" class="min-h-[250px] sm:min-h-[300px]"></div>
        </div>

        {{-- Payment Methods --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-xs sm:text-sm text-gray-900 dark:text-white mb-3">Metode Pembayaran</h3>
            <div class="h-56 sm:h-64 flex items-center justify-center">
                <div x-ref="paymentChart" class="w-full"></div>
            </div>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="grid grid-cols-1 gap-4">
        {{-- Top Products --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-xs sm:text-sm text-gray-900 dark:text-white mb-3">Produk Terlaris</h3>
            @if(count($this->topProducts) > 0)
                <div class="space-y-1 sm:space-y-2">
                    @foreach($this->topProducts as $i => $product)
                        <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <span @class([
                                'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400' => $i < 3,
                                'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => $i >= 3,
                            ])>{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($product->total_qty) }} terjual</p>
                            </div>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                                {{ format_currency($product->total_revenue) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs sm:text-sm text-gray-400 text-center py-8">Belum ada data</p>
            @endif
        </div>
    </div>

    {{-- Transactions Table --}}
    <x-ui.card padding="false">
        {{-- Mobile Cards --}}
        <div class="sm:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($sales as $sale)
                <div class="p-3 space-y-1">
                    <div class="flex justify-between items-start">
                        <div wire:click="showDetail({{ $sale->id }})" class="cursor-pointer flex-1">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">{{ $sale->invoice_number }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-sm text-gray-900 dark:text-white">{{ format_currency($sale->total_amount) }}</span>
                                                        @can('kelola_penjualan')
                            <button wire:click="confirmDelete({{ $sale->id }})" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded" title="Hapus">
                                <x-ui.icon name="trash" class="w-4 h-4" />
                            </button>
                            @endcan
                        </div>
                    </div>
                    <div wire:click="showDetail({{ $sale->id }})" class="cursor-pointer">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ $sale->created_at->format('d/m H:i') }}</span>
                            <span class="px-1.5 py-0.5 rounded text-xs font-medium
                                {{ $sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : '' }}
                                {{ $sale->payment_method === 'transfer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400' : '' }}
                                {{ $sale->payment_method === 'qris' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-400' : '' }}">
                                {{ strtoupper($sale->payment_method) }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">{{ $sale->cashier->name ?? '-' }} • {{ $sale->items_count }} item</div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400 text-sm">Tidak ada transaksi</div>
            @endforelse
        </div>

        {{-- Desktop Table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Kasir</th>
                        <th class="px-4 py-3 text-center">Item</th>
                        <th class="px-4 py-3 text-center">Metode</th>
                        <th class="px-4 py-3 text-right">Total</th>
                                                    @can('kelola_penjualan')
                        <th class="px-4 py-3 text-center w-16">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td wire:click="showDetail({{ $sale->id }})" class="px-4 py-3 cursor-pointer">
                                <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">{{ $sale->invoice_number }}</span>
                            </td>
                            <td wire:click="showDetail({{ $sale->id }})" class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs cursor-pointer">
                                {{ $sale->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td wire:click="showDetail({{ $sale->id }})" class="px-4 py-3 text-gray-900 dark:text-white cursor-pointer">{{ $sale->cashier->name ?? '-' }}</td>
                            <td wire:click="showDetail({{ $sale->id }})" class="px-4 py-3 text-center text-xs cursor-pointer">{{ $sale->items_count }}</td>
                            <td wire:click="showDetail({{ $sale->id }})" class="px-4 py-3 text-center cursor-pointer">
                                <span class="px-1.5 py-0.5 rounded text-xs font-medium
                                    {{ $sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : '' }}
                                    {{ $sale->payment_method === 'transfer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400' : '' }}
                                    {{ $sale->payment_method === 'qris' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-400' : '' }}">
                                    {{ strtoupper($sale->payment_method) }}
                                </span>
                            </td>
                            <td wire:click="showDetail({{ $sale->id }})" class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white cursor-pointer">
                                {{ format_currency($sale->total_amount) }}
                            </td>
                                                        @can('kelola_penjualan')
                            <td class="px-4 py-3 text-center">
                                <button wire:click="confirmDelete({{ $sale->id }})" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition" title="Hapus transaksi">
                                    <x-ui.icon name="trash" class="w-4 h-4" />
                                </button>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="                            @can('kelola_penjualan')7@else 6 @endcan" class="px-4 py-12 text-center text-gray-400 text-sm">Tidak ada transaksi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($sales->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $sales->links() }}
            </div>
        @endif
    </x-ui.card>

    {{-- Loading --}}
    <div wire:loading.delay class="fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg px-4 py-3 shadow-lg flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-primary-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-gray-700 dark:text-gray-300">Memuat...</span>
        </div>
    </div>

    {{-- Detail Modal --}}
    @if($selectedSaleId && $this->selectedSale)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:keydown.escape.window="closeDetail">
            <div class="flex min-h-full items-end sm:items-center justify-center p-4">
                {{-- Backdrop --}}
                <div wire:click="closeDetail" class="fixed inset-0 bg-black/50 transition-opacity"></div>
                
                {{-- Modal --}}
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md sm:max-w-lg transform transition-all">
                    {{-- Header --}}
                    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Detail Transaksi</h3>
                            <p class="text-xs text-gray-500 font-mono">{{ $this->selectedSale->invoice_number }}</p>
                        </div>
                        <button wire:click="closeDetail" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    {{-- Content --}}
                    <div class="p-4 space-y-4 max-h-[60vh] overflow-y-auto">
                        {{-- Info --}}
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500">Tanggal</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $this->selectedSale->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Kasir</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $this->selectedSale->cashier->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Metode</p>
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                    {{ $this->selectedSale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : '' }}
                                    {{ $this->selectedSale->payment_method === 'transfer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400' : '' }}
                                    {{ $this->selectedSale->payment_method === 'qris' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-400' : '' }}">
                                    {{ strtoupper($this->selectedSale->payment_method) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Item</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $this->selectedSale->items->count() }} produk</p>
                            </div>
                        </div>

                        {{-- Items --}}
                        <div>
                            <p class="text-xs text-gray-500 mb-2">Daftar Item</p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($this->selectedSale->items as $item)
                                    <div class="p-3 flex justify-between items-start gap-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $item->product->name ?? $item->product_name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $item->quantity }} × {{ format_currency($item->price) }}
                                            </p>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white shrink-0">
                                            {{ format_currency($item->subtotal) }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Payment Summary --}}
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Total</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ format_currency($this->selectedSale->total_amount) }}</span>
                            </div>
                            @if($this->selectedSale->payment_amount)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Bayar</span>
                                    <span class="text-gray-900 dark:text-white">{{ format_currency($this->selectedSale->payment_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Kembalian</span>
                                    <span class="text-gray-900 dark:text-white">{{ format_currency($this->selectedSale->change_amount) }}</span>
                                </div>
                            @endif
                        </div>

                        @if($this->selectedSale->notes)
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Catatan</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $this->selectedSale->notes }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex gap-2">
                        <button wire:click="closeDetail" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                            Tutup
                        </button>
                                                    @can('kelola_penjualan')
                        <button wire:click="confirmDelete({{ $this->selectedSale->id }})" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" wire:keydown.escape.window="cancelDelete">
            <div class="flex min-h-full items-center justify-center p-4">
                {{-- Backdrop --}}
                <div wire:click="cancelDelete" class="fixed inset-0 bg-black/50 transition-opacity"></div>
                
                {{-- Modal --}}
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-sm transform transition-all">
                    <div class="p-6 text-center">
                        {{-- Icon --}}
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Hapus Transaksi?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Transaksi akan dihapus dan stok produk akan dikembalikan. Tindakan ini tidak dapat dibatalkan.
                        </p>
                        
                        <div class="flex gap-3">
                            <button wire:click="cancelDelete" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                                Batal
                            </button>
                            <button wire:click="deleteSale" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                                <span wire:loading.remove wire:target="deleteSale">Ya, Hapus</span>
                                <span wire:loading wire:target="deleteSale" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Menghapus...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@script
<script>
    Alpine.data('salesCharts', () => ({
        revenueChart: null,
        paymentChart: null,

        init() {
            this.initRevenueChart();
            this.initPaymentChart();

            // Listen for chart updates from Livewire
            this.$wire.on('update-charts', (event) => {
                this.updateCharts(event.data, event.hourly, event.payment);
            });

            // Handle window resize for responsive charts
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    this.handleResize();
                }, 250);
            });
        },

        handleResize() {
            const isMobile = window.innerWidth < 768;
            
            if (this.revenueChart) {
                this.revenueChart.updateOptions({
                    chart: {
                        height: isMobile ? 250 : 300
                    },
                    stroke: { width: isMobile ? 1.5 : 2 },
                    xaxis: {
                        labels: {
                            style: { fontSize: isMobile ? '8px' : '10px' },
                            rotate: isMobile ? -90 : -45,
                            rotateAlways: isMobile
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { fontSize: isMobile ? '8px' : '10px' }
                        }
                    },
                    grid: {
                        padding: { left: isMobile ? 5 : 10, right: isMobile ? 5 : 0 }
                    }
                });
            }

            if (this.paymentChart) {
                this.paymentChart.updateOptions({
                    chart: {
                        height: isMobile ? 220 : 280
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: isMobile ? '60%' : '65%',
                                labels: {
                                    value: { fontSize: isMobile ? '10px' : '12px' },
                                    total: { fontSize: isMobile ? '10px' : '12px' }
                                }
                            }
                        }
                    },
                    legend: {
                        fontSize: isMobile ? '10px' : '12px',
                        markers: { radius: isMobile ? 8 : 12 },
                        itemMargin: { horizontal: isMobile ? 5 : 10, vertical: isMobile ? 3 : 5 }
                    }
                });
            }
        },

        initRevenueChart() {
            const initialData = @json($this->chartData);
            const isMobile = window.innerWidth < 768;
            
            const options = {
                series: [{
                    name: 'Pendapatan',
                    data: initialData.revenue
                }],
                chart: {
                    type: 'area',
                    height: isMobile ? 250 : 300,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    sparkline: { enabled: false }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: isMobile ? 1.5 : 2 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [50, 100, 100]
                    }
                },
                colors: ['#10b981'],
                xaxis: {
                    categories: initialData.labels,
                    labels: { 
                        show: true,
                        style: { colors: '#9ca3af', fontSize: isMobile ? '8px' : '10px' },
                        rotate: isMobile ? -90 : -45,
                        rotateAlways: isMobile,
                        hideOverlappingLabels: true
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    tooltip: { enabled: false }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            if (isMobile) {
                                return new Intl.NumberFormat('id-ID', { 
                                    style: 'currency', 
                                    currency: 'IDR', 
                                    minimumFractionDigits: 0,
                                    notation: 'compact'
                                }).format(value);
                            }
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
                        },
                        style: { colors: '#9ca3af', fontSize: isMobile ? '8px' : '10px' }
                    }
                },
                grid: {
                    show: true,
                    borderColor: '#f3f4f6',
                    strokeDashArray: 4,
                    padding: { left: isMobile ? 5 : 10, right: isMobile ? 5 : 0 }
                },
                tooltip: {
                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                    y: {
                        formatter: function (value) {
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                        }
                    }
                }
            };

            this.revenueChart = new ApexCharts(this.$refs.revenueChart, options);
            this.revenueChart.render();
        },

        initPaymentChart() {
            const paymentData = @json($this->paymentSummary);
            const isMobile = window.innerWidth < 768;
            
            // Map colors from Tailwind names to Hex
            const colorMap = {
                'emerald': '#10b981',
                'blue': '#3b82f6',
                'violet': '#8b5cf6',
                'amber': '#f59e0b',
                'gray': '#6b7280'
            };

            // Use amount instead of count for series
            const series = paymentData.map(item => item.amount);
            const labels = paymentData.map(item => item.name);
            const colors = paymentData.map(item => colorMap[item.color] || '#6b7280');

            const options = {
                series: series,
                labels: labels,
                colors: colors,
                chart: {
                    type: 'donut',
                    height: isMobile ? 220 : 280,
                    fontFamily: 'inherit',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: isMobile ? '60%' : '65%',
                            labels: {
                                show: true,
                                value: {
                                    fontSize: isMobile ? '10px' : '12px',
                                    formatter: function (val) {
                                        if (isMobile) {
                                            return new Intl.NumberFormat('id-ID', { 
                                                style: 'currency', 
                                                currency: 'IDR',
                                                maximumFractionDigits: 0,
                                                notation: 'compact'
                                            }).format(val);
                                        }
                                        return new Intl.NumberFormat('id-ID', { 
                                            style: 'currency', 
                                            currency: 'IDR',
                                            maximumFractionDigits: 0
                                        }).format(val);
                                    }
                                },
                                total: {
                                    show: true,
                                    label: isMobile ? 'Total' : 'Total',
                                    fontSize: isMobile ? '10px' : '12px',
                                    formatter: function (w) {
                                        const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        if (isMobile) {
                                            return new Intl.NumberFormat('id-ID', { 
                                                style: 'currency', 
                                                currency: 'IDR',
                                                maximumFractionDigits: 0,
                                                notation: 'compact'
                                            }).format(total);
                                        }
                                        return new Intl.NumberFormat('id-ID', { 
                                            style: 'currency', 
                                            currency: 'IDR',
                                            maximumFractionDigits: 0
                                        }).format(total);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: {
                    position: isMobile ? 'bottom' : 'bottom',
                    fontSize: isMobile ? '10px' : '12px',
                    markers: { radius: isMobile ? 8 : 12 },
                    itemMargin: { horizontal: isMobile ? 5 : 10, vertical: isMobile ? 3 : 5 }
                },
                tooltip: {
                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return new Intl.NumberFormat('id-ID', { 
                                style: 'currency', 
                                currency: 'IDR',
                                maximumFractionDigits: 0
                            }).format(val);
                        }
                    }
                },
                stroke: { show: false }
            };

            // Only render if there is data
            if (series.length > 0) {
                this.paymentChart = new ApexCharts(this.$refs.paymentChart, options);
                this.paymentChart.render();
            } else {
                this.$refs.paymentChart.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 text-xs sm:text-sm">Tidak ada data pembayaran</div>';
            }
        },

        updateCharts(revenueData, hourlyData, paymentData) {
            if (this.revenueChart) {
                this.revenueChart.updateOptions({
                    xaxis: { categories: revenueData.labels }
                });
                this.revenueChart.updateSeries([{
                    name: 'Pendapatan',
                    data: revenueData.revenue
                }]);
            }

            if (this.paymentChart && paymentData) {
                const colorMap = {
                    'emerald': '#10b981',
                    'blue': '#3b82f6',
                    'violet': '#8b5cf6',
                    'amber': '#f59e0b',
                    'gray': '#6b7280'
                };

                const series = paymentData.map(item => item.amount);
                const labels = paymentData.map(item => item.name);
                const colors = paymentData.map(item => colorMap[item.color] || '#6b7280');

                if (series.length > 0) {
                    this.paymentChart.updateOptions({
                        labels: labels,
                        colors: colors
                    });
                    this.paymentChart.updateSeries(series);
                } else {
                    // Handle empty data case if needed, or just clear
                    this.paymentChart.updateSeries([]);
                }
            }
        }
    }));
</script>
@endscript
