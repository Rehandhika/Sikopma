<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Persetujuan Cuti</h2>
        <div class="flex items-center space-x-4">
            <div class="bg-yellow-100 px-4 py-2 rounded-lg">
                <span class="text-sm text-yellow-800">Menunggu: <strong>{{ $stats['pending'] }}</strong></span>
            </div>
            <div class="bg-green-100 px-4 py-2 rounded-lg">
                <span class="text-sm text-green-800">Disetujui Hari Ini: <strong>{{ $stats['approved_today'] }}</strong></span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal Pengajuan</th>
                    <th>Anggota</th>
                    <th>Jenis Cuti</th>
                    <th>Periode</th>
                    <th>Durasi</th>
                    <th>Alasan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td>{{ $leave->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="font-medium">{{ $leave->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $leave->user->nim }}</div>
                        </td>
                        <td>{{ $leave->leaveType->name ?? '-' }}</td>
                        <td>
                            <div class="text-sm">
                                {{ $leave->date_from->format('d/m/Y') }} -
                                {{ $leave->date_to->format('d/m/Y') }}
                            </div>
                        </td>
                        <td>{{ $leave->days }} hari</td>
                        <td>
                            <div class="max-w-xs truncate">{{ $leave->reason }}</div>
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button wire:click="viewDetails({{ $leave->id }})" 
                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                    Detail
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            Tidak ada permintaan cuti yang menunggu persetujuan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $leaves->links() }}</div>

    <!-- Modal -->
    @if($showModal && $selectedLeave)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50" 
             x-data @click.self="$wire.set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Permintaan Cuti</h3>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama</label>
                            <div class="mt-1 text-gray-900">{{ $selectedLeave->user->name }}</div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">NIM</label>
                            <div class="mt-1 text-gray-900">{{ $selectedLeave->user->nim }}</div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Jenis Cuti</label>
                            <div class="mt-1 text-gray-900">{{ $selectedLeave->leaveType->name }}</div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Durasi</label>
                            <div class="mt-1 text-gray-900">{{ $selectedLeave->days }} hari</div>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-medium text-gray-500">Periode</label>
                            <div class="mt-1 text-gray-900">
                                {{ $selectedLeave->date_from->format('d/m/Y') }} - 
                                {{ $selectedLeave->date_to->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-medium text-gray-500">Alasan</label>
                            <div class="mt-1 text-gray-900">{{ $selectedLeave->reason }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Catatan Persetujuan (Opsional)</label>
                        <textarea wire:model="approvalNotes" rows="3" class="form-control"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="$set('showModal', false)" class="btn btn-white">
                        Batal
                    </button>
                    <button wire:click="reject({{ $selectedLeave->id }})" 
                            class="btn btn-danger">
                        Tolak
                    </button>
                    <button wire:click="approve({{ $selectedLeave->id }})" 
                            class="btn btn-secondary">
                        Setujui
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
