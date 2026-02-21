<div class="space-y-6">
    <!-- Page Header -->
    <x-layout.page-header 
        title="Riwayat Tukar Jadwal"
        description="Lihat semua riwayat permintaan tukar shift Anda" />

    <!-- Empty State -->
    <x-ui.card>
        <x-layout.empty-state 
            icon="calendar" 
            title="Belum ada riwayat tukar jadwal"
            description="Fitur akan segera tersedia">
            <x-slot:action>
                <x-ui.button 
                    variant="primary" 
                    :href="route('admin.swap.create')" 
                    icon="arrow-path">
                    Buat Permintaan Tukar Shift
                </x-ui.button>
            </x-slot:action>
        </x-layout.empty-state>
    </x-ui.card>
</div>
