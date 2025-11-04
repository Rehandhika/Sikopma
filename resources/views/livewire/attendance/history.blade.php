<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Riwayat Absensi</h2>
        <button wire:click="export" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="form-label">Dari Tanggal</label>
                <input type="date" wire:model="dateFrom" class="form-control">
            </div>
            <div>
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" wire:model="dateTo" class="form-control">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select wire:model.live="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="present">Hadir</option>
                    <option value="late">Terlambat</option>
                    <option value="absent">Tidak Hadir</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button wire:click="applyFilter" class="btn btn-primary flex-1">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                </button>
                <button wire:click="resetFilter" class="btn btn-white">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Durasi</th>
                    <th>Status</th>
                    <th>Lokasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->check_in->format('d/m/Y') }}</td>
                        <td>{{ $attendance->check_in->locale('id')->dayName }}</td>
                        <td>{{ $attendance->check_in->format('H:i') }}</td>
                        <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                        <td>
                            @if($attendance->check_out)
                                {{ $attendance->check_in->diffInHours($attendance->check_out) }}j 
                                {{ $attendance->check_in->diffInMinutes($attendance->check_out) % 60 }}m
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $attendance->status === 'present' ? 'badge-secondary' : ($attendance->status === 'late' ? 'badge-warning' : 'badge-danger') }}">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </td>
                        <td>
                            @if($attendance->location_lat && $attendance->location_lng)
                                <a href="https://maps.google.com/?q={{ $attendance->location_lat }},{{ $attendance->location_lng }}" 
                                   target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    Lihat Peta
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            Tidak ada data absensi
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>
        {{ $attendances->links() }}
    </div>
</div>
