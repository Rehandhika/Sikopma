<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Manajemen Stok</h2>
        <a href="{{ route('stock.adjustment') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Penyesuaian Stok
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_products'] }}</div>
            <div class="text-sm text-gray-600">Total Produk</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['low_stock'] }}</div>
            <div class="text-sm text-gray-600">Stok Rendah</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-red-600">{{ $stats['out_of_stock'] }}</div>
            <div class="text-sm text-gray-600">Stok Habis</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['total_value'], 0, ',', '.') }}</div>
            <div class="text-sm text-gray-600">Nilai Stok</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" wire:model.live="search" placeholder="Cari produk..." 
                   class="px-4 py-2 border border-gray-300 rounded-lg">
            <select wire:model.live="stockFilter" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="all">Semua Stok</option>
                <option value="low">Stok Rendah</option>
                <option value="out">Stok Habis</option>
            </select>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>SKU</th>
                    <th>Kategori</th>
                    <th>Stok Saat Ini</th>
                    <th>Min. Stok</th>
                    <th>Harga Modal</th>
                    <th>Nilai Total</th>
                    <th>Status</th>
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
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->category?->name ?? '-' }}</td>
                        <td>
                            <span class="text-lg font-bold {{ $product->stock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $product->stock }}
                            </span>
                        </td>
                        <td>{{ $product->min_stock }}</td>
                        <td>Rp {{ number_format($product->cost, 0, ',', '.') }}</td>
                        <td class="font-medium">Rp {{ number_format($product->stock * $product->cost, 0, ',', '.') }}</td>
                        <td>
                            @if($product->stock > $product->min_stock)
                                <span class="badge badge-secondary">Normal</span>
                            @elseif($product->stock > 0)
                                <span class="badge badge-warning">Rendah</span>
                            @else
                                <span class="badge badge-danger">Habis</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">Tidak ada produk</td>
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
