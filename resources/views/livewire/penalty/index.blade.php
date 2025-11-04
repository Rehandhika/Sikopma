<div class="space-y-6">
    <!-- Header -->
    <h2 class="text-2xl font-bold text-gray-900">Penalti Saya</h2>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-red-600">{{ $summary['total_points'] }}</div>
            <div class="text-sm text-gray-600">Total Poin</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $summary['by_status']['active'] ?? 0 }}</div>
            <div class="text-sm text-gray-600">Aktif</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $summary['by_status']['appealed'] ?? 0 }}</div>
            <div class="text-sm text-gray-600">Banding</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $summary['count'] }}</div>
            <div class="text-sm text-gray-600">Total Penalti</div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="appealed">Banding</option>
            <option value="dismissed">Dibatalkan</option>
            <option value="expired">Kedaluwarsa</option>
        </select>
    </div>

    <!-- Penalty List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Deskripsi</th>
                    <th>Poin</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penalties as $penalty)
                    <tr>
                        <td>{{ $penalty->date->format('d/m/Y') }}</td>
                        <td>{{ $penalty->penaltyType->name ?? '-' }}</td>
                        <td>
                            <div class="max-w-xs">{{ $penalty->description }}</div>
                        </td>
                        <td>
                            <span class="font-medium text-red-600">{{ $penalty->points }}</span>
                        </td>
                        <td>
                            <span class="badge {{ 
                                $penalty->status === 'active' ? 'badge-danger' : 
                                ($penalty->status === 'appealed' ? 'badge-warning' : 
                                ($penalty->status === 'dismissed' ? 'badge-secondary' : 'badge-gray')) 
                            }}">
                                {{ ucfirst($penalty->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            Tidak ada penalti
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>
        {{ $penalties->links() }}
    </div>
</div>
