<div class="space-y-6">
    <!-- Page Header -->
    <x-layout.page-header 
        title="Kelola Penalti"
        description="Kelola penalti anggota dan review banding">
        <x-slot:actions>
            <div class="flex items-center space-x-4">
                <x-ui.badge variant="danger" size="md">
                    Aktif: <strong>{{ $stats['total_active'] }}</strong>
                </x-ui.badge>
                <x-ui.badge variant="warning" size="md">
                    Banding: <strong>{{ $stats['total_appealed'] }}</strong>
                </x-ui.badge>
                <x-ui.badge variant="success" size="md">
                    Dibatalkan: <strong>{{ $stats['total_dismissed'] }}</strong>
                </x-ui.badge>
            </div>
        </x-slot:actions>
    </x-layout.page-header>

    <!-- Filter -->
    <x-ui.card padding="true">
        <div class="flex items-center space-x-4">
            <label class="text-sm font-medium text-gray-700">Filter Status:</label>
            <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <option value="all">Semua</option>
                <option value="active">Aktif</option>
                <option value="appealed">Dalam Banding</option>
                <option value="dismissed">Dibatalkan</option>
                <option value="expired">Kadaluarsa</option>
            </select>
        </div>
    </x-ui.card>

    <!-- Penalties Table -->
    <x-ui.card padding="false">
        <x-data.table :headers="['Tanggal', 'Anggota', 'Jenis Penalti', 'Poin', 'Deskripsi', 'Status', 'Aksi']">
            @forelse($penalties as $penalty)
                <x-data.table-row>
                    <x-data.table-cell>{{ $penalty->date->format('d/m/Y') }}</x-data.table-cell>
                    <x-data.table-cell>
                        <div class="font-medium">{{ $penalty->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $penalty->user->nim }}</div>
                    </x-data.table-cell>
                    <x-data.table-cell>
                        <div class="font-medium">{{ $penalty->penaltyType->name }}</div>
                        @if($penalty->reference_type)
                            <div class="text-xs text-gray-500">Ref: {{ $penalty->reference_type }}</div>
                        @endif
                    </x-data.table-cell>
                    <x-data.table-cell>
                        <span class="font-semibold text-red-600">{{ $penalty->points }}</span>
                    </x-data.table-cell>
                    <x-data.table-cell>
                        <div class="max-w-xs truncate">{{ $penalty->description }}</div>
                    </x-data.table-cell>
                    <x-data.table-cell>
                        @if($penalty->status === 'active')
                            <x-ui.badge variant="danger">Aktif</x-ui.badge>
                        @elseif($penalty->status === 'appealed')
                            <x-ui.badge variant="warning">Dalam Banding</x-ui.badge>
                            @if($penalty->appeal_reason)
                                <div class="text-xs text-gray-500 mt-1">Alasan: {{ Str::limit($penalty->appeal_reason, 30) }}</div>
                            @endif
                        @elseif($penalty->status === 'dismissed')
                            <x-ui.badge variant="success">Dibatalkan</x-ui.badge>
                        @else
                            <x-ui.badge variant="secondary">{{ ucfirst($penalty->status) }}</x-ui.badge>
                        @endif
                    </x-data.table-cell>
                    <x-data.table-cell>
                        @if($penalty->status === 'appealed')
                            <x-ui.button 
                                variant="ghost" 
                                size="sm"
                                wire:click="openReviewModal({{ $penalty->id }})">
                                Review Banding
                            </x-ui.button>
                        @else
                            <span class="text-sm text-gray-500">-</span>
                        @endif
                    </x-data.table-cell>
                </x-data.table-row>
            @empty
                <x-data.table-row>
                    <x-data.table-cell colspan="7">
                        <x-layout.empty-state 
                            icon="clipboard-check"
                            title="Tidak ada penalti"
                            description="Tidak ada penalti yang sesuai dengan filter yang dipilih" />
                    </x-data.table-cell>
                </x-data.table-row>
            @endforelse
        </x-data.table>
    </x-ui.card>

    <x-data.pagination :paginator="$penalties" />

    <!-- Review Appeal Modal -->
    @if($showReviewModal && $selectedPenalty)
        <x-ui.modal 
            name="review-modal" 
            title="Review Banding Penalti"
            maxWidth="2xl"
            x-data 
            x-show="true"
            @click.away="$wire.set('showReviewModal', false)">
            
            <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Anggota</label>
                            <div class="mt-1 text-gray-900">{{ $selectedPenalty->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $selectedPenalty->user->nim }}</div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Jenis Penalti</label>
                            <div class="mt-1 text-gray-900">{{ $selectedPenalty->penaltyType->name }}</div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Poin</label>
                            <div class="mt-1 text-red-600 font-semibold">{{ $selectedPenalty->points }}</div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal</label>
                            <div class="mt-1 text-gray-900">{{ $selectedPenalty->date->format('d/m/Y') }}</div>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-medium text-gray-500">Deskripsi Penalti</label>
                            <div class="mt-1 text-gray-900">{{ $selectedPenalty->description }}</div>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-medium text-gray-500">Alasan Banding</label>
                            <div class="mt-1 text-gray-900 bg-yellow-50 p-3 rounded border border-yellow-200">
                                {{ $selectedPenalty->appeal_reason }}
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-medium text-gray-500">Tanggal Banding</label>
                            <div class="mt-1 text-gray-900">{{ $selectedPenalty->appealed_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <x-ui.alert variant="info" :icon="true">
                    <strong>Setujui:</strong> Penalti akan dibatalkan dan poin dikurangi dari total user.<br>
                    <strong>Tolak:</strong> Penalti tetap aktif dan poin tetap dihitung.
                </x-ui.alert>

                <x-ui.textarea 
                    name="reviewNotes"
                    label="Catatan Review"
                    wire:model="reviewNotes"
                    rows="3"
                    placeholder="Berikan catatan untuk keputusan Anda (minimal 10 karakter)"
                    required
                    :error="$errors->first('reviewNotes')"
                    help="{{ strlen($reviewNotes) }}/500 karakter" />
            </div>

            <x-slot:footer>
                <x-ui.button 
                    variant="white" 
                    wire:click="$set('showReviewModal', false)">
                    Batal
                </x-ui.button>
                <x-ui.button 
                    variant="danger" 
                    wire:click="rejectAppeal"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="rejectAppeal">Tolak Banding</span>
                    <span wire:loading wire:target="rejectAppeal">Memproses...</span>
                </x-ui.button>
                <x-ui.button 
                    variant="success" 
                    wire:click="approveAppeal"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="approveAppeal">Setujui Banding</span>
                    <span wire:loading wire:target="approveAppeal">Memproses...</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
