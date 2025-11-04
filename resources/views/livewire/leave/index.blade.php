<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Permintaan Cuti</h2>
        <a href="{{ route('leave.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Ajukan Cuti
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $summary['total'] }}</div>
            <div class="text-sm text-gray-600">Total Pengajuan</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-green-600">{{ $summary['approved'] }}</div>
            <div class="text-sm text-gray-600">Disetujui</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $summary['pending'] }}</div>
            <div class="text-sm text-gray-600">Menunggu</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-red-600">{{ $summary['rejected'] }}</div>
            <div class="text-sm text-gray-600">Ditolak</div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Semua Status</option>
            <option value="pending">Menunggu</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="cancelled">Dibatalkan</option>
        </select>
    </div>

    <!-- Leave List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis Cuti</th>
                    <th>Durasi</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td>
                            <div class="text-sm">
                                <div class="font-medium">{{ $leave->date_from->format('d/m/Y') }}</div>
                                <div class="text-gray-500">s/d {{ $leave->date_to->format('d/m/Y') }}</div>
                            </div>
                        </td>
                        <td>{{ $leave->leaveType->name ?? '-' }}</td>
                        <td>{{ $leave->days }} hari</td>
                        <td>
                            <div class="max-w-xs truncate">{{ $leave->reason }}</div>
                        </td>
                        <td>
                            <span class="badge {{ 
                                $leave->status === 'approved' ? 'badge-secondary' : 
                                ($leave->status === 'pending' ? 'badge-warning' : 
                                ($leave->status === 'rejected' ? 'badge-danger' : 'badge-gray')) 
                            }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </td>
                        <td>
                            @if($leave->status === 'pending')
                                <button wire:click="cancelRequest({{ $leave->id }})" 
                                        wire:confirm="Batalkan permintaan cuti?"
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    Batalkan
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            Belum ada permintaan cuti
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>
        {{ $leaves->links() }}
    </div>
</div>
