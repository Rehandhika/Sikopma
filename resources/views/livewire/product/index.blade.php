<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Daftar Produk</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Produk
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Cari produk..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <select wire:model.live="categoryFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="stockFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Stok</option>
                    <option value="low">Stok Rendah</option>
                    <option value="out">Habis</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>SKU/Barcode</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded flex-shrink-0">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover rounded">
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm">
                                <div>{{ $product->sku }}</div>
                                <div class="text-gray-500">{{ $product->barcode }}</div>
                            </div>
                        </td>
                        <td>{{ $product->category ?? '-' }}</td>
                        <td class="font-medium">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>
                            <div class="text-sm">
                                <div class="font-medium {{ $product->stock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $product->stock }}
                                </div>
                                <div class="text-gray-500">Min: {{ $product->min_stock }}</div>
                            </div>
                        </td>
                        <td>
                            @if($product->stock > $product->min_stock)
                                <span class="badge badge-secondary">Tersedia</span>
                            @elseif($product->stock > 0)
                                <span class="badge badge-warning">Stok Rendah</span>
                            @else
                                <span class="badge badge-danger">Habis</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button wire:click="deleteProduct({{ $product->id }})" 
                                        wire:confirm="Hapus produk ini?"
                                        class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">Tidak ada produk</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>
        {{ $products->links() }}
    </div>
</div>
