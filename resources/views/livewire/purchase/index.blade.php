<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Purchase Orders</h2>
        <a href="{{ route('purchase.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Buat PO Baru
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            <div class="text-sm text-gray-600">Total PO</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
            <div class="text-sm text-gray-600">Menunggu</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['approved'] }}</div>
            <div class="text-sm text-gray-600">Disetujui</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-green-600">{{ $stats['received'] }}</div>
            <div class="text-sm text-gray-600">Diterima</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" wire:model.live="search" placeholder="Cari PO atau supplier..." 
                   class="px-4 py-2 border border-gray-300 rounded-lg">
            <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="received">Diterima</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>
    </div>

    <!-- PO List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td class="font-medium">{{ $purchase->po_number }}</td>
                        <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td class="font-medium">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ 
                                $purchase->status === 'received' ? 'badge-secondary' : 
                                ($purchase->status === 'approved' ? 'badge-info' : 
                                ($purchase->status === 'pending' ? 'badge-warning' : 'badge-danger')) 
                            }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td>{{ $purchase->createdBy->name }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                @if($purchase->status === 'pending')
                                    <button wire:click="approvePurchase({{ $purchase->id }})" 
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                        Setujui
                                    </button>
                                @elseif($purchase->status === 'approved')
                                    <a href="{{ route('purchase.receive', $purchase) }}" 
                                       class="text-green-600 hover:text-green-800 text-sm">
                                        Terima
                                    </a>
                                @endif
                                <a href="{{ route('purchase.show', $purchase) }}" 
                                   class="text-gray-600 hover:text-gray-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">Tidak ada purchase order</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>
        {{ $purchases->links() }}
    </div>
</div>
