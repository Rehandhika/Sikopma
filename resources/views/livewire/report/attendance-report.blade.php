<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Laporan Kehadiran</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Hadir</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['present'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Terlambat</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['late'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Tidak Hadir</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['absent'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari</label>
                <input wire:model.live="dateFrom" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai</label>
                <input wire:model.live="dateTo" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                <select wire:model.live="userFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="all">Semua</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="all">Semua</option>
                    <option value="present">Hadir</option>
                    <option value="late">Terlambat</option>
                    <option value="absent">Tidak Hadir</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Check In</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Check Out</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jam Kerja</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($attendances as $attendance)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $attendance->date->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $attendance->user->name }}</td>
                    <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $attendance->check_in ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $attendance->check_out ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $attendance->work_hours ?? '-' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span @class([
                            'px-3 py-1 text-xs font-semibold rounded-full',
                            'bg-green-100 text-green-800' => $attendance->status === 'present',
                            'bg-yellow-100 text-yellow-800' => $attendance->status === 'late',
                            'bg-red-100 text-red-800' => $attendance->status === 'absent',
                            'bg-blue-100 text-blue-800' => $attendance->status === 'excused',
                        ])>
                            {{ match($attendance->status) {
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir',
                                'excused' => 'Izin',
                                default => $attendance->status
                            } }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">Tidak ada data kehadiran</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
