<div class="max-w-4xl mx-auto p-4 space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Ketersediaan</h1>
            <p class="text-sm text-gray-500">{{ $this->weekLabel }}</p>
        </div>
        <span @class([
            'px-2.5 py-1 text-xs font-medium rounded-full',
            'bg-green-100 text-green-700' => $status === 'submitted',
            'bg-amber-100 text-amber-700' => $status === 'draft',
        ])>
            {{ $status === 'submitted' ? 'Terkirim' : 'Draft' }}
        </span>
    </div>

    {{-- Week Nav --}}
    <div class="flex items-center justify-center gap-3 py-2">
        <button wire:click="$set('weekOffset', {{ $weekOffset - 1 }})" 
                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-40" 
                @disabled($weekOffset <= -4)>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <select wire:model.live="weekOffset" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            @for($i = 0; $i <= 4; $i++)
                <option value="{{ $i }}">{{ $i === 0 ? 'Minggu Ini' : ($i === 1 ? 'Minggu Depan' : "$i Minggu Lagi") }}</option>
            @endfor
        </select>
        <button wire:click="$set('weekOffset', {{ $weekOffset + 1 }})" 
                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-40" 
                @disabled($weekOffset >= 4)>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>

    {{-- Grid Section with Alpine - wire:ignore to prevent re-render conflicts --}}
    <div x-data="availabilityGrid(@js($availability), @js($this->canEdit))"
         x-on:availability-reset.window="resetGrid($event.detail)"
         wire:ignore.self>
        
        {{-- Stats --}}
        <div class="flex items-center justify-center gap-6 py-3 text-sm mb-4">
            <div class="text-center">
                <span class="text-2xl font-bold text-blue-600" x-text="totalSessions"></span>
                <p class="text-gray-500">Sesi</p>
            </div>
            <div class="text-center">
                <span class="text-2xl font-bold text-green-600" x-text="totalSessions * 4"></span>
                <p class="text-gray-500">Jam</p>
            </div>
            <div class="text-center">
                <span class="text-2xl font-bold text-purple-600" x-text="Math.round((totalSessions / 12) * 100) + '%'"></span>
                <p class="text-gray-500">Coverage</p>
            </div>
        </div>

        {{-- Grid --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="py-3 px-3 text-left font-semibold text-gray-700">Hari</th>
                            @foreach($this->sessions as $key => $time)
                                <th class="py-3 px-2 text-center font-semibold text-gray-700">
                                    <div>Sesi {{ $key }}</div>
                                    <div class="text-xs font-normal text-gray-400">{{ $time }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($this->days as $dayKey => $dayLabel)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-4 px-3">
                                    @if($this->canEdit)
                                        <button type="button" @click="toggleDay('{{ $dayKey }}')" 
                                                class="font-medium text-gray-900 hover:text-blue-600 transition-colors">
                                            {{ $dayLabel }}
                                        </button>
                                    @else
                                        <span class="font-medium text-gray-900">{{ $dayLabel }}</span>
                                    @endif
                                </td>
                                @foreach(array_keys($this->sessions) as $sessionKey)
                                    <td class="py-4 px-2 text-center">
                                        <button type="button"
                                                @if($this->canEdit) @click="toggle('{{ $dayKey }}', '{{ $sessionKey }}')" @endif
                                                class="w-10 h-10 rounded-xl border-2 inline-flex items-center justify-center transition-all duration-150 
                                                       {{ $this->canEdit ? 'cursor-pointer active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1' : 'cursor-default' }}"
                                                :class="isSelected('{{ $dayKey }}', '{{ $sessionKey }}') 
                                                    ? 'bg-green-500 border-green-500 text-white hover:bg-green-600' 
                                                    : 'bg-gray-100 border-gray-300 {{ $this->canEdit ? 'hover:border-blue-400 hover:bg-blue-50' : '' }}'">
                                            <svg x-show="isSelected('{{ $dayKey }}', '{{ $sessionKey }}')" 
                                                 x-cloak
                                                 class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        @if($this->canEdit)
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4">
                <button type="button" @click="save('draft')" 
                        :disabled="saving"
                        class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50">
                    <span x-show="!saving || saveType !== 'draft'">Simpan Draft</span>
                    <span x-show="saving && saveType === 'draft'" x-cloak class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
                <button type="button" @click="save('submit')"
                        :disabled="saving || totalSessions === 0"
                        class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                    <span x-show="!saving || saveType !== 'submit'">Kirim Ketersediaan</span>
                    <span x-show="saving && saveType === 'submit'" x-cloak class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mengirim...
                    </span>
                </button>
            </div>
        @else
            <p class="text-center text-sm text-gray-500 py-3 bg-gray-50 rounded-lg mt-4">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Ketersediaan sudah terkirim untuk minggu ini
            </p>
        @endif
    </div>
</div>

@script
<script>
Alpine.data('availabilityGrid', (initialData, canEdit) => ({
    grid: JSON.parse(JSON.stringify(initialData)),
    canEdit: canEdit,
    saving: false,
    saveType: null,
    
    get totalSessions() {
        let count = 0;
        for (const day in this.grid) {
            for (const session in this.grid[day]) {
                if (this.grid[day][session]) count++;
            }
        }
        return count;
    },
    
    isSelected(day, session) {
        return this.grid[day] && this.grid[day][session] === true;
    },
    
    toggle(day, session) {
        if (!this.canEdit) return;
        if (!this.grid[day]) this.grid[day] = {};
        this.grid[day][session] = !this.grid[day][session];
    },
    
    toggleDay(day) {
        if (!this.canEdit) return;
        const sessions = ['1', '2', '3'];
        const allSelected = sessions.every(s => this.grid[day] && this.grid[day][s]);
        sessions.forEach(s => {
            if (!this.grid[day]) this.grid[day] = {};
            this.grid[day][s] = !allSelected;
        });
    },
    
    async save(type) {
        if (this.saving) return;
        this.saving = true;
        this.saveType = type;
        
        try {
            if (type === 'draft') {
                await $wire.saveWithData(this.grid, 'draft');
            } else {
                await $wire.saveWithData(this.grid, 'submitted');
            }
        } finally {
            this.saving = false;
            this.saveType = null;
        }
    },
    
    resetGrid(newData) {
        this.grid = JSON.parse(JSON.stringify(newData));
    }
}));
</script>
@endscript
