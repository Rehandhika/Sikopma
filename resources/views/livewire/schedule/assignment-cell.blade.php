<div class="relative group">
    @if($hasAssignment)
        {{-- Cell with Assignment --}}
        <div class="p-4 rounded-lg border-2 transition-all duration-200 cursor-pointer
                    {{ $hasAvailabilityWarning ? 'border-yellow-300 bg-yellow-50 hover:border-yellow-400' : 'border-green-300 bg-green-50 hover:border-green-400' }}
                    {{ !$isEditable ? 'opacity-75 cursor-not-allowed' : '' }}"
             wire:click="selectCell">
            
            <div class="flex items-center space-x-3">
                {{-- User Avatar --}}
                <div class="flex-shrink-0">
                    @if(!empty($assignment['user_photo']))
                        <img src="{{ asset('storage/' . $assignment['user_photo']) }}" 
                             alt="{{ $assignment['user_name'] }}"
                             class="w-10 h-10 rounded-full object-cover border-2 {{ $hasAvailabilityWarning ? 'border-yellow-400' : 'border-green-400' }}">
                    @else
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm
                                    {{ $hasAvailabilityWarning ? 'bg-yellow-500' : 'bg-green-500' }}">
                            {{ $this->getUserInitials() }}
                        </div>
                    @endif
                </div>
                
                {{-- User Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">
                        {{ $assignment['user_name'] }}
                    </p>
                    <p class="text-xs text-gray-600">
                        {{ $assignment['user_nim'] ?? 'No NIM' }}
                    </p>
                </div>
                
                {{-- Status Indicator --}}
                <div class="flex-shrink-0">
                    @if($hasAvailabilityWarning)
                        <div class="flex items-center text-yellow-600" title="User tidak tersedia pada waktu ini">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @else
                        <div class="flex items-center text-green-600" title="User tersedia">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Remove Button (shown on hover) --}}
            @if($isEditable)
                <button wire:click.stop="removeAssignment"
                        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200
                               p-1 rounded-full bg-red-500 hover:bg-red-600 text-white"
                        title="Hapus assignment">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>
    @else
        {{-- Empty Cell --}}
        <div class="p-4 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 
                    transition-all duration-200 cursor-pointer
                    {{ $isEditable ? 'hover:border-blue-400 hover:bg-blue-50' : 'opacity-50 cursor-not-allowed' }}"
             wire:click="selectCell">
            
            <div class="flex items-center justify-center h-16">
                @if($isEditable)
                    <div class="text-center">
                        <svg class="w-8 h-8 mx-auto text-gray-400 group-hover:text-blue-500 transition-colors" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <p class="text-xs text-gray-500 mt-1 group-hover:text-blue-600">Assign User</p>
                    </div>
                @else
                    <div class="text-center">
                        <svg class="w-8 h-8 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <p class="text-xs text-gray-400 mt-1">Tidak ada assignment</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Tooltip with additional info (shown on hover) --}}
    @if($hasAssignment)
        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
            <div class="font-semibold">{{ $assignment['user_name'] }}</div>
            <div class="text-gray-300">{{ $this->getFormattedDate() }}</div>
            <div class="text-gray-300">{{ $this->getSessionTime() }}</div>
            @if($hasAvailabilityWarning)
                <div class="text-yellow-400 mt-1">⚠ Tidak tersedia</div>
            @endif
            {{-- Arrow --}}
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                <div class="border-4 border-transparent border-t-gray-900"></div>
            </div>
        </div>
    @endif
</div>
