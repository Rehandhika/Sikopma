<div class="max-w-5xl mx-auto space-y-6 pb-20" 
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
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Izin & Cuti</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola dan pantau permohonan izin atau cuti anggota</p>
        </div>
        <div class="flex items-center gap-3">
            <x-ui.button 
                wire:click="openForm" 
                variant="primary" 
                icon="plus"
                class="shadow-lg shadow-primary-200">
                Ajukan Baru
            </x-ui.button>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden p-1">
        <nav class="flex flex-wrap gap-1">
            <button 
                wire:click="setTab('my-requests')" 
                @class([
                    'flex-1 min-w-[140px] px-4 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2',
                    'bg-primary-600 text-white shadow-md' => $activeTab === 'my-requests',
                    'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' => $activeTab !== 'my-requests'
                ])>
                <x-ui.icon name="user" class="w-4 h-4" />
                Pengajuan Saya
                @if($stats['pending'] > 0 && $activeTab === 'my-requests')
                    <span class="px-1.5 py-0.5 text-[10px] bg-white text-primary-600 rounded-full">{{ $stats['pending'] }}</span>
                @endif
            </button>

            @if($isAdmin)
                <button 
                    wire:click="setTab('approvals')" 
                    @class([
                        'flex-1 min-w-[140px] px-4 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2',
                        'bg-primary-600 text-white shadow-md' => $activeTab === 'approvals',
                        'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' => $activeTab !== 'approvals'
                    ])>
                    <x-ui.icon name="check-badge" class="w-4 h-4" />
                    Persetujuan Admin
                    @if($stats['pending'] > 0 && $activeTab === 'approvals')
                        <span class="px-1.5 py-0.5 text-[10px] bg-white text-primary-600 rounded-full">{{ $stats['pending'] }}</span>
                    @endif
                </button>
            @endif
        </nav>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        <!-- Sidebar Filters & Stats -->
        <div class="space-y-6">
            <x-ui.card padding="true" class="bg-primary-600 text-white overflow-hidden relative shadow-lg">
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-80">Total Menunggu</p>
                    <p class="text-4xl font-black mt-1">{{ $stats['pending'] }}</p>
                    <div class="mt-4 flex items-center gap-2 text-xs font-bold bg-white/20 p-2 rounded-lg">
                        <x-ui.icon name="information-circle" class="w-4 h-4" />
                        <span>Butuh tindakan segera</span>
                    </div>
                </div>
                <x-ui.icon name="document-text" class="w-24 h-24 absolute -right-4 -bottom-4 opacity-10 text-white" />
            </x-ui.card>

            <x-ui.card padding="true" class="shadow-sm">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Filter Status</h2>
                <div class="space-y-2">
                    @foreach(['all' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $val => $label)
                        <button 
                            wire:click="$set('statusFilter', '{{ $val }}')"
                            @class([
                                'w-full text-left px-3 py-2 rounded-lg text-sm transition-all flex items-center justify-between group',
                                'bg-primary-50 text-primary-700 font-bold border-l-4 border-primary-600' => $statusFilter === $val,
                                'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' => $statusFilter !== $val
                            ])>
                            {{ $label }}
                            @if($statusFilter === $val)
                                <x-ui.icon name="chevron-right" class="w-4 h-4" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <!-- Main List Content -->
        <div class="lg:col-span-3 space-y-4">
            @forelse($requests as $req)
                <x-ui.card padding="false" class="overflow-hidden hover:shadow-md transition-all border-l-4 group
                    {{ match($req->status) {
                        'pending' => 'border-l-warning-500',
                        'approved' => 'border-l-success-500',
                        'rejected' => 'border-l-danger-500',
                        default => 'border-l-gray-300'
                    } }}">
                    <div class="p-5 flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0 cursor-pointer" wire:click="viewRequest({{ $req->id }})">
                            <div class="flex items-center gap-3 mb-2">
                                @if($activeTab === 'approvals' && $req->user)
                                    <div class="flex items-center gap-2">
                                        <x-ui.avatar :name="$req->user->name" size="xs" />
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $req->user->name }}</span>
                                    </div>
                                    <span class="text-gray-300 dark:text-gray-600">|</span>
                                @endif
                                <x-ui.badge 
                                    :variant="match($req->leave_type) {
                                        'sick' => 'danger',
                                        'emergency' => 'warning',
                                        'permission' => 'primary',
                                        default => 'gray'
                                    }" 
                                    size="sm"
                                    class="font-black uppercase tracking-tighter">
                                    {{ $req->getLeaveTypeLabel() }}
                                </x-ui.badge>
                                <span class="text-[10px] text-gray-400 font-medium">Diajukan {{ $req->created_at->diffForHumans() }}</span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $req->start_date->format('d M') }} - {{ $req->end_date->format('d M Y') }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5 font-medium">
                                <x-ui.icon name="clock" class="w-4 h-4 text-primary-500" />
                                Durasi {{ $req->total_days }} Hari
                            </p>
                            
                            @if($req->reason)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 line-clamp-1 italic font-medium bg-gray-50 dark:bg-gray-800/50 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                                    "{{ $req->reason }}"
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-col items-end justify-between self-stretch shrink-0">
                            <x-ui.badge 
                                :variant="match($req->status) {
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'cancelled' => 'gray',
                                    default => 'secondary'
                                }"
                                size="md"
                                class="rounded-full px-3 font-bold">
                                {{ match($req->status) {
                                    'pending' => 'Menunggu',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    'cancelled' => 'Batal',
                                    default => $req->status
                                } }}
                            </x-ui.badge>

                            <div class="flex items-center gap-1" wire:click.stop>
                                @if($activeTab === 'approvals' && $req->status === 'pending')
                                    <button wire:click="openReview({{ $req->id }}, 'approved')" class="p-2 text-success-600 hover:bg-success-50 dark:hover:bg-success-900/20 rounded-lg transition-colors shadow-sm bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700" title="Setujui">
                                        <x-ui.icon name="check" class="w-5 h-5" stroke-width="3" />
                                    </button>
                                    <button wire:click="openReview({{ $req->id }}, 'rejected')" class="p-2 text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg transition-colors shadow-sm bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700" title="Tolak">
                                        <x-ui.icon name="x-mark" class="w-5 h-5" stroke-width="3" />
                                    </button>
                                @endif
                                <button wire:click="viewRequest({{ $req->id }})" class="p-2 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors shadow-sm bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700" title="Detail">
                                    <x-ui.icon name="eye" class="w-5 h-5" stroke-width="2" />
                                </button>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <x-ui.icon name="document-text" class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tidak ada data</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">Tidak ditemukan pengajuan izin atau cuti pada kriteria ini.</p>
                </div>
            @endforelse

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        </div>
    </div>

    {{-- Create Form Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeForm"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-gray-700" @click.stop>
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tighter flex items-center gap-2">
                        <x-ui.icon name="pencil-square" class="w-5 h-5 text-primary-600" />
                        Ajukan Izin/Cuti Baru
                    </h3>
                    <button wire:click="closeForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <x-ui.icon name="x-mark" class="w-6 h-6" />
                    </button>
                </div>
                
                <form wire:submit="submitForm" class="p-6 space-y-6">
                    {{-- Type Selector --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Jenis Pengajuan</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach(['permission' => 'Izin', 'sick' => 'Sakit', 'emergency' => 'Darurat', 'other' => 'Lainnya'] as $val => $label)
                                <button 
                                    type="button" 
                                    wire:click="$set('leave_type', '{{ $val }}')" 
                                    @class([
                                        'p-3 border-2 rounded-xl text-sm font-bold transition-all text-center',
                                        'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400 ring-2 ring-primary-100' => $leave_type === $val,
                                        'border-gray-100 bg-gray-50 text-gray-500 dark:bg-gray-700 dark:border-gray-600 hover:border-gray-200' => $leave_type !== $val
                                    ])>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal Mulai</label>
                            <input type="date" wire:model.live="start_date" min="{{ now()->format('Y-m-d') }}" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-xl text-sm font-bold focus:ring-primary-500 focus:border-primary-500 transition-all">
                            @error('start_date') <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal Selesai</label>
                            <input type="date" wire:model.live="end_date" min="{{ $start_date }}" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-xl text-sm font-bold focus:ring-primary-500 focus:border-primary-500 transition-all">
                            @error('end_date') <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if($totalDays > 0)
                        <div class="bg-primary-50 dark:bg-primary-900/20 p-3 rounded-xl border border-primary-100 dark:border-primary-800/30 flex items-center justify-between shadow-sm">
                            <span class="text-xs font-bold text-primary-700 dark:text-primary-400 uppercase tracking-wider">Estimasi Durasi:</span>
                            <span class="px-3 py-1 bg-primary-600 text-white rounded-full text-xs font-black shadow-md shadow-primary-200">{{ $totalDays }} Hari</span>
                        </div>
                    @endif

                    {{-- Affected Schedules Indicator --}}
                    @if(count($affectedSchedules) > 0)
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Shift yang Terdampak ({{ count($affectedSchedules) }})</label>
                            <div class="max-h-32 overflow-y-auto space-y-2 pr-2">
                                @foreach($affectedSchedules as $sch)
                                    <div class="flex items-center justify-between text-[11px] bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg border border-gray-100 dark:border-gray-600">
                                        <div class="font-bold text-gray-700 dark:text-gray-300">
                                            {{ $sch['date'] }} <span class="text-gray-400 font-medium ml-1">Sesi {{ $sch['session'] }}</span>
                                        </div>
                                        <div class="text-primary-600 font-black tracking-tighter uppercase">Excused</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Reason --}}
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Alasan Pengajuan</label>
                        <textarea wire:model="reason" rows="3" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-xl text-sm font-medium focus:ring-primary-500 focus:border-primary-500 transition-all placeholder:text-gray-300" placeholder="Berikan penjelasan yang lengkap..."></textarea>
                        @error('reason') <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    {{-- Attachment --}}
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Lampiran Pendukung (Optional)</label>
                        <div class="relative">
                            <input type="file" wire:model="attachment" id="attachment" class="hidden">
                            <label for="attachment" class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all cursor-pointer group">
                                @if($attachment)
                                    <x-ui.icon name="check-circle" class="w-8 h-8 text-success-500 mb-1" />
                                    <p class="text-xs font-bold text-success-600 truncate px-4">{{ $attachment->getClientOriginalName() }}</p>
                                @else
                                    <x-ui.icon name="paper-clip" class="w-8 h-8 text-gray-300 group-hover:text-primary-500 transition-colors" />
                                    <p class="text-[10px] font-bold text-gray-400 group-hover:text-primary-600">Klik untuk upload file (PDF/JPG)</p>
                                @endif
                            </label>
                        </div>
                        @error('attachment') <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    {{-- Footer Actions --}}
                    <div class="flex gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <x-ui.button type="button" wire:click="closeForm" variant="white" class="flex-1 font-bold rounded-xl py-3">Batal</x-ui.button>
                        <x-ui.button type="submit" variant="primary" class="flex-1 font-black rounded-xl py-3 shadow-lg shadow-primary-200" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submitForm">Kirim Pengajuan</span>
                            <span wire:loading wire:target="submitForm">Mengirim...</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Detail Modal --}}
    @if($viewingRequest)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeView"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-700" @click.stop>
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <x-ui.icon name="document-text" class="w-5 h-5 text-primary-600" />
                        Detail Pengajuan
                    </h3>
                    <button wire:click="closeView" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <x-ui.icon name="x-mark" class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <x-ui.badge 
                            :variant="match($viewingRequest->status) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'cancelled' => 'gray',
                                default => 'secondary'
                            }"
                            size="md"
                            class="rounded-full font-black uppercase tracking-tighter px-4">
                            {{ match($viewingRequest->status) {
                                'pending' => 'Menunggu Review',
                                'approved' => 'Sudah Disetujui',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
                                default => $viewingRequest->status
                            } }}
                        </x-ui.badge>
                        <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $viewingRequest->getLeaveTypeLabel() }}</span>
                    </div>

                    @if($activeTab === 'approvals' && $viewingRequest->user)
                        <div class="p-4 bg-primary-50 dark:bg-primary-900/10 rounded-xl border border-primary-100 dark:border-primary-800/30 flex items-center gap-3 shadow-sm">
                            <x-ui.avatar :name="$viewingRequest->user->name" size="md" />
                            <div>
                                <p class="text-[10px] font-black text-primary-600 uppercase tracking-widest">Pemohon</p>
                                <p class="font-black text-gray-900 dark:text-white leading-tight">{{ $viewingRequest->user->name }}</p>
                                <p class="text-xs text-primary-700 dark:text-primary-400 font-bold mt-0.5">{{ $viewingRequest->user->nim }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Mulai</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $viewingRequest->start_date->format('d M Y') }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Selesai</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $viewingRequest->end_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Alasan</p>
                        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-700 italic text-sm text-gray-700 dark:text-gray-300 font-medium shadow-sm">
                            "{{ $viewingRequest->reason }}"
                        </div>
                    </div>

                    @if($viewingRequest->attachment)
                        <a href="{{ $this->getAttachmentUrl($viewingRequest->attachment) }}" target="_blank" class="flex items-center justify-between gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800/30 text-blue-700 dark:text-blue-400 hover:bg-blue-100 transition-colors group shadow-sm">
                            <div class="flex items-center gap-3">
                                <x-ui.icon name="paper-clip" class="w-5 h-5 group-hover:rotate-12 transition-transform" />
                                <span class="text-xs font-black uppercase tracking-wider">Dokumen Lampiran</span>
                            </div>
                            <x-ui.icon name="chevron-right" class="w-4 h-4" />
                        </a>
                    @endif

                    @if($viewingRequest->reviewed_at)
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border-l-4 border-primary-500 shadow-sm">
                            <div class="flex items-center gap-2 mb-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <x-ui.icon name="check-badge" class="w-4 h-4 text-primary-500" />
                                Reviewer: {{ $viewingRequest->reviewer?->name }}
                            </div>
                            <p class="text-xs text-gray-500 mb-2 font-bold">{{ $viewingRequest->reviewed_at->format('d M Y, H:i') }}</p>
                            @if($viewingRequest->review_notes)
                                <p class="text-sm text-gray-700 dark:text-gray-300 font-medium bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                                    "{{ $viewingRequest->review_notes }}"
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer Actions --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex gap-3 mt-4">
                    @if($activeTab === 'approvals' && $viewingRequest->status === 'pending')
                        <x-ui.button wire:click="openReview({{ $viewingRequest->id }}, 'rejected')" variant="danger" class="flex-1 font-bold rounded-xl py-2.5">Tolak</x-ui.button>
                        <x-ui.button wire:click="openReview({{ $viewingRequest->id }}, 'approved')" variant="success" class="flex-1 font-black rounded-xl py-2.5 shadow-md shadow-success-100">Setujui</x-ui.button>
                    @elseif($activeTab === 'my-requests' && $viewingRequest->status === 'pending')
                        <x-ui.button wire:click="closeView" variant="white" class="flex-1 font-bold rounded-xl py-2.5 border-gray-200">Tutup</x-ui.button>
                        <x-ui.button wire:click="cancelRequest({{ $viewingRequest->id }})" wire:confirm="Yakin ingin membatalkan?" variant="danger" class="flex-1 font-black rounded-xl py-2.5 shadow-md shadow-danger-100">Batalkan</x-ui.button>
                    @else
                        <x-ui.button wire:click="closeView" variant="white" class="w-full font-bold rounded-xl py-2.5 border-gray-200">Tutup</x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Review Action Modal --}}
    @if($reviewingId)
    <div class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeReview"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-700" @click.stop>
                <div class="p-6 text-center">
                    <div @class([
                        'w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4',
                        'bg-success-100 text-success-600 shadow-inner' => $reviewAction === 'approved',
                        'bg-danger-100 text-danger-600 shadow-inner' => $reviewAction === 'rejected'
                    ])>
                        <x-ui.icon :name="$reviewAction === 'approved' ? 'check' : 'x-mark'" class="w-10 h-10" stroke-width="3" />
                    </div>
                    
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-2">
                        {{ $reviewAction === 'approved' ? 'Setujui Pengajuan' : 'Tolak Pengajuan' }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 font-medium leading-relaxed">
                        {{ $reviewAction === 'approved' 
                            ? 'Setelah disetujui, jadwal shift yang bersangkutan akan otomatis ditandai sebagai izin.' 
                            : 'Berikan alasan penolakan agar anggota dapat memahami keputusannya.' }}
                    </p>

                    <div class="text-left mb-6">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Catatan Review (Opsional)</label>
                        <textarea wire:model="reviewNotes" rows="3" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 rounded-xl text-sm font-medium focus:ring-primary-500 focus:border-primary-500 transition-all placeholder:text-gray-300" placeholder="Tulis catatan atau alasan di sini..."></textarea>
                    </div>

                    <div class="flex gap-3">
                        <x-ui.button wire:click="closeReview" variant="white" class="flex-1 font-bold rounded-xl py-3 border-gray-200">Batal</x-ui.button>
                        <x-ui.button 
                            wire:click="submitReview" 
                            @class([
                                'flex-1 font-black rounded-xl text-white shadow-lg py-3',
                                'bg-success-600 hover:bg-success-700 shadow-success-200' => $reviewAction === 'approved',
                                'bg-danger-600 hover:bg-danger-700 shadow-danger-200' => $reviewAction === 'rejected'
                            ])>
                            Ya, Konfirmasi
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
