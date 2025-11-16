<div>
    @if($show)
    {{-- Modal Overlay --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 wire:click="closeModal"></div>

            {{-- Modal panel --}}
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white" id="modal-title">
                                Pilih Anggota
                            </h3>
                            <p class="text-sm text-blue-100 mt-1">
                                {{ $this->getFormattedDate() }} • Sesi {{ $session }} ({{ $this->getSessionTime() }})
                            </p>
                        </div>
                        <button wire:click="closeModal" 
                                class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Search --}}
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Cari nama atau NIM...">
                    </div>
                </div>

                {{-- User List --}}
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    {{-- Available Users --}}
                    @if(count($availableUsers) > 0)
                        <div class="mb-6">
                            <div class="flex items-center mb-3">
                                <div class="flex items-center justify-center w-6 h-6 rounded-full bg-green-100 mr-2">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900">
                                    Tersedia ({{ count($availableUsers) }})
                                </h4>
                            </div>
                            
                            <div class="space-y-2">
                                @foreach($availableUsers as $user)
                                    <button wire:click="selectUser({{ $user['id'] }})"
                                            class="w-full flex items-center p-3 rounded-lg border-2 border-green-200 bg-green-50 hover:bg-green-100 hover:border-green-300 transition-all duration-200 group">
                                        {{-- Avatar --}}
                                        <div class="flex-shrink-0 mr-3">
                                            @if(!empty($user['photo']))
                                                <img src="{{ asset('storage/' . $user['photo']) }}" 
                                                     alt="{{ $user['name'] }}"
                                                     class="w-12 h-12 rounded-full object-cover border-2 border-green-400">
                                            @else
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-500 text-white font-semibold border-2 border-green-400">
                                                    {{ $this->getUserInitials($user['name']) }}
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- User Info --}}
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $user['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-600">
                                                {{ $user['nim'] ?? 'No NIM' }}
                                            </p>
                                        </div>
                                        
                                        {{-- Stats --}}
                                        <div class="flex-shrink-0 text-right mr-3">
                                            <p class="text-xs text-gray-600">
                                                Shifts: <span class="font-semibold text-gray-900">{{ $user['current_shifts'] }}</span>
                                            </p>
                                            <p class="text-xs text-green-600 font-medium">
                                                {{ $user['availability_level'] === 'high' ? 'Tersedia' : 'Available' }}
                                            </p>
                                        </div>
                                        
                                        {{-- Select Icon --}}
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-green-600 group-hover:text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Not Available Users --}}
                    @if(count($notAvailableUsers) > 0)
                        <div class="mb-6">
                            <div class="flex items-center mb-3">
                                <div class="flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100 mr-2">
                                    <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900">
                                    Tidak Tersedia / Konflik ({{ count($notAvailableUsers) }})
                                </h4>
                            </div>
                            
                            <div class="space-y-2">
                                @foreach($notAvailableUsers as $user)
                                    <button wire:click="selectUser({{ $user['id'] }})"
                                            class="w-full flex items-center p-3 rounded-lg border-2 border-yellow-200 bg-yellow-50 hover:bg-yellow-100 hover:border-yellow-300 transition-all duration-200 group">
                                        {{-- Avatar --}}
                                        <div class="flex-shrink-0 mr-3">
                                            @if(!empty($user['photo']))
                                                <img src="{{ asset('storage/' . $user['photo']) }}" 
                                                     alt="{{ $user['name'] }}"
                                                     class="w-12 h-12 rounded-full object-cover border-2 border-yellow-400 opacity-75">
                                            @else
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-yellow-500 text-white font-semibold border-2 border-yellow-400 opacity-75">
                                                    {{ $this->getUserInitials($user['name']) }}
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- User Info --}}
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $user['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-600">
                                                {{ $user['nim'] ?? 'No NIM' }}
                                            </p>
                                        </div>
                                        
                                        {{-- Stats --}}
                                        <div class="flex-shrink-0 text-right mr-3">
                                            <p class="text-xs text-gray-600">
                                                Shifts: <span class="font-semibold text-gray-900">{{ $user['current_shifts'] }}</span>
                                            </p>
                                            @if($user['has_conflict'])
                                                <p class="text-xs text-red-600 font-medium">
                                                    Sudah ditugaskan
                                                </p>
                                            @else
                                                <p class="text-xs text-yellow-600 font-medium">
                                                    Tidak tersedia
                                                </p>
                                            @endif
                                        </div>
                                        
                                        {{-- Select Icon --}}
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-yellow-600 group-hover:text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Inactive Users --}}
                    @if(count($inactiveUsers) > 0)
                        <div class="mb-6">
                            <div class="flex items-center mb-3">
                                <div class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 mr-2">
                                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900">
                                    Tidak Aktif ({{ count($inactiveUsers) }})
                                </h4>
                            </div>
                            
                            <div class="space-y-2">
                                @foreach($inactiveUsers as $user)
                                    <div class="w-full flex items-center p-3 rounded-lg border-2 border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed">
                                        {{-- Avatar --}}
                                        <div class="flex-shrink-0 mr-3">
                                            @if(!empty($user['photo']))
                                                <img src="{{ asset('storage/' . $user['photo']) }}" 
                                                     alt="{{ $user['name'] }}"
                                                     class="w-12 h-12 rounded-full object-cover border-2 border-gray-300 grayscale">
                                            @else
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-400 text-white font-semibold border-2 border-gray-300">
                                                    {{ $this->getUserInitials($user['name']) }}
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- User Info --}}
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-semibold text-gray-700">
                                                {{ $user['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $user['nim'] ?? 'No NIM' }}
                                            </p>
                                        </div>
                                        
                                        {{-- Status --}}
                                        <div class="flex-shrink-0 text-right">
                                            <p class="text-xs text-gray-500 font-medium">
                                                Status: {{ ucfirst($user['status']) }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- No Results --}}
                    @if(count($availableUsers) === 0 && count($notAvailableUsers) === 0 && count($inactiveUsers) === 0)
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="mt-4 text-sm text-gray-600">
                                @if($search)
                                    Tidak ada anggota yang cocok dengan pencarian "{{ $search }}"
                                @else
                                    Tidak ada anggota tersedia
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-600">
                            Total: {{ count($availableUsers) + count($notAvailableUsers) + count($inactiveUsers) }} anggota
                        </p>
                        <button wire:click="closeModal" 
                                class="btn btn-secondary">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
