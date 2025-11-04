<div class="p-6">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
        {{ session('success') }}
    </div>
    @endif
    
    @if (session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Cuti/Izin Saya</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola dan pantau permohonan cuti/izin Anda</p>
        </div>
        <a href="{{ route('leave.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Ajukan Baru</span>
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Total Pengajuan</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Menunggu</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Disetujui</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Ditolak</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex items-center space-x-4">
            <label class="text-sm font-medium text-gray-700">Filter Status:</label>
            <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="all">Semua</option>
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>
    </div>

    {{-- Requests List --}}
    <div class="space-y-4">
        @forelse($requests as $request)
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <span @class([
                            'px-3 py-1 text-xs font-semibold rounded-full',
                            'bg-yellow-100 text-yellow-800' => $request->status === 'pending',
                            'bg-green-100 text-green-800' => $request->status === 'approved',
                            'bg-red-100 text-red-800' => $request->status === 'rejected',
                            'bg-gray-100 text-gray-800' => $request->status === 'cancelled',
                        ])>
                            {{ match($request->status) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
                                default => $request->status
                            } }}
                        </span>
                        <span @class([
                            'px-3 py-1 text-xs font-medium rounded-full',
                            'bg-blue-100 text-blue-800' => $request->leave_type === 'permission',
                            'bg-red-100 text-red-800' => $request->leave_type === 'sick',
                            'bg-yellow-100 text-yellow-800' => $request->leave_type === 'emergency',
                            'bg-purple-100 text-purple-800' => $request->leave_type === 'other',
                        ])>
                            {{ $request->getLeaveTypeLabel() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Periode</p>
                            <p class="font-semibold text-gray-900">
                                {{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}
                            </p>
                            <p class="text-sm text-gray-600">{{ $request->total_days }} hari</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Diajukan</p>
                            <p class="text-sm text-gray-900">{{ $request->created_at->format('d M Y H:i') }}</p>
                        </div>
                        @if($request->reviewed_at)
                        <div>
                            <p class="text-xs text-gray-500">Ditinjau oleh</p>
                            <p class="text-sm text-gray-900">{{ $request->reviewer->name ?? '-' }}</p>
                            <p class="text-xs text-gray-600">{{ $request->reviewed_at->format('d M Y H:i') }}</p>
                        </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <p class="text-xs text-gray-500 mb-1">Alasan:</p>
                        <p class="text-sm text-gray-700">{{ $request->reason }}</p>
                    </div>

                    @if($request->attachment)
                    <div class="flex items-center space-x-2 text-sm text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <a href="{{ Storage::url($request->attachment) }}" target="_blank" class="hover:underline">
                            Lihat Lampiran
                        </a>
                    </div>
                    @endif

                    @if($request->review_notes)
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Catatan Review:</p>
                        <p class="text-sm text-gray-700">{{ $request->review_notes }}</p>
                    </div>
                    @endif
                </div>

                @if($request->canCancel())
                <div class="ml-4">
                    <button wire:click="cancel({{ $request->id }})" 
                            wire:confirm="Yakin ingin membatalkan permohonan ini?"
                            class="px-3 py-1 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition">
                        Batalkan
                    </button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 font-medium">Belum ada pengajuan cuti/izin</p>
            <p class="text-sm text-gray-400 mt-1">Klik tombol "Ajukan Baru" untuk membuat pengajuan</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($requests->hasPages())
    <div class="mt-6">
        {{ $requests->links() }}
    </div>
    @endif

    {{-- Loading State --}}
    <div wire:loading class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-40">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-700 font-medium">Memuat...</p>
        </div>
    </div>
</div>
