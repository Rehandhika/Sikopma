<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Persetujuan Tukar Shift</h2>
        <div class="flex items-center space-x-4">
            <div class="bg-yellow-100 px-4 py-2 rounded-lg">
                <span class="text-sm text-yellow-800">Menunggu: <strong>{{ $stats['pending'] }}</strong></span>
            </div>
            <div class="bg-green-100 px-4 py-2 rounded-lg">
                <span class="text-sm text-green-800">Disetujui Hari Ini: <strong>{{ $stats['approved_today'] }}</strong></span>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($swaps as $swap)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="badge badge-info">Diterima oleh target</span>
                            <span class="text-sm text-gray-500">{{ $swap->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- From -->
                            <div class="border-r border-gray-200 pr-6">
                                <div class="text-xs text-gray-500 mb-2">DARI</div>
                                <div class="space-y-2">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $swap->requester->name }}</div>
                                        <div class="text-sm text-gray-600">{{ $swap->requester->nim }}</div>
                                    </div>
                                    <div class="bg-blue-50 p-3 rounded">
                                        <div class="text-sm font-medium text-blue-900">
                                            {{ $swap->originalSchedule->date->format('d/m/Y') }}
                                        </div>
                                        <div class="text-sm text-blue-700">
                                            Sesi {{ $swap->originalSchedule->session }} • 
                                            {{ Carbon\Carbon::parse($swap->originalSchedule->time_start)->format('H:i') }} - 
                                            {{ Carbon\Carbon::parse($swap->originalSchedule->time_end)->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- To -->
                            <div class="pl-6">
                                <div class="text-xs text-gray-500 mb-2">KE</div>
                                <div class="space-y-2">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $swap->target->name }}</div>
                                        <div class="text-sm text-gray-600">{{ $swap->target->nim }}</div>
                                    </div>
                                    @if($swap->targetSchedule)
                                        <div class="bg-purple-50 p-3 rounded">
                                            <div class="text-sm font-medium text-purple-900">
                                                {{ $swap->targetSchedule->date->format('d/m/Y') }}
                                            </div>
                                            <div class="text-sm text-purple-700">
                                                Sesi {{ $swap->targetSchedule->session }} • 
                                                {{ Carbon\Carbon::parse($swap->targetSchedule->time_start)->format('H:i') }} - 
                                                {{ Carbon\Carbon::parse($swap->targetSchedule->time_end)->format('H:i') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($swap->reason)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="text-sm text-gray-500">Alasan:</div>
                                <div class="text-gray-900">{{ $swap->reason }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="ml-6">
                        <button wire:click="viewDetails({{ $swap->id }})" 
                                class="btn btn-primary">
                            Review
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p>Tidak ada permintaan tukar shift yang menunggu persetujuan</p>
            </div>
        @endforelse
    </div>

    <div>{{ $swaps->links() }}</div>

    <!-- Modal -->
    @if($showModal && $selectedSwap)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50" 
             x-data @click.self="$wire.set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Review Tukar Shift</h3>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-600 mb-2">Kedua pihak telah menyetujui pertukaran ini</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500">Pemohon</div>
                                <div class="font-medium">{{ $selectedSwap->requester->name }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Target</div>
                                <div class="font-medium">{{ $selectedSwap->target->name }}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Catatan Persetujuan (Opsional)</label>
                        <textarea wire:model="approvalNotes" rows="3" class="form-control" 
                                  placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="$set('showModal', false)" class="btn btn-white">
                        Batal
                    </button>
                    <button wire:click="reject({{ $selectedSwap->id }})" 
                            class="btn btn-danger">
                        Tolak
                    </button>
                    <button wire:click="approve({{ $selectedSwap->id }})" 
                            class="btn btn-secondary">
                        Setujui & Tukar Jadwal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
