<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Buat Jadwal Baru</h1>
                <p class="mt-1 text-sm text-gray-600">Buat jadwal shift untuk periode mingguan (Senin-Kamis)</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('schedule.index') }}" class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Period Selection --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Periode Jadwal</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai (Senin)</label>
                <input type="date" wire:model.live="weekStartDate" class="input" required>
                @error('weekStartDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai (Kamis)</label>
                <input type="date" wire:model.live="weekEndDate" class="input" required>
                @error('weekEndDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <input type="text" wire:model="notes" class="input" placeholder="Catatan jadwal...">
            </div>
        </div>
    </div>

    {{-- Mode Selection & Actions --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <h2 class="text-lg font-semibold text-gray-900">Mode Pembuatan</h2>
                <div class="flex items-center space-x-2">
                    <button wire:click="$set('mode', 'manual')" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $mode === 'manual' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Manual
                    </button>
                    <button wire:click="autoAssign" 
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $mode === 'auto' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed">
                        <span wire:loading.remove wire:target="autoAssign" class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Auto Assign
                        </span>
                        <span wire:loading wire:target="autoAssign" class="flex items-center">
                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generating...
                        </span>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                {{-- Undo/Redo Buttons --}}
                <div class="flex items-center space-x-1 border-r border-gray-300 pr-2 mr-2">
                    <button wire:click="undo" 
                            {{ !$canUndo ? 'disabled' : '' }}
                            class="p-2 rounded-lg transition-colors {{ $canUndo ? 'text-gray-700 hover:bg-gray-100' : 'text-gray-300 cursor-not-allowed' }}"
                            title="Undo ({{ $historyIndex }} / {{ count($history) - 1 }})">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </button>
                    <button wire:click="redo" 
                            {{ !$canRedo ? 'disabled' : '' }}
                            class="p-2 rounded-lg transition-colors {{ $canRedo ? 'text-gray-700 hover:bg-gray-100' : 'text-gray-300 cursor-not-allowed' }}"
                            title="Redo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/>
                        </svg>
                    </button>
                    @if($canUndo || $canRedo)
                    <span class="text-xs text-gray-500 ml-1">{{ $historyIndex + 1 }}/{{ count($history) }}</span>
                    @endif
                </div>
                
                @if(!empty($templates))
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="btn btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Load Template
                    </button>
                    <div x-show="open" @click.away="open = false" 
                         class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg z-10 max-h-64 overflow-y-auto">
                        @foreach($templates as $template)
                        <button wire:click="loadTemplate({{ $template['id'] }})" 
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                            <div class="font-medium text-gray-900">{{ $template['name'] }}</div>
                            <div class="text-sm text-gray-500">{{ $template['description'] ?? 'No description' }}</div>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
                
                {{-- Bulk Actions Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="btn btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        Bulk Actions
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" 
                         class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg z-10 border border-gray-200">
                        <button @click="$dispatch('open-bulk-assign-modal', { type: 'allSessions' }); open = false" 
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-100 flex items-center">
                            <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900">Assign to All Sessions</div>
                                <div class="text-xs text-gray-500">Same user for all sessions in a day</div>
                            </div>
                        </button>
                        <button @click="$dispatch('open-bulk-assign-modal', { type: 'allDays' }); open = false" 
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-100 flex items-center">
                            <svg class="w-5 h-5 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900">Assign to All Days</div>
                                <div class="text-xs text-gray-500">Same user for one session across all days</div>
                            </div>
                        </button>
                        <button wire:click="clearAll" 
                                wire:confirm="Yakin ingin menghapus semua assignment?"
                                @click="open = false"
                                class="w-full text-left px-4 py-3 hover:bg-red-50 flex items-center text-red-600">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <div>
                                <div class="font-medium">Clear All</div>
                                <div class="text-xs">Remove all assignments</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Grid --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Shift</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hari / Tanggal
                        </th>
                        @for($session = 1; $session <= 3; $session++)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Sesi {{ $session }}<br>
                            <span class="text-xs font-normal text-gray-400">{{ $this->getSessionTime($session) }}</span>
                        </th>
                        @endfor
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $startDate = \Carbon\Carbon::parse($weekStartDate);
                    @endphp
                    @for($day = 0; $day < 4; $day++)
                        @php
                            $date = $startDate->copy()->addDays($day);
                            $dateStr = $date->format('Y-m-d');
                            $dayName = $date->locale('id')->dayName;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $dayName }}</div>
                                <div class="text-sm text-gray-500">{{ $date->format('d M Y') }}</div>
                            </td>
                            @for($session = 1; $session <= 3; $session++)
                            <td class="px-6 py-4">
                                @if(isset($assignments[$dateStr][$session]) && $assignments[$dateStr][$session])
                                    @php $assignment = $assignments[$dateStr][$session]; @endphp
                                    <div class="flex items-center justify-between p-3 rounded-lg border 
                                                {{ isset($assignment['has_availability_warning']) && $assignment['has_availability_warning'] 
                                                   ? 'bg-yellow-50 border-yellow-300' 
                                                   : 'bg-blue-50 border-blue-200' }}">
                                        <div class="flex items-center space-x-3">
                                            @if($assignment['user_photo'])
                                            <img src="{{ asset('storage/' . $assignment['user_photo']) }}" 
                                                 alt="{{ $assignment['user_name'] }}" 
                                                 class="w-8 h-8 rounded-full">
                                            @else
                                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-medium">
                                                {{ substr($assignment['user_name'], 0, 1) }}
                                            </div>
                                            @endif
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <div class="text-sm font-medium text-gray-900">{{ $assignment['user_name'] }}</div>
                                                    @if(isset($assignment['has_availability_warning']) && $assignment['has_availability_warning'])
                                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="User tidak tersedia">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $assignment['user_nim'] }}</div>
                                            </div>
                                        </div>
                                        <button wire:click="removeAssignment('{{ $dateStr }}', {{ $session }})" 
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="selectCell('{{ $dateStr }}', {{ $session }})" 
                                            class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                                        <svg class="w-6 h-6 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span class="text-xs text-gray-500 mt-1 block">Assign</span>
                                    </button>
                                @endif
                            </td>
                            @endfor
                            {{-- Actions Column --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="clearDay('{{ $dateStr }}')" 
                                            wire:confirm="Yakin ingin menghapus semua assignment pada {{ $dayName }}, {{ $date->format('d M Y') }}?"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Clear Day">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    {{-- Conflicts Alert --}}
    @if(!empty($conflicts['critical']) || !empty($conflicts['warning']))
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <svg class="w-5 h-5 inline-block mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Konflik Terdeteksi
        </h2>
        
        @if(!empty($conflicts['critical']))
        <div class="mb-4">
            <h3 class="text-sm font-medium text-red-700 mb-2">Critical Issues</h3>
            <div class="space-y-2">
                @foreach($conflicts['critical'] as $conflict)
                <div class="flex items-start p-3 bg-red-50 border border-red-200 rounded-lg">
                    <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-red-800">{{ $conflict['message'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        @if(!empty($conflicts['warning']))
        <div>
            <h3 class="text-sm font-medium text-yellow-700 mb-2">Warnings</h3>
            <div class="space-y-2">
                @foreach($conflicts['warning'] as $conflict)
                <div class="flex items-start p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-yellow-800">{{ $conflict['message'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Statistics --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-sm text-blue-600 font-medium">Total Assignments</div>
                <div class="text-2xl font-bold text-blue-900 mt-1">{{ $totalAssignments }} / 12</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-sm text-green-600 font-medium">Coverage Rate</div>
                <div class="text-2xl font-bold text-green-900 mt-1">{{ number_format($coverageRate, 1) }}%</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="text-sm text-purple-600 font-medium">Unique Users</div>
                <div class="text-2xl font-bold text-purple-900 mt-1">{{ count($assignmentsPerUser) }}</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
                <div class="text-sm text-orange-600 font-medium">Unassigned Slots</div>
                <div class="text-2xl font-bold text-orange-900 mt-1">{{ 12 - $totalAssignments }}</div>
            </div>
        </div>
        
        @if(!empty($assignmentsPerUser))
        <div class="mt-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Assignments per User</h3>
            <div class="space-y-2">
                @foreach($assignmentsPerUser as $userStat)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $userStat['user_name'] }}</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($userStat['count'] / 4) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $userStat['count'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end space-x-3">
        <button wire:click="saveDraft" 
                wire:loading.attr="disabled"
                class="btn btn-secondary">
            <span wire:loading.remove wire:target="saveDraft">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Save Draft
            </span>
            <span wire:loading wire:target="saveDraft">Saving...</span>
        </button>
        
        <button wire:click="publish" 
                wire:loading.attr="disabled"
                class="btn btn-primary">
            <span wire:loading.remove wire:target="publish">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Publish Schedule
            </span>
            <span wire:loading wire:target="publish">Publishing...</span>
        </button>
    </div>

    {{-- User Selector Modal --}}
    @if($showUserSelector)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" wire:click.self="$set('showUserSelector', false)">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Pilih Anggota</h3>
                    <button wire:click="$set('showUserSelector', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-1">
                    {{ \Carbon\Carbon::parse($selectedDate)->locale('id')->dayName }}, 
                    {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }} - 
                    Sesi {{ $selectedSession }} ({{ $this->getSessionTime($selectedSession) }})
                </p>
            </div>
            
            <div class="px-6 py-4 max-h-96 overflow-y-auto">
                @if(empty($availableUsers))
                <div class="text-center py-8 text-gray-500">
                    Tidak ada user yang tersedia
                </div>
                @else
                <div class="space-y-2">
                    @foreach($availableUsers as $user)
                    <button wire:click="assignUser({{ $user['id'] }})" 
                            class="w-full flex items-center justify-between p-4 rounded-lg border transition-colors
                                   {{ $user['has_conflict'] ? 'border-red-200 bg-red-50 cursor-not-allowed' : 
                                      ($user['is_not_available'] ? 'border-yellow-200 bg-yellow-50 hover:bg-yellow-100' :
                                       ($user['is_available'] ? 'border-green-200 bg-green-50 hover:bg-green-100' : 
                                        'border-gray-200 bg-gray-50 hover:bg-gray-100')) }}"
                            {{ $user['has_conflict'] ? 'disabled' : '' }}>
                        <div class="flex items-center space-x-3">
                            @if($user['photo'])
                            <img src="{{ asset('storage/' . $user['photo']) }}" 
                                 alt="{{ $user['name'] }}" 
                                 class="w-10 h-10 rounded-full">
                            @else
                            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium">
                                {{ substr($user['name'], 0, 1) }}
                            </div>
                            @endif
                            <div class="text-left">
                                <div class="font-medium text-gray-900">{{ $user['name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $user['nim'] }}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">{{ $user['current_assignments'] }} shifts</span>
                            @if($user['has_conflict'])
                            <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded">Conflict</span>
                            @elseif($user['is_not_available'])
                            <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded">Not Available</span>
                            @elseif($user['is_available'])
                            <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded">Available</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded">No Data</span>
                            @endif
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Loading Overlay --}}
    <div wire:loading wire:target="autoAssign,applyAutoAssignment,loadTemplate" 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm mx-4">
            <div class="flex items-center space-x-4">
                <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <div class="text-lg font-semibold text-gray-900">Processing...</div>
                    <div class="text-sm text-gray-600">Please wait</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Auto-Assignment Preview Modal --}}
    @if($showAutoPreview)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" wire:click.self="cancelAutoPreview">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Preview Auto-Assignment
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Algoritma: Fair Distribution + Availability Weighting</p>
                    </div>
                    <button wire:click="cancelAutoPreview" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Content --}}
            <div class="px-6 py-4 max-h-96 overflow-y-auto">
                @if(!empty($autoPreviewData['statistics']))
                    {{-- Statistics Cards --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-blue-600 font-medium">Total Assignments</div>
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-blue-900">
                                {{ $autoPreviewData['statistics']['total_assignments'] }} / {{ $autoPreviewData['statistics']['total_slots'] }}
                            </div>
                        </div>
                        
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-green-600 font-medium">Coverage Rate</div>
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-green-900">
                                {{ number_format($autoPreviewData['statistics']['coverage_rate'], 1) }}%
                            </div>
                        </div>
                        
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-purple-600 font-medium">Fairness Score</div>
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-purple-900">
                                {{ number_format($autoPreviewData['statistics']['fairness_score'], 1) }}
                            </div>
                            <div class="text-xs text-purple-600 mt-1">
                                @if($autoPreviewData['statistics']['fairness_score'] >= 90)
                                    Excellent
                                @elseif($autoPreviewData['statistics']['fairness_score'] >= 70)
                                    Good
                                @else
                                    Fair
                                @endif
                            </div>
                        </div>
                        
                        <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-orange-600 font-medium">Unique Users</div>
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-orange-900">
                                {{ $autoPreviewData['statistics']['unique_users'] }}
                            </div>
                        </div>
                    </div>
                    
                    {{-- Distribution Details --}}
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Distribution per User
                        </h4>
                        <div class="space-y-3">
                            @foreach($autoPreviewData['statistics']['assignments_per_user'] as $userId => $count)
                                @php $user = \App\Models\User::find($userId); @endphp
                                @if($user)
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        @if($user->photo)
                                        <img src="{{ asset('storage/' . $user->photo) }}" 
                                             alt="{{ $user->name }}" 
                                             class="w-8 h-8 rounded-full">
                                        @else
                                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-medium">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->nim }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-32 bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2.5 rounded-full transition-all" 
                                                 style="width: {{ min(($count / 4) * 100, 100) }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 w-8 text-right">{{ $count }}</span>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        
                        {{-- Summary Stats --}}
                        <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-xs text-gray-500">Min Assignments</div>
                                <div class="text-lg font-bold text-gray-900">{{ $autoPreviewData['statistics']['min_assignments'] }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Avg Assignments</div>
                                <div class="text-lg font-bold text-gray-900">{{ number_format($autoPreviewData['statistics']['avg_assignments'], 1) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Max Assignments</div>
                                <div class="text-lg font-bold text-gray-900">{{ $autoPreviewData['statistics']['max_assignments'] }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Tidak ada data preview</p>
                    </div>
                @endif
            </div>
            
            {{-- Footer Actions --}}
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Note:</span> Menerapkan auto-assignment akan menghapus semua assignment yang ada saat ini.
                </div>
                <div class="flex items-center space-x-3">
                    <button wire:click="cancelAutoPreview" 
                            class="btn btn-secondary"
                            wire:loading.attr="disabled">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </button>
                    <button wire:click="applyAutoAssignment" 
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed">
                        <span wire:loading.remove wire:target="applyAutoAssignment" class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Apply Auto-Assignment
                        </span>
                        <span wire:loading wire:target="applyAutoAssignment" class="flex items-center">
                            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Applying...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Assign Modal --}}
    <div x-data="{ 
        showBulkModal: false, 
        bulkType: '', 
        selectedDate: '', 
        selectedSession: null,
        selectedUserId: null 
    }"
         @open-bulk-assign-modal.window="showBulkModal = true; bulkType = $event.detail.type"
         x-show="showBulkModal"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
         style="display: none;">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" @click.away="showBulkModal = false">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="bulkType === 'allSessions' ? 'Assign to All Sessions' : 'Assign to All Days'"></h3>
                    <button @click="showBulkModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-1" x-show="bulkType === 'allSessions'">
                    Assign same user to all 3 sessions in one day
                </p>
                <p class="text-sm text-gray-600 mt-1" x-show="bulkType === 'allDays'">
                    Assign same user to one session across all 4 days
                </p>
            </div>
            
            <div class="px-6 py-4">
                {{-- Select Date (for allSessions) --}}
                <div x-show="bulkType === 'allSessions'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Day</label>
                    <select x-model="selectedDate" class="input w-full">
                        <option value="">-- Select Day --</option>
                        @php $startDate = \Carbon\Carbon::parse($weekStartDate); @endphp
                        @for($day = 0; $day < 4; $day++)
                            @php
                                $date = $startDate->copy()->addDays($day);
                                $dateStr = $date->format('Y-m-d');
                                $dayName = $date->locale('id')->dayName;
                            @endphp
                            <option value="{{ $dateStr }}">{{ $dayName }}, {{ $date->format('d M Y') }}</option>
                        @endfor
                    </select>
                </div>
                
                {{-- Select Session (for allDays) --}}
                <div x-show="bulkType === 'allDays'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Session</label>
                    <select x-model="selectedSession" class="input w-full">
                        <option value="">-- Select Session --</option>
                        <option value="1">Sesi 1 (08:00 - 12:00)</option>
                        <option value="2">Sesi 2 (13:00 - 17:00)</option>
                        <option value="3">Sesi 3 (17:00 - 21:00)</option>
                    </select>
                </div>
                
                {{-- Select User --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                    <select x-model="selectedUserId" class="input w-full">
                        <option value="">-- Select User --</option>
                        @foreach(\App\Models\User::where('status', 'active')->orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->nim }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end space-x-3">
                <button @click="showBulkModal = false" class="btn btn-secondary">
                    Cancel
                </button>
                <button @click="
                    if (bulkType === 'allSessions' && selectedDate && selectedUserId) {
                        $wire.assignToAllSessions(selectedDate, selectedUserId);
                        showBulkModal = false;
                        selectedDate = '';
                        selectedUserId = null;
                    } else if (bulkType === 'allDays' && selectedSession && selectedUserId) {
                        $wire.assignToAllDays(parseInt(selectedSession), selectedUserId);
                        showBulkModal = false;
                        selectedSession = null;
                        selectedUserId = null;
                    }
                " 
                class="btn btn-primary"
                :disabled="(bulkType === 'allSessions' && (!selectedDate || !selectedUserId)) || (bulkType === 'allDays' && (!selectedSession || !selectedUserId))">
                    Assign
                </button>
            </div>
        </div>
    </div>

</div>
