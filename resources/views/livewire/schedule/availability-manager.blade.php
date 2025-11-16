<div>
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Ketersediaan</h1>
                <p class="text-gray-600 mt-1">Atur jadwal ketersediaan Anda untuk minggu ini</p>
            </div>
            <div class="flex items-center space-x-2">
                @if($status === 'submitted')
                    <div class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                        <i class="fas fa-check-circle mr-1"></i>
                        Terkirim
                    </div>
                @elseif($status === 'draft')
                    <div class="bg-yellow-50 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">
                        <i class="fas fa-edit mr-1"></i>
                        Draft
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Week Selection -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-calendar-week mr-2 text-blue-600"></i>
                Pilih Minggu
            </h2>
            @if(!$isCurrentWeek)
                <button 
                    wire:click="$set('selectedWeekOffset', 0)"
                    class="text-sm text-blue-600 hover:text-blue-700"
                >
                    <i class="fas fa-calendar-day mr-1"></i>
                    Minggu Ini
                </button>
            @endif
        </div>
        
        <div class="flex items-center space-x-4 mt-4">
            <button 
                wire:click="$set('selectedWeekOffset', $selectedWeekOffset - 1)"
                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                {{ $selectedWeekOffset <= -4 ? 'disabled' : '' }}
            >
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="flex-1 text-center">
                <select 
                    wire:model.live="selectedWeekOffset"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    {{ !$canEdit ? 'disabled' : '' }}
                >
                    <option value="0">Minggu Ini</option>
                    <option value="1">Minggu Depan</option>
                    <option value="2">2 Minggu Depan</option>
                    <option value="3">3 Minggu Depan</option>
                    <option value="4">4 Minggu Depan</option>
                </select>
                <p class="text-sm text-gray-600 mt-1">{{ $weekRange }}</p>
            </div>
            
            <button 
                wire:click="$set('selectedWeekOffset', $selectedWeekOffset + 1)"
                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                {{ $selectedWeekOffset >= 4 ? 'disabled' : '' }}
            >
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-calendar-check text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Sesi</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalSessions }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-clock text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Jam</p>
                    <p class="text-xl font-bold text-green-600">{{ $totalHours }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-calendar-day text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Hari Tersedia</p>
                    <p class="text-xl font-bold text-purple-900">{{ $availableDays }}/7</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-percentage text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Coverage</p>
                    <p class="text-xl font-bold text-yellow-900">{{ round(($totalSessions / 21) * 100) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    @if($canEdit)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                Aksi Cepat
            </h3>
            <div class="flex flex-wrap gap-3">
                <button 
                    wire:click="selectAll"
                    type="button"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                >
                    <i class="fas fa-check-double mr-2"></i>
                    Pilih Semua
                </button>
                <button 
                    wire:click="clearAll"
                    type="button"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    <i class="fas fa-times-circle mr-2"></i>
                    Hapus Semua
                </button>
                
                @foreach($sessions as $sessionKey => $sessionName)
                    <button 
                        wire:click="setSessionAvailability({{ $sessionKey }}, true)"
                        type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <i class="fas fa-clock mr-2"></i>
                        {{ $sessionName }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

        <!-- Availability Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
            Ketersediaan per Hari & Sesi
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-medium text-gray-700">Hari</th>
                        @foreach($sessions as $sessionKey => $sessionName)
                            <th class="text-center py-3 px-4 font-medium text-gray-700">
                                <div class="flex flex-col items-center">
                                    <span>Sesi {{ $sessionKey }}</span>
                                    <span class="text-xs text-gray-500">{{ getSessionTime($sessionKey) }}</span>
                                </div>
                            </th>
                        @endforeach
                        <th class="text-center py-3 px-4 font-medium text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $dayKey => $dayName)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4 font-medium text-gray-900">
                                {{ $dayName }}
                            </td>
                            
                            @foreach($sessions as $sessionKey => $sessionName)
                                <td class="py-4 px-4 text-center">
                                    @if($canEdit)
                                        <button 
                                            wire:click="toggleAvailability('{{ $dayKey }}', {{ $sessionKey }})"
                                            type="button"
                                            class="w-6 h-6 rounded-full border-2 transition-colors
                                                {{ $availability[$dayKey][$sessionKey] 
                                                    ? 'bg-green-500 border-green-500 hover:bg-green-600' 
                                                    : 'bg-white border-gray-300 hover:border-gray-400' }}"
                                        >
                                            @if($availability[$dayKey][$sessionKey])
                                                <i class="fas fa-check text-white text-xs"></i>
                                            @endif
                                        </button>
                                    @else
                                        <div class="w-6 h-6 rounded-full border-2 mx-auto
                                            {{ $availability[$dayKey][$sessionKey] 
                                                ? 'bg-green-500 border-green-500' 
                                                : 'bg-gray-100 border-gray-300' }}">
                                            @if($availability[$dayKey][$sessionKey])
                                                <i class="fas fa-check text-white text-xs flex items-center justify-center h-full"></i>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            
                            <td class="py-4 px-4 text-center">
                                @if($canEdit)
                                    <div class="flex justify-center space-x-2">
                                        <button 
                                            wire:click="setDayAvailability('{{ $dayKey }}', true)"
                                            type="button"
                                            class="text-green-600 hover:text-green-700"
                                            title="Pilih semua sesi"
                                        >
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button 
                                            wire:click="setDayAvailability('{{ $dayKey }}', false)"
                                            type="button"
                                            class="text-red-600 hover:text-red-700"
                                            title="Hapus semua sesi"
                                        >
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>
            Catatan Tambahan
        </h3>
        <textarea 
            wire:model="notes"
            placeholder="Tambahkan catatan atau preferensi khusus..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
            rows="3"
            {{ !$canEdit ? 'disabled' : '' }}
        ></textarea>
        <div class="mt-2 text-right">
            <span class="text-sm text-gray-500">{{ strlen($notes) }}/500 karakter</span>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                @if($status === 'submitted')
                    <p class="text-sm text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>
                        Ketersediaan Anda telah dikirim untuk minggu ini
                    </p>
                @elseif($totalSessions === 0)
                    <p class="text-sm text-yellow-600">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Pilih minimal satu sesi untuk menyimpan ketersediaan
                    </p>
                @endif
            </div>
            
            @if($canEdit)
                <div class="flex items-center space-x-3">
                    <button 
                        wire:click="saveAsDraft"
                        type="button"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>
                            <i class="fas fa-save mr-2"></i>
                            Simpan Draft
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Menyimpan...
                        </span>
                    </button>
                    
                    <button 
                        wire:click="submitAvailability"
                        type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        {{ $totalSessions === 0 ? 'disabled' : '' }}
                    >
                        <span wire:loading.remove>
                            <i class="fas fa-paper-plane mr-2"></i>
                            Kirim Ketersediaan
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Mengirim...
                        </span>
                    </button>
                </div>
            @else
                <div class="text-sm text-gray-500">
                    <i class="fas fa-lock mr-1"></i>
                    Ketersediaan untuk minggu ini tidak dapat diubah
                </div>
            @endif
        </div>
    </div>
</div>
