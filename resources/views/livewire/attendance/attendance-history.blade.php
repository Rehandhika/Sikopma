<div class="space-y-6">
    {{-- Header --}}
    <x-layout.page-header title="Riwayat Absensi" subtitle="Lihat dan kelola catatan waktu kehadiran Anda" />

    {{-- Filters --}}
    <x-ui.card>
        <x-layout.grid cols="3" gap="4">
            <div>
                <x-ui.input
                    type="date"
                    name="dateFrom"
                    label="Dari Tanggal"
                    wire:model.live="dateFrom"
                />
            </div>
            <div>
                <x-ui.input
                    type="date"
                    name="dateTo"
                    label="Sampai Tanggal"
                    wire:model.live="dateTo"
                />
            </div>
            <div>
                <x-ui.select
                    name="status"
                    label="Status"
                    wire:model.live="status"
                    :options="[
                        '' => 'Semua Status',
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir'
                    ]"
                />
            </div>
        </x-layout.grid>
    </x-ui.card>

    {{-- Table --}}
    <x-ui.card padding="false">
        <x-data.table 
            :headers="['Tanggal', 'Check-in', 'Check-out', 'Durasi', 'Status']"
            striped="true"
            hoverable="true"
        >
            @forelse($attendances as $attendance)
                <x-data.table-row>
                    <x-data.table-cell>
                        <div class="font-medium text-gray-900">{{ $attendance->check_in->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $attendance->check_in->locale('id')->dayName }}</div>
                    </x-data.table-cell>
                    
                    <x-data.table-cell>
                        <div class="flex items-center text-gray-700">
                            <x-ui.icon name="arrow-right-on-rectangle" class="w-4 h-4 mr-1.5 text-success-500" />
                            {{ $attendance->check_in->format('H:i') }}
                        </div>
                    </x-data.table-cell>
                    
                    <x-data.table-cell>
                        @if($attendance->check_out)
                            <div class="flex items-center text-gray-700">
                                <x-ui.icon name="logout" class="w-4 h-4 mr-1.5 text-info-500" />
                                {{ $attendance->check_out->format('H:i') }}
                            </div>
                        @else
                            <span class="text-gray-400 italic">Belum</span>
                        @endif
                    </x-data.table-cell>
                    
                    <x-data.table-cell>
                        @if($attendance->check_out)
                            <div class="font-medium">
                                {{ number_format($attendance->check_in->diffInSeconds($attendance->check_out) / 60, 2, ',', '.') }} <span class="text-xs text-gray-500 font-normal">Menit</span>
                            </div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </x-data.table-cell>
                    
                    <x-data.table-cell>
                        @php
                            $statusLabel = [
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir'
                            ][$attendance->status] ?? ucfirst($attendance->status);
                        @endphp
                        
                        <x-ui.badge 
                            :variant="$attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger')"
                            size="sm"
                        >
                            {{ $statusLabel }}
                        </x-ui.badge>
                    </x-data.table-cell>
                </x-data.table-row>
            @empty
                <x-data.table-row>
                    <x-data.table-cell class="text-center py-10 text-gray-500" colspan="5">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                <x-ui.icon name="document-text" class="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Tidak Ada Data Absensi</h3>
                            <p class="text-sm text-gray-500">Belum ada catatan absensi untuk periode ini.</p>
                        </div>
                    </x-data.table-cell>
                </x-data.table-row>
            @endforelse
        </x-data.table>
    </x-ui.card>

    {{-- Pagination --}}
    @if($attendances->hasPages())
        <div class="mt-4">
            <x-data.pagination :paginator="$attendances" />
        </div>
    @endif
</div>
