<div>
    <div
        x-data="{ show: @entangle('show') }"
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
            wire:click="close"
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
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
            >
                <!-- Header -->
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold leading-6 text-gray-900" id="modal-title">Pengadaan Barang Baru</h3>
                        <button wire:click="close" class="text-gray-400 hover:text-gray-500">
                            <x-ui.icon name="x-mark" class="h-6 w-6" />
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-4 py-5 sm:p-6">
                    <!-- Form Header -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <x-ui.input label="No. Invoice / Referensi" wire:model="invoice_number" placeholder="INV-..." />
                        <x-ui.input type="date" label="Tanggal Pengadaan" wire:model="date" />
                        <x-ui.input label="Supplier (Opsional)" wire:model="supplier_name" placeholder="Nama Toko / Supplier" />
                    </div>

                    <!-- Product Search -->
                    <div class="relative mb-6 z-20">
                        <x-ui.input 
                            type="search" 
                            label="Cari Produk" 
                            wire:model.live.debounce.300ms="search" 
                            placeholder="Ketik nama produk atau SKU..." 
                            icon="search"
                        />
                        
                        @if(!empty($searchResults))
                            <div class="absolute w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto z-50">
                                @foreach($searchResults as $result)
                                    <div class="p-2 hover:bg-gray-50 border-b last:border-0 cursor-pointer">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $result['name'] }}</div>
                                                <div class="text-xs text-gray-500">{{ $result['sku'] }}</div>
                                            </div>
                                            
                                            @if($result['has_variants'])
                                                <div class="flex flex-wrap gap-2 mt-1">
                                                    @if(empty($result['variants']))
                                                        <span class="text-xs text-red-500 italic">Tidak ada varian aktif</span>
                                                    @else
                                                        @foreach($result['variants'] as $variant)
                                                            <button 
                                                                wire:click="addItem({{ $result['id'] }}, {{ $variant['id'] }})"
                                                                class="flex items-center gap-1 px-2 py-1 text-xs bg-primary-50 text-primary-700 border border-primary-200 rounded hover:bg-primary-100 transition-colors"
                                                                title="Stok: {{ $variant['stock'] }}"
                                                            >
                                                                <span>{{ $variant['variant_name'] }}</span>
                                                                <span class="text-primary-400 text-[10px]">({{ $variant['stock'] }})</span>
                                                            </button>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @else
                                                <button 
                                                    wire:click="addItem({{ $result['id'] }})"
                                                    class="px-3 py-1 text-xs bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors flex items-center gap-2"
                                                >
                                                    Pilih <span class="opacity-75 text-[10px]">({{ $result['stock'] }})</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(strlen($search) >= 2)
                            <div class="absolute w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg p-3 text-center text-gray-500 text-sm z-50">
                                Produk tidak ditemukan.
                            </div>
                        @endif
                    </div>

                    <!-- Items Table -->
                    <div class="border rounded-lg overflow-hidden mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Stok Saat Ini</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Qty Masuk</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Harga Beli (@)</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Subtotal</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($items as $index => $item)
                                    <tr wire:key="item-{{ $index }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $item['sku'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 bg-gray-50">
                                            {{ $item['current_stock'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input 
                                                type="number" 
                                                wire:model.live.debounce.500ms="items.{{ $index }}.quantity" 
                                                min="1"
                                                class="block w-full text-right rounded-md border-gray-300 py-1.5 text-gray-900 shadow-sm focus:ring-primary-600 focus:border-primary-600 sm:text-sm sm:leading-6"
                                            >
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input 
                                                type="number" 
                                                wire:model.live.debounce.500ms="items.{{ $index }}.cost_price" 
                                                min="0"
                                                step="any"
                                                class="block w-full text-right rounded-md border-gray-300 py-1.5 text-gray-900 shadow-sm focus:ring-primary-600 focus:border-primary-600 sm:text-sm sm:leading-6"
                                            >
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button wire:click="removeItem({{ $index }})" class="text-red-600 hover:text-red-900">
                                                <x-ui.icon name="trash" class="h-5 w-5" />
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 text-sm">
                                            Belum ada produk yang dipilih. Silakan cari produk di atas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total</td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-primary-600">
                                        Rp {{ number_format($this->totalAmount, 0, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <x-ui.textarea label="Catatan Tambahan" wire:model="notes" placeholder="Keterangan..." />
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <x-ui.button variant="primary" wire:click="save" class="ml-3 w-full sm:w-auto">
                        Simpan Pengadaan
                    </x-ui.button>
                    <x-ui.button variant="secondary" wire:click="close" class="mt-3 w-full sm:mt-0 sm:w-auto">
                        Batal
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>
