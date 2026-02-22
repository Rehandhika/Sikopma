<div
    x-data="{ show: @entangle('showModal') }"
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
        @click="show = false"
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
            <!-- Header -->
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold leading-6 text-gray-900">
                        {{ $editingId ? 'Edit Produk' : 'Tambah Produk Baru' }}
                    </h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-500">
                        <x-ui.icon name="x-mark" class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="px-4 py-5 sm:p-6">
                <form wire:submit.prevent="save">
                    <x-layout.grid cols="2">
                        <div class="md:col-span-2">
                            <x-ui.input 
                                label="Nama Produk"
                                type="text"
                                wire:model="name"
                                required
                                :error="$errors->first('name')"
                            />
                        </div>

                        <x-ui.input 
                            label="SKU"
                            type="text"
                            wire:model="sku"
                            placeholder="Opsional"
                            :error="$errors->first('sku')"
                        />

                        <x-ui.input 
                            label="Kategori"
                            type="text"
                            wire:model="category"
                            placeholder="Opsional"
                            :error="$errors->first('category')"
                        />

                        <x-ui.input 
                            label="Harga"
                            type="number"
                            wire:model="price"
                            required
                            help="Masukkan harga dalam Rupiah"
                            :error="$errors->first('price')"
                        />

                        <x-ui.input 
                            label="Stok Awal"
                            type="number"
                            wire:model="stock"
                            required
                            :error="$errors->first('stock')"
                        />

                        <x-ui.input 
                            label="Stok Minimum"
                            type="number"
                            wire:model="min_stock"
                            required
                            :error="$errors->first('min_stock')"
                        />

                        <x-ui.select 
                            label="Status"
                            wire:model="status"
                            :options="[
                                'active' => 'Aktif',
                                'inactive' => 'Nonaktif'
                            ]"
                            required
                            :error="$errors->first('status')"
                        />

                        <div class="md:col-span-2">
                            <x-ui.textarea 
                                label="Deskripsi"
                                wire:model="description"
                                rows="3"
                                placeholder="Opsional"
                                :error="$errors->first('description')"
                            />
                        </div>
                    </x-layout.grid>

                    <!-- Footer -->
                    <div class="mt-6 sm:flex sm:flex-row-reverse">
                        <x-ui.button 
                            variant="primary"
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="w-full sm:w-auto sm:ml-3"
                        >
                            <x-ui.spinner wire:loading wire:target="save" size="sm" class="mr-2" color="white" />
                            {{ $editingId ? 'Update' : 'Simpan' }}
                        </x-ui.button>
                        <x-ui.button 
                            variant="white"
                            type="button"
                            @click="show = false"
                            class="mt-3 w-full sm:mt-0 sm:w-auto"
                        >
                            Batal
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
