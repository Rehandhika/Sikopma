<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Statistik Jadwal
    </h2>

    {{-- Overview Cards --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        {{-- Total Assignments --}}
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Assignments</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">{{ $totalAssignments }}</p>
                    <p class="text-xs text-blue-700 mt-1">dari {{ $totalSlots }} slot</p>
                </div>
                <div class="bg-blue-500 rounded-full p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Coverage Rate --}}
        <div class="bg-gradient-to-br from-{{ $this->getCoverageColor() }}-50 to-{{ $this->getCoverageColor() }}-100 rounded-lg p-4 border border-{{ $this->getCoverageColor() }}-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-{{ $this->getCoverageColor() }}-600 uppercase tracking-wide">Coverage</p>
                    <p class="text-2xl font-bold text-{{ $this->getCoverageColor() }}-900 mt-1">{{ number_format($coverageRate, 1) }}%</p>
                    <p class="text-xs text-{{ $this->getCoverageColor() }}-700 mt-1">{{ $unassignedSlots }} slot kosong</p>
                </div>
                <div class="bg-{{ $this->getCoverageColor() }}-500 rounded-full p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Fairness Score --}}
        <div class="bg-gradient-to-br from-{{ $this->getFairnessColor() }}-50 to-{{ $this->getFairnessColor() }}-100 rounded-lg p-4 border border-{{ $this->getFairnessColor() }}-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-{{ $this->getFairnessColor() }}-600 uppercase tracking-wide">Fairness Score</p>
                    <p class="text-2xl font-bold text-{{ $this->getFairnessColor() }}-900 mt-1">{{ number_format($fairnessScore, 1) }}</p>
                    <p class="text-xs text-{{ $this->getFairnessColor() }}-700 mt-1">
                        @if($fairnessScore >= 80)
                            Sangat adil
                        @elseif($fairnessScore >= 60)
                            Cukup adil
                        @else
                            Perlu perbaikan
                        @endif
                    </p>
                </div>
                <div class="bg-{{ $this->getFairnessColor() }}-500 rounded-full p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Conflicts --}}
        <div class="bg-gradient-to-br from-{{ $this->getTotalConflicts() > 0 ? 'red' : 'gray' }}-50 to-{{ $this->getTotalConflicts() > 0 ? 'red' : 'gray' }}-100 rounded-lg p-4 border border-{{ $this->getTotalConflicts() > 0 ? 'red' : 'gray' }}-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-{{ $this->getTotalConflicts() > 0 ? 'red' : 'gray' }}-600 uppercase tracking-wide">Conflicts</p>
                    <p class="text-2xl font-bold text-{{ $this->getTotalConflicts() > 0 ? 'red' : 'gray' }}-900 mt-1">{{ $this->getTotalConflicts() }}</p>
                    <p class="text-xs text-{{ $this->getTotalConflicts() > 0 ? 'red' : 'gray' }}-700 mt-1">
                        @if($this->getTotalConflicts() === 0)
                            Tidak ada konflik
                        @else
                            Perlu perhatian
                        @endif
                    </p>
                </div>
                <div class="bg-{{ $this->getTotalConflicts() > 0 ? 'red' : 'gray' }}-500 rounded-full p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Assignments per User (Bar Chart) --}}
    @if(count($assignmentsPerUser) > 0)
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Distribusi per Anggota
            </h3>
            
            <div class="space-y-3">
                @foreach($assignmentsPerUser as $user)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-700 truncate max-w-[150px]" title="{{ $user['user_name'] }}">
                                {{ $user['user_name'] }}
                            </span>
                            <span class="text-xs font-bold text-gray-900">
                                {{ $user['count'] }} shift{{ $user['count'] > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                            <div class="{{ $this->getBarColor($user['count']) }} h-2.5 rounded-full transition-all duration-500"
                                 style="width: {{ $this->getBarWidth($user['count']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Legend --}}
            <div class="mt-4 flex flex-wrap gap-3 text-xs">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                    <span class="text-gray-600">Seimbang</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded mr-1"></div>
                    <span class="text-gray-600">Agak banyak</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded mr-1"></div>
                    <span class="text-gray-600">Terlalu banyak</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-300 rounded mr-1"></div>
                    <span class="text-gray-600">Kurang</span>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-sm text-gray-600 mt-2">Belum ada assignment</p>
            <p class="text-xs text-gray-500 mt-1">Mulai assign anggota untuk melihat statistik</p>
        </div>
    @endif

    {{-- Conflict Details --}}
    @if($this->getTotalConflicts() > 0)
        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Detail Konflik
            </h3>
            
            <div class="space-y-2">
                {{-- Critical Conflicts --}}
                @if($this->getConflictCount('critical') > 0)
                    @foreach($conflicts['critical'] as $conflict)
                        <div class="flex items-start p-3 bg-red-50 border border-red-200 rounded-lg">
                            <svg class="w-5 h-5 text-red-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-red-900">CRITICAL</p>
                                <p class="text-xs text-red-800 mt-1">{{ $conflict['message'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
                
                {{-- Warning Conflicts --}}
                @if($this->getConflictCount('warning') > 0)
                    @foreach($conflicts['warning'] as $conflict)
                        <div class="flex items-start p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-yellow-900">WARNING</p>
                                <p class="text-xs text-yellow-800 mt-1">{{ $conflict['message'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
                
                {{-- Info Conflicts --}}
                @if($this->getConflictCount('info') > 0)
                    @foreach($conflicts['info'] as $conflict)
                        <div class="flex items-start p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-blue-900">INFO</p>
                                <p class="text-xs text-blue-800 mt-1">{{ $conflict['message'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif

    {{-- Progress Bar --}}
    <div class="mt-6 pt-4 border-t border-gray-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-700">Progress Pengisian</span>
            <span class="text-xs font-bold text-gray-900">{{ $totalAssignments }}/{{ $totalSlots }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                 style="width: {{ $coverageRate }}%">
                @if($coverageRate > 15)
                    <span class="text-[10px] font-bold text-white">{{ number_format($coverageRate, 0) }}%</span>
                @endif
            </div>
        </div>
    </div>
</div>
