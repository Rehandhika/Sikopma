<div class="space-y-6"
    x-data="{ 
        lockBody: @entangle('showForm') || @entangle('viewingId') || @entangle('reviewingId')
    }" 
    x-init="$watch('lockBody', value => {
        if (value) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    })">
    
    <x-ui.card :padding="false">
        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button
                    type="button"
                    wire:click="setTab('my-requests')"
                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 
                        {{ $activeTab === 'my-requests' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Pengajuan Saya
                </button>
                @if($isAdmin)
                <button
                    type="button"
                    wire:click="setTab('admin')"
                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 
                        {{ $activeTab === 'admin' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Persetujuan
                    @if($stats['pending'] > 0)
                    <span class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-700 font-bold rounded-full">{{ $stats['pending'] }}</span>
                    @endif
                </button>
                @endif
            </nav>
        </div>

        <!-- Request List -->
        <div class="p-6">
            <div class="space-y-4">
                @forelse($requests as $req)
                    <x-ui.card shadow="sm" class="hover:shadow-md transition-shadow cursor-pointer border border-gray-100" wire:click="viewRequest({{ $req->id }})">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-3">
                                    @php
                                        $statusVariant = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary'
                                        ][$req->status] ?? 'primary';
                                    @endphp
                                    <x-ui.badge :variant="$statusVariant">
                                        {{ $req->getStatusLabel() }}
                                    </x-ui.badge>
                                    <x-ui.badge :variant="$req->change_type === 'reschedule' ? 'info' : 'warning'">
                                        {{ $req->getChangeTypeLabel() }}
                                    </x-ui.badge>
                                    <span class="text-sm text-gray-500">
                                        {{ $req->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <div class="text-xs font-medium text-gray-500 uppercase mb-1">
                                            @if($activeTab === 'admin')
                                                Dari: {{ $req->user->name ?? '-' }}
                                            @else
                                                Jadwal Asal
                                            @endif
                                        </div>
                                        @if($req->originalAssignment)
                                            <div class="font-semibold text-gray-900">
                                                {{ \Carbon\Carbon::parse($req->originalAssignment->date)->format('d M Y') }}
                                            </div>
                                            <div class="text-sm text-gray-600 mt-1">
                                                Sesi {{ $req->originalAssignment->session }} ({{ $req->originalAssignment->time_start }}-{{ $req->originalAssignment->time_end }})
                                            </div>
                                        @else
                                            <div class="font-semibold text-gray-400 italic">Jadwal tidak ditemukan</div>
                                        @endif
                                    </div>

                                    @if($req->change_type === 'reschedule' && $req->requested_date)
                                    <div>
                                        <div class="text-xs font-medium text-blue-500 uppercase mb-1">Tujuan Pindah</div>
                                        <div class="font-semibold text-blue-700">
                                            {{ $req->requested_date->format('d M Y') }}
                                        </div>
                                        <div class="text-sm text-blue-600 mt-1">
                                            {{ $req->getSessionLabel() }}
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                @if($req->reason)
                                    <div class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">
                                        <span class="font-medium">Alasan:</span> {{ $req->reason }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center space-x-2 ml-4" wire:click.stop>
                                @if($activeTab === 'admin' && $req->status === 'pending')
                                    <x-ui.button 
                                        wire:click="openReview({{ $req->id }}, 'approved')" 
                                        variant="success" 
                                        size="sm">
                                        Setujui
                                    </x-ui.button>
                                    <x-ui.button 
                                        wire:click="openReview({{ $req->id }}, 'rejected')" 
                                        variant="danger" 
                                        size="sm">
                                        Tolak
                                    </x-ui.button>
                                @elseif($activeTab === 'my-requests' && $req->status === 'pending')
                                    <x-ui.button 
                                        wire:click="cancelRequest({{ $req->id }})" 
                                        wire:confirm="Batalkan pengajuan ini?"
                                        variant="white" 
                                        size="sm">
                                        Batalkan
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                    </x-ui.card>
                @empty
                    <x-layout.empty-state 
                        icon="calendar" 
                        title="Belum ada pengajuan"
                        description="Data pengajuan perubahan jadwal akan tampil di sini." />
                @endforelse
            </div>
        </div>
    </x-ui.card>

    @if($requests->hasPages())
    <div>
        {{ $requests->links() }}
    </div>
    @endif

    {{-- Create Form Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="closeForm"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">Ajukan Perubahan Jadwal</h3>
                    <button wire:click="closeForm" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <x-ui.icon name="x" class="w-6 h-6" />
                    </button>
                </div>
                
                <div class="p-6">
                    <form wire:submit="submitForm" class="space-y-5">
                        {{-- Select Schedule --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih Jadwal yang Ingin Diubah</label>
                            <select wire:model="selectedAssignment" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm shadow-sm">
                                <option value="">-- Pilih jadwal --</option>
                                @foreach($myAssignments as $asg)
                                <option value="{{ $asg->id }}">
                                    {{ \Carbon\Carbon::parse($asg->date)->format('d M Y') }} - Sesi {{ $asg->session }} ({{ $asg->time_start }}-{{ $asg->time_end }})
                                </option>
                                @endforeach
                            </select>
                            @error('selectedAssignment') <p class="text-danger-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>

                        {{-- Change Type --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Perubahan</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" wire:click="$set('changeType', 'reschedule')" @class(['p-4 border-2 rounded-xl text-sm font-bold transition-all duration-200 flex flex-col items-center justify-center gap-2', 'border-blue-500 bg-blue-50 text-blue-700' => $changeType === 'reschedule', 'border-gray-100 text-gray-500 hover:border-gray-300 hover:bg-gray-50' => $changeType !== 'reschedule'])>
                                    <x-ui.icon name="calendar" class="w-6 h-6" />
                                    Pindah Jadwal
                                </button>
                                <button type="button" wire:click="$set('changeType', 'cancel')" @class(['p-4 border-2 rounded-xl text-sm font-bold transition-all duration-200 flex flex-col items-center justify-center gap-2', 'border-orange-500 bg-orange-50 text-orange-700' => $changeType === 'cancel', 'border-gray-100 text-gray-500 hover:border-gray-300 hover:bg-gray-50' => $changeType !== 'cancel'])>
                                    <x-ui.icon name="x-circle" class="w-6 h-6" />
                                    Batalkan Jadwal
                                </button>
                            </div>
                        </div>

                        {{-- Target Date & Session (only for reschedule) --}}
                        @if($changeType === 'reschedule')
                        <div class="grid grid-cols-2 gap-4 p-4 bg-blue-50/50 rounded-xl border border-blue-100">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal Tujuan</label>
                                <input type="date" wire:model="requestedDate" min="{{ now()->format('Y-m-d') }}" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm shadow-sm">
                                @error('requestedDate') <p class="text-danger-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sesi Tujuan</label>
                                <select wire:model="requestedSession" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm shadow-sm">
                                    <option value="0">-- Pilih sesi --</option>
                                    <option value="1">Sesi 1 (07:30-10:00)</option>
                                    <option value="2">Sesi 2 (10:20-12:50)</option>
                                    <option value="3">Sesi 3 (13:30-16:00)</option>
                                </select>
                                @error('requestedSession') <p class="text-danger-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @endif

                        {{-- Reason --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alasan Pengajuan</label>
                            <textarea wire:model="reason" rows="3" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm shadow-sm resize-none" placeholder="Jelaskan alasan secara detail..."></textarea>
                            @error('reason') <p class="text-danger-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-3 pt-2">
                            <x-ui.button type="button" variant="white" wire:click="closeForm" class="flex-1 justify-center">Batal</x-ui.button>
                            <x-ui.button type="submit" variant="primary" class="flex-1 justify-center">
                                <span wire:loading.remove wire:target="submitForm">Kirim Pengajuan</span>
                                <span wire:loading wire:target="submitForm" class="flex items-center">
                                    <x-ui.icon name="arrow-path" class="w-4 h-4 animate-spin mr-2" />
                                    Mengirim...
                                </span>
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Detail Modal --}}
    @if($viewingRequest)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="closeView"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">Detail Pengajuan</h3>
                    <button wire:click="closeView" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <x-ui.icon name="x" class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Status badges --}}
                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge :variant="$viewingRequest->change_type === 'reschedule' ? 'info' : 'warning'">
                            {{ $viewingRequest->getChangeTypeLabel() }}
                        </x-ui.badge>
                        @php
                            $statusVariant = [
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'cancelled' => 'secondary'
                            ][$viewingRequest->status] ?? 'primary';
                        @endphp
                        <x-ui.badge :variant="$statusVariant">
                            {{ $viewingRequest->getStatusLabel() }}
                        </x-ui.badge>
                    </div>

                    @if($activeTab === 'admin')
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center border border-gray-200 shrink-0">
                            <x-ui.icon name="user" class="w-5 h-5 text-gray-500" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Pemohon</p>
                            <p class="font-bold text-gray-900">{{ $viewingRequest->user->name ?? '-' }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 {{ $viewingRequest->change_type === 'reschedule' ? 'sm:grid-cols-2 gap-4' : 'gap-4' }}">
                        {{-- Original Schedule --}}
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center"><x-ui.icon name="calendar" class="w-3.5 h-3.5 mr-1" /> Jadwal Asal</p>
                            @if($viewingRequest->originalAssignment)
                            <p class="font-bold text-gray-900 mb-0.5">{{ \Carbon\Carbon::parse($viewingRequest->originalAssignment->date)->format('d M Y') }}</p>
                            <p class="text-sm font-medium text-gray-600">Sesi {{ $viewingRequest->originalAssignment->session }} ({{ $viewingRequest->originalAssignment->time_start }}-{{ $viewingRequest->originalAssignment->time_end }})</p>
                            @else
                            <p class="text-gray-400 italic">Jadwal tidak ditemukan</p>
                            @endif
                        </div>

                        {{-- Target Schedule (for reschedule) --}}
                        @if($viewingRequest->change_type === 'reschedule' && $viewingRequest->requested_date)
                        <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100">
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1.5 flex items-center"><x-ui.icon name="arrow-right" class="w-3.5 h-3.5 mr-1" /> Jadwal Tujuan</p>
                            <p class="font-bold text-blue-700 mb-0.5">{{ $viewingRequest->requested_date->format('d M Y') }}</p>
                            <p class="text-sm font-medium text-blue-600">{{ $viewingRequest->getSessionLabel() }}</p>
                        </div>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Alasan Pengajuan</p>
                        <p class="text-gray-700 bg-white p-3 border border-gray-100 rounded-xl text-sm leading-relaxed">{{ $viewingRequest->reason }}</p>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-400 font-medium">
                        <span>Diajukan: {{ $viewingRequest->created_at->format('d M Y H:i') }}</span>
                    </div>

                    @if($viewingRequest->admin_responded_at)
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggapan Admin</p>
                        <p class="text-sm text-gray-600 mb-2">Oleh <span class="font-semibold">{{ $viewingRequest->adminResponder?->name ?? '-' }}</span> pada {{ $viewingRequest->admin_responded_at->format('d M Y H:i') }}</p>
                        @if($viewingRequest->admin_response)
                        <div class="border-l-2 border-gray-300 pl-3 py-1">
                            <p class="text-gray-700 text-sm italic">"{{ $viewingRequest->admin_response }}"</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="p-6 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl flex gap-3">
                    @if($activeTab === 'admin' && $viewingRequest->status === 'pending')
                        <x-ui.button wire:click="openReview({{ $viewingRequest->id }}, 'rejected')" variant="danger" class="flex-1 justify-center bg-white" outline>Tolak</x-ui.button>
                        <x-ui.button wire:click="openReview({{ $viewingRequest->id }}, 'approved')" variant="success" class="flex-1 justify-center">Setujui</x-ui.button>
                    @elseif($activeTab === 'my-requests' && $viewingRequest->status === 'pending')
                        <x-ui.button wire:click="closeView" variant="white" class="flex-1 justify-center">Tutup</x-ui.button>
                        <x-ui.button wire:click="cancelRequest({{ $viewingRequest->id }})" wire:confirm="Yakin ingin membatalkan pengajuan ini?" variant="danger" class="flex-1 justify-center">Batalkan Pengajuan</x-ui.button>
                    @else
                        <x-ui.button wire:click="closeView" variant="white" class="w-full justify-center">Tutup</x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Review Modal --}}
    @if($reviewingId)
    <div class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="closeReview"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.stop>
                <div class="p-6">
                    <div class="w-12 h-12 {{ $reviewAction === 'approved' ? 'bg-success-100 text-success-600' : 'bg-danger-100 text-danger-600' }} rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-ui.icon name="{{ $reviewAction === 'approved' ? 'check' : 'x' }}" class="w-6 h-6" />
                    </div>
                    
                    <h3 class="text-xl font-bold text-center text-gray-900 mb-2">
                        {{ $reviewAction === 'approved' ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?' }}
                    </h3>
                    <p class="text-center text-gray-500 text-sm mb-6">Silakan tambahkan catatan opsional untuk pemohon.</p>

                    <div class="mb-6">
                        <textarea wire:model="reviewNotes" rows="3" class="w-full border-gray-300 focus:border-{{ $reviewAction === 'approved' ? 'success' : 'danger' }}-500 focus:ring-{{ $reviewAction === 'approved' ? 'success' : 'danger' }}-500 rounded-xl text-sm shadow-sm resize-none" placeholder="Tambahkan catatan (opsional)..."></textarea>
                    </div>

                    <div class="flex gap-3">
                        <x-ui.button wire:click="closeReview" variant="white" class="flex-1 justify-center">Batal</x-ui.button>
                        <x-ui.button wire:click="submitReview" variant="{{ $reviewAction === 'approved' ? 'success' : 'danger' }}" class="flex-1 justify-center">
                            {{ $reviewAction === 'approved' ? 'Ya, Setujui' : 'Ya, Tolak' }}
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>