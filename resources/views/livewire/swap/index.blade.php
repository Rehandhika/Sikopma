<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Tukar Shift</h2>
        <a href="{{ route('swap.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
            </svg>
            Buat Permintaan
        </a>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button wire:click="$set('tab', 'my-requests')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm {{ $tab === 'my-requests' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Permintaan Saya
                </button>
                <button wire:click="$set('tab', 'received')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm {{ $tab === 'received' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Permintaan Masuk
                </button>
            </nav>
        </div>

        <!-- Swap List -->
        <div class="p-6">
            <div class="space-y-4">
                @forelse($swaps as $swap)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="badge {{ 
                                        $swap->status === 'accepted' ? 'badge-secondary' : 
                                        ($swap->status === 'pending' ? 'badge-warning' : 'badge-danger') 
                                    }}">
                                        {{ ucfirst($swap->status) }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ $swap->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <div class="text-xs text-gray-500 mb-1">Dari</div>
                                        <div class="font-medium">{{ $swap->requester->name }}</div>
                                        <div class="text-sm text-gray-600">
                                            {{ $swap->originalSchedule->date->format('d/m/Y') }} - 
                                            Sesi {{ $swap->originalSchedule->session }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500 mb-1">Ke</div>
                                        <div class="font-medium">{{ $swap->target->name }}</div>
                                        @if($swap->targetSchedule)
                                            <div class="text-sm text-gray-600">
                                                {{ $swap->targetSchedule->date->format('d/m/Y') }} - 
                                                Sesi {{ $swap->targetSchedule->session }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($swap->reason)
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">Alasan:</span> {{ $swap->reason }}
                                    </div>
                                @endif
                            </div>

                            @if($tab === 'received' && $swap->status === 'pending')
                                <div class="flex space-x-2 ml-4">
                                    <button wire:click="acceptRequest({{ $swap->id }})" 
                                            class="btn btn-sm btn-secondary">
                                        Terima
                                    </button>
                                    <button wire:click="rejectRequest({{ $swap->id }})" 
                                            class="btn btn-sm btn-danger">
                                        Tolak
                                    </button>
                                </div>
                            @elseif($tab === 'my-requests' && $swap->status === 'pending')
                                <button wire:click="cancelRequest({{ $swap->id }})" 
                                        wire:confirm="Batalkan permintaan?"
                                        class="btn btn-sm btn-white ml-4">
                                    Batalkan
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                        <p>Tidak ada permintaan tukar shift</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div>
        {{ $swaps->links() }}
    </div>
</div>
