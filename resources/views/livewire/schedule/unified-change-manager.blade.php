<div class="space-y-6 pb-10">
    {{-- Header --}}
    <x-layout.page-header 
        title="Tukar & Pindah Jadwal" 
        subtitle="Kelola permintaan tukar shift dan perubahan jadwal kerja Anda"
    >
        <x-slot:actions>
            @if($activeType === 'swap')
                <x-ui.button 
                    variant="primary" 
                    :href="route('admin.swap.create')" 
                    icon="plus">
                    Buat Permintaan Tukar
                </x-ui.button>
            @else
                <x-ui.button 
                    wire:click="$dispatch('openChangeForm')" 
                    variant="primary" 
                    icon="plus">
                    Ajukan Pindah/Batal
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-layout.page-header>

    <div class="max-w-6xl mx-auto w-full space-y-6">
        {{-- Unified Tabs --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-1 flex">
            <button 
                wire:click="setType('change')" 
                @class([
                    'flex-1 py-3 text-sm font-bold rounded-xl transition-all duration-200 flex items-center justify-center', 
                    'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' => $activeType === 'change', 
                    'text-gray-500 hover:text-gray-700 hover:bg-gray-50' => $activeType !== 'change'
                ])>
                <x-ui.icon name="calendar" class="w-5 h-5 mr-2 {{ $activeType === 'change' ? 'text-blue-600' : 'text-gray-400' }}" />
                Pindah / Batal Jadwal
            </button>
            <button 
                wire:click="setType('swap')" 
                @class([
                    'flex-1 py-3 text-sm font-bold rounded-xl transition-all duration-200 flex items-center justify-center', 
                    'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' => $activeType === 'swap', 
                    'text-gray-500 hover:text-gray-700 hover:bg-gray-50' => $activeType !== 'swap'
                ])>
                <x-ui.icon name="user-group" class="w-5 h-5 mr-2 {{ $activeType === 'swap' ? 'text-blue-600' : 'text-gray-400' }}" />
                Tukar Shift (Swap)
            </button>
        </div>

        {{-- Content Area --}}
        <div>
            @if($activeType === 'swap')
                @livewire('swap.swap-request-list')
            @else
                @livewire('schedule.schedule-change-manager')
            @endif
        </div>
    </div>
</div>
