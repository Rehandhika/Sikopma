<div class="space-y-6">
    {{-- Header --}}
    <x-layout.page-header title="Riwayat Absensi" />

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
            :headers="['Tanggal', 'Hari', 'Check-in', 'Check-out', 'Durasi', 'Terlambat', 'Status']"
            striped="true"
            hoverable="true"
        >
            @forelse($attendances as $attendance)
                <x-data.table-row>
                    <x-data.table-cell>{{ $attendance->check_in->format('d/m/Y') }}</x-data.table-cell>
                    <x-data.table-cell>{{ $attendance->check_in->locale('id')->dayName }}</x-data.table-cell>
                    <x-data.table-cell>{{ $attendance->check_in->format('H:i') }}</x-data.table-cell>
                    <x-data.table-cell>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</x-data.table-cell>
                    <x-data.table-cell>
                        @if($attendance->check_out)
                            {{ $attendance->check_in->diffInMinutes($attendance->check_out) }} menit
                        @else
                            -
                        @endif
                    </x-data.table-cell>
                    <x-data.table-cell>
                        @if($attendance->status === 'late' && $attendance->scheduleAssignment)
                            @php
                                $scheduleStart = $attendance->scheduleAssignment->date->copy()->setTimeFromTimeString($attendance->scheduleAssignment->time_start);
                                $lateMinutes = $attendance->check_in->diffInMinutes($scheduleStart, false);
                            @endphp
                            {{ abs($lateMinutes) }} menit
                        @else
                            -
                        @endif
                    </x-data.table-cell>
                    <x-data.table-cell>
                        <x-ui.badge 
                            :variant="$attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger')"
                            size="sm"
                        >
                            {{ ucfirst($attendance->status) }}
                        </x-ui.badge>
                    </x-data.table-cell>
                </x-data.table-row>
            @empty
                <x-data.table-row>
                    <x-data.table-cell class="text-center py-8 text-gray-500" colspan="7">
                        <x-layout.empty-state
                            icon="document-text"
                            title="Tidak ada data absensi"
                        />
                    </x-data.table-cell>
                </x-data.table-row>
            @endforelse
        </x-data.table>
    </x-ui.card>

    {{-- Pagination --}}
    <x-data.pagination :paginator="$attendances" />
</div>
