<div class="space-y-6">
    <x-layout.page-header 
        title="Persetujuan Cuti"
        description="Kelola persetujuan permintaan cuti anggota">
        <x-slot:actions>
            <div class="flex items-center space-x-4">
                <x-ui.badge variant="warning" size="md">
                    Menunggu: <strong>{{ $stats['pending'] }}</strong>
                </x-ui.badge>
                <x-ui.badge variant="success" size="md">
                    Disetujui Hari Ini: <strong>{{ $stats['approved_today'] }}</strong>
                </x-ui.badge>
            </div>
        </x-slot:actions>
    </x-layout.page-header>

    <x-ui.card padding="false">
        <x-data.table :headers="['Tanggal Pengajuan', 'Anggota', 'Jenis Cuti', 'Periode', 'Durasi', 'Alasan', 'Aksi']">
            @forelse($leaves as $leave)
                <x-data.table-row>
                    <x-data.table-cell>{{ $leave->created_at->format('d/m/Y H:i') }}</x-data.table-cell>
                    <x-data.table-cell>
                        <div class="font-medium">{{ $leave->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $leave->user->nim }}</div>
                    </x-data.table-cell>
                    <x-data.table-cell>{{ $leave->leaveType->name ?? '-' }}</x-data.table-cell>
                    <x-data.table-cell>
                        <div class="text-sm">
                            {{ $leave->date_from->format('d/m/Y') }} -
                            {{ $leave->date_to->format('d/m/Y') }}
                        </div>
                    </x-data.table-cell>
                    <x-data.table-cell>{{ $leave->days }} hari</x-data.table-cell>
                    <x-data.table-cell>
                        <div class="max-w-xs truncate">{{ $leave->reason }}</div>
                    </x-data.table-cell>
                    <x-data.table-cell>
                        <x-ui.button 
                            variant="ghost" 
                            size="sm"
                            wire:click="viewDetails({{ $leave->id }})">
                            Detail
                        </x-ui.button>
                    </x-data.table-cell>
                </x-data.table-row>
            @empty
                <x-data.table-row>
                    <x-data.table-cell colspan="7">
                        <x-layout.empty-state 
                            icon="clipboard-check"
                            title="Tidak ada permintaan cuti yang menunggu persetujuan"
                            description="Semua permintaan cuti telah ditinjau" />
                    </x-data.table-cell>
                </x-data.table-row>
            @endforelse
        </x-data.table>
    </x-ui.card>

    <x-data.pagination :paginator="$leaves" />

    @if($showModal && $selectedLeave)
        <x-ui.modal 
            name="leave-detail" 
            title="Detail Permintaan Cuti"
            maxWidth="2xl"
            x-data 
            x-show="true"
            @click.away="$wire.set('showModal', false)">
            
            <div class="space-y-4">
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

                    @if(count($affectedSchedules) > 0)
                        <div class="col-span-2">
                            <label class="text-sm font-medium text-gray-500 mb-2 block">
                                Jadwal yang Akan Terdampak
                                <span class="text-gray-400 font-normal">({{ count($affectedSchedules) }} jadwal)</span>
                            </label>
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-3 max-h-48 overflow-y-auto">
                                <div class="space-y-2">
                                    @foreach($affectedSchedules as $schedule)
                                        <div class="flex items-center justify-between bg-white p-2 rounded border border-gray-200">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $schedule['date'] }}</p>
                                                    <p class="text-xs text-gray-500">{{ $schedule['session_name'] }} ({{ $schedule['time'] }})</p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                @if($schedule['status'] === 'scheduled') bg-blue-100 text-blue-800
                                                @elseif($schedule['status'] === 'excused') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($schedule['status']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Status jadwal akan diubah menjadi "excused" jika disetujui
                            </p>
                        </div>
                    @else
                        <div class="col-span-2">
                            <x-ui.alert variant="warning" :icon="true">
                                Tidak ada jadwal yang terdampak pada periode ini
                            </x-ui.alert>
                        </div>
                    @endif
                </div>

                <x-ui.textarea 
                    name="approvalNotes"
                    label="Catatan Persetujuan (Opsional)"
                    wire:model="approvalNotes"
                    rows="3" />
            </div>

            <x-slot:footer>
                <x-ui.button 
                    variant="white" 
                    wire:click="$set('showModal', false)">
                    Batal
                </x-ui.button>
                <x-ui.button 
                    variant="danger" 
                    wire:click="reject({{ $selectedLeave->id }})">
                    Tolak
                </x-ui.button>
                <x-ui.button 
                    variant="success" 
                    wire:click="approve({{ $selectedLeave->id }})">
                    Setujui
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
