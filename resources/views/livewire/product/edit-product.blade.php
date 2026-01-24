<div class="max-w-4xl mx-auto">
    <x-layout.page-header 
        title="Edit Produk"
        :description="'Mengubah: ' . $product->name"
    >
        <x-slot:actions>
            <x-ui.button variant="white" :href="route('admin.products.index')" icon="arrow-left">
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Image --}}
            <div class="lg:col-span-1">
                <x-ui.card padding="true">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Gambar Produk</h3>
                    <x-ui.image-upload 
                        name="image"
                        label=""
                        :preview="$imagePreview"
                        :existingImage="$existingImage"
                        :error="$errors->first('image')"
                        hint="Gambar akan dioptimasi otomatis"
                    />
                </x-ui.card>

                <x-ui.card padding="true" class="mt-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Info Produk</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">ID</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">#{{ $product->id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Dibuat</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $product->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Diupdate</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $product->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
            </div>

            {{-- Right Column - Form Fields --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Basic Info --}}
                <x-ui.card padding="true">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Dasar</h3>
                    <div class="space-y-4">
                        <x-ui.input label="Nama Produk" wire:model="name" placeholder="Masukkan nama produk" required :error="$errors->first('name')" />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input label="SKU / Kode Barang" wire:model="sku" placeholder="Opsional" :error="$errors->first('sku')" />
                            <x-ui.input label="Kategori" wire:model="category" placeholder="Contoh: Makanan, Minuman" :error="$errors->first('category')" />
                        </div>
                        <x-ui.textarea label="Deskripsi" wire:model="description" rows="3" placeholder="Deskripsi singkat produk (opsional)" :error="$errors->first('description')" />
                    </div>
                </x-ui.card>

                {{-- Pricing & Stock --}}
                <x-ui.card padding="true">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Harga & Stok</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <x-ui.input type="number" label="Harga Beli / Modal (Rp)" wire:model.live="cost_price" min="0" step="100" required :error="$errors->first('cost_price')" hint="Harga pembelian dari supplier" />
                        <x-ui.input type="number" label="Harga Jual (Rp)" wire:model.live="price" min="0" step="100" required :error="$errors->first('price')" />
                    </div>

                    @if($cost_price && $price && $price > 0)
                        @php $profit = $price - $cost_price; $margin = round(($profit / $price) * 100, 1); @endphp
                        <div class="p-3 rounded-lg mb-4 {{ $profit > 0 ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }}">
                            <div class="flex items-center justify-between text-sm">
                                <span class="{{ $profit > 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                    <strong>Keuntungan per unit:</strong> Rp {{ number_format($profit, 0, ',', '.') }}
                                </span>
                                <span class="{{ $profit > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-semibold">{{ $margin }}% margin</span>
                            </div>
                        </div>
                    @endif

                    @if(!$has_variants)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input type="number" label="Stok Saat Ini" wire:model="stock" min="0" required :error="$errors->first('stock')" />
                            <x-ui.input type="number" label="Minimal Stok" wire:model="min_stock" min="0" required :error="$errors->first('min_stock')" hint="Alert jika stok di bawah nilai ini" />
                        </div>
                    @else
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg text-sm text-blue-700 dark:text-blue-400">
                            <x-ui.icon name="information-circle" class="w-4 h-4 inline mr-1" />
                            Stok dikelola per varian di bagian bawah.
                        </div>
                    @endif
                </x-ui.card>

                {{-- Variants Section --}}
                <x-ui.card padding="true">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Varian Produk</h3>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="has_variants" class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Produk memiliki varian</span>
                        </label>
                    </div>

                    @if($has_variants)
                        {{-- Variant Options Selection --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Tipe Varian</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($variantOptions as $option)
                                    <label class="inline-flex items-center px-3 py-2 border rounded-lg cursor-pointer transition-colors
                                        {{ collect($selectedVariantOptions)->contains($option->id) 
                                            ? 'bg-primary-50 dark:bg-primary-900/30 border-primary-500 text-primary-700 dark:text-primary-400' 
                                            : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                                        <input type="checkbox" wire:model.live="selectedVariantOptions" value="{{ $option->id }}" class="sr-only">
                                        <span class="text-sm">{{ $option->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Variant Summary --}}
                        @php $summary = $this->getVariantSummary(); @endphp
                        @if($summary['count'] > 0)
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-sm text-blue-700 dark:text-blue-400">
                                    <span><strong>{{ $summary['count'] }}</strong> varian</span>
                                    <span>Total stok: <strong>{{ number_format($summary['total_stock']) }}</strong></span>
                                    @if($summary['price_range'])
                                        <span>
                                            Harga: 
                                            @if($summary['price_range']['min'] == $summary['price_range']['max'])
                                                <strong>Rp {{ number_format($summary['price_range']['min'], 0, ',', '.') }}</strong>
                                            @else
                                                <strong>Rp {{ number_format($summary['price_range']['min'], 0, ',', '.') }} - {{ number_format($summary['price_range']['max'], 0, ',', '.') }}</strong>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Variants Table --}}
                        @if(count($variants) > 0 && !empty($selectedVariantOptions))
                            <div class="border dark:border-gray-700 rounded-lg overflow-hidden mb-4">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                @foreach($variantOptions as $option)
                                                    @if(collect($selectedVariantOptions)->contains($option->id))
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $option->name }}</th>
                                                    @endif
                                                @endforeach
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">Harga</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-24">Stok</th>
                                                <th class="px-3 py-2 w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($variants as $index => $variant)
                                                <tr wire:key="variant-row-{{ $index }}" class="{{ ($variant['stock'] ?? 0) <= 5 ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                                    @foreach($variantOptions as $option)
                                                        @if(collect($selectedVariantOptions)->contains($option->id))
                                                            <td class="px-3 py-2">
                                                                <input type="text" 
                                                                    wire:model="variants.{{ $index }}.option_texts.{{ $option->id }}" 
                                                                    placeholder="{{ $option->name }}"
                                                                    class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                    <td class="px-3 py-2">
                                                        <input type="number" wire:model="variants.{{ $index }}.price" min="0" step="100"
                                                            class="w-full text-right text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input type="number" wire:model="variants.{{ $index }}.stock" min="0"
                                                            class="w-full text-right text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <button type="button" wire:click="removeVariant({{ $index }})" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-1" title="Hapus">
                                                            <x-ui.icon name="trash" class="w-4 h-4" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @elseif(count($variants) > 0 && empty($selectedVariantOptions))
                            <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg text-sm text-yellow-700 dark:text-yellow-400">
                                Pilih tipe varian untuk menampilkan tabel varian.
                            </div>
                        @endif

                        <x-ui.button type="button" variant="white" wire:click="addVariant" icon="plus" :disabled="empty($selectedVariantOptions)">
                            Tambah Varian
                        </x-ui.button>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Aktifkan opsi di atas jika produk memiliki varian seperti ukuran, warna, atau tipe yang berbeda.
                        </p>
                    @endif
                </x-ui.card>

                {{-- Status --}}
                <x-ui.card padding="true">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Status</h3>
                    <x-ui.select label="Status Produk" wire:model="status" :error="$errors->first('status')">
                        <option value="active">Aktif - Dapat dijual</option>
                        <option value="inactive">Tidak Aktif - Tidak ditampilkan</option>
                    </x-ui.select>
                </x-ui.card>

                {{-- Actions --}}
                <div class="flex justify-end space-x-3">
                    <x-ui.button type="button" variant="white" :href="route('admin.products.index')">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </x-ui.button>
                </div>
            </div>
        </div>
    </form>
</div>
