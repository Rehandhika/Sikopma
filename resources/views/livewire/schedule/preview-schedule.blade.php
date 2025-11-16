<div>
    @if($show)
    {{-- Modal Overlay --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 wire:click="closePreview"></div>

            {{-- Modal panel --}}
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white" id="modal-title">
                                Preview Jadwal
                            </h3>
                            <p class="text-sm text-blue-100 mt-1">
                                {{ $this->getDateRange() }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            {{-- View Mode Toggle --}}
                            <div class="flex items-center space-x-1 bg-blue-800 rounded-lg p-1">
                                <button wire:click="setViewMode('calendar')"
                                        class="px-3 py-1 rounded text-xs font-medium transition-colors {{ $viewMode === 'calendar' ? 'bg-white text-blue-700' : 'text-blue-100 hover:text-white' }}">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Calendar
                                </button>
                                <button wire:click="setViewMode('list')"
                                        class="px-3 py-1 rounded text-xs font-medium transition-colors {{ $viewMode === 'list' ? 'bg-white text-blue-700' : 'text-blue-100 hover:text-white' }}">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    List
                                </button>
                            </div>
                            
                            {{-- Print Button --}}
                            <button wire:click="printSchedule"
                                    class="px-3 py-1 bg-white text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Print
                            </button>
                            
                            {{-- Close Button --}}
                            <button wire:click="closePreview" 
                                    class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                    @if($viewMode === 'calendar')
                        {{-- Calendar View --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                            Hari / Tanggal
                                        </th>
                                        @for($session = 1; $session <= 3; $session++)
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sesi {{ $session }}<br>
                                            <span class="text-xs font-normal text-gray-400">{{ $this->getSessionTime($session) }}</span>
                                        </th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($assignments as $dateStr => $sessions)
                                        @php
                                            $date = \Carbon\Carbon::parse($dateStr);
                                            $dayName = $date->locale('id')->dayName;
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-gray-900">{{ $dayName }}</div>
                                                <div class="text-xs text-gray-500">{{ $date->format('d M Y') }}</div>
                                            </td>
                                            @for($session = 1; $session <= 3; $session++)
                                                <td class="px-4 py-4">
                                                    @if(isset($sessions[$session]) && $sessions[$session])
                                                        @php $assignment = $sessions[$session]; @endphp
                                                        <div class="flex items-center space-x-3 p-2 rounded-lg bg-green-50 border border-green-200 {{ $isEditable ? 'cursor-pointer hover:bg-green-100' : '' }}"
                                                             @if($isEditable) wire:click="editAssignment('{{ $dateStr }}', {{ $session }})" @endif>
                                                            {{-- Avatar --}}
                                                            <div class="flex-shrink-0">
                                                                @if(!empty($assignment['user_photo']))
                                                                    <img src="{{ asset('storage/' . $assignment['user_photo']) }}" 
                                                                         alt="{{ $assignment['user_name'] }}"
                                                                         class="w-10 h-10 rounded-full object-cover border-2 border-green-400">
                                                                @else
                                                                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-500 text-white font-semibold text-sm border-2 border-green-400">
                                                                        {{ $this->getUserInitials($assignment['user_name']) }}
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
                                                        </div>
                                                    @else
                                                        <div class="flex items-center justify-center h-16 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50">
                                                            <span class="text-xs text-gray-400">Tidak ada assignment</span>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{-- List View --}}
                        <div class="space-y-4">
                            @foreach($assignments as $dateStr => $sessions)
                                @php
                                    $date = \Carbon\Carbon::parse($dateStr);
                                    $dayName = $date->locale('id')->dayName;
                                @endphp
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <h4 class="text-sm font-semibold text-gray-900">
                                            {{ $dayName }}, {{ $date->format('d M Y') }}
                                        </h4>
                                    </div>
                                    <div class="divide-y divide-gray-200">
                                        @for($session = 1; $session <= 3; $session++)
                                            <div class="px-4 py-3 hover:bg-gray-50">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-shrink-0 w-32">
                                                        <p class="text-xs font-medium text-gray-500">Sesi {{ $session }}</p>
                                                        <p class="text-xs text-gray-400">{{ $this->getSessionTime($session) }}</p>
                                                    </div>
                                                    <div class="flex-1">
                                                        @if(isset($sessions[$session]) && $sessions[$session])
                                                            @php $assignment = $sessions[$session]; @endphp
                                                            <div class="flex items-center space-x-3 {{ $isEditable ? 'cursor-pointer' : '' }}"
                                                                 @if($isEditable) wire:click="editAssignment('{{ $dateStr }}', {{ $session }})" @endif>
                                                                {{-- Avatar --}}
                                                                <div class="flex-shrink-0">
                                                                    @if(!empty($assignment['user_photo']))
                                                                        <img src="{{ asset('storage/' . $assignment['user_photo']) }}" 
                                                                             alt="{{ $assignment['user_name'] }}"
                                                                             class="w-10 h-10 rounded-full object-cover border-2 border-green-400">
                                                                    @else
                                                                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-500 text-white font-semibold text-sm border-2 border-green-400">
                                                                            {{ $this->getUserInitials($assignment['user_name']) }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                {{-- User Info --}}
                                                                <div class="flex-1">
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{ $assignment['user_name'] }}
                                                                    </p>
                                                                    <p class="text-xs text-gray-600">
                                                                        {{ $assignment['user_nim'] ?? 'No NIM' }}
                                                                    </p>
                                                                </div>
                                                                
                                                                @if($isEditable)
                                                                <div class="flex-shrink-0">
                                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                                    </svg>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <p class="text-sm text-gray-400 italic">Tidak ada assignment</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Statistics Summary --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="grid grid-cols-3 gap-4">
                        {{-- Total Assignments --}}
                        <div class="text-center">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Assignments</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalAssignments }}</p>
                            <p class="text-xs text-gray-600">dari 12 slot</p>
                        </div>
                        
                        {{-- Coverage --}}
                        <div class="text-center">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Coverage</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($coverageRate, 1) }}%</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                                     style="width: {{ $coverageRate }}%"></div>
                            </div>
                        </div>
                        
                        {{-- Assigned Users --}}
                        <div class="text-center">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Anggota Ditugaskan</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ count($assignmentsPerUser) }}</p>
                            <p class="text-xs text-gray-600">anggota</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-end space-x-3">
                        <button wire:click="closePreview" 
                                class="btn btn-secondary">
                            Tutup
                        </button>
                        @if($isEditable)
                        <button wire:click="$dispatch('apply-preview')"
                                class="btn btn-primary">
                            Terapkan Perubahan
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Print Styles --}}
@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
@endpush

{{-- Print Script --}}
@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('print-schedule', () => {
            window.print();
        });
    });
</script>
@endpush
