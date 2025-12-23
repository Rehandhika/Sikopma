<div class="space-y-6">
    <x-layout.page-header 
        title="Pengaturan Toko"
        description="Kelola status operasional dan pengaturan koperasi"
    />

    {{-- Current Status Section --}}
    <x-ui.card>
        <x-layout.form-section 
            title="Status Toko Saat Ini"
            description="Informasi status operasional koperasi secara real-time"
        >
            @if($statusLoaded)
                <div class="space-y-4">
                    {{-- Status Badge --}}
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            @if($currentStatus['is_open'])
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                <span class="text-lg font-semibold text-green-600">BUKA</span>
                            @else
                                <span class="flex h-3 w-3 relative">
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                                <span class="text-lg font-semibold text-red-600">TUTUP</span>
                            @endif
                        </div>

                        {{-- Mode Indicator --}}
                        <div class="flex items-center gap-2">
                            @if($currentStatus['mode'] === 'manual')
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">
                                    Mode Manual
                                </span>
                            @elseif($currentStatus['mode'] === 'temporary_close')
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-700">
                                    Tutup Sementara
                                </span>
                            @elseif($currentStatus['mode'] === 'override')
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                    Override Aktif
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                    Mode Otomatis
                                </span>
                            @endif
                        </div>

                        {{-- Refresh Button --}}
                        <button 
                            wire:click="refreshStatus" 
                            class="ml-auto text-gray-500 hover:text-gray-700 transition-colors"
                            title="Refresh status"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>

                    {{-- Status Reason --}}
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Alasan:</span> {{ $currentStatus['reason'] }}
                    </div>

                    {{-- Attendees --}}
                    @if($currentStatus['is_open'] && !empty($currentStatus['attendees']))
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Pengurus yang bertugas:</span>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @foreach($currentStatus['attendees'] as $attendee)
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-green-50 text-green-700 border border-green-200">
                                        {{ $attendee }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Next Open Time --}}
                    @if(!$currentStatus['is_open'] && $currentStatus['next_open_time'])
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Buka berikutnya:</span> {{ $currentStatus['next_open_time'] }}
                        </div>
                    @endif

                    {{-- Last Update --}}
                    <div class="text-xs text-gray-500">
                        Terakhir diperbarui: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm:ss') }}
                    </div>
                </div>
            @else
                <div class="text-center py-4 text-gray-500">
                    Memuat status...
                </div>
            @endif
        </x-layout.form-section>
    </x-ui.card>

    {{-- Quick Actions Section --}}
    <x-ui.card>
        <x-layout.form-section 
            title="Aksi Cepat"
            description="Tutup koperasi sementara dengan durasi yang telah ditentukan"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Close for 1 hour --}}
                <button 
                    wire:click="closeFor(1)"
                    wire:confirm="Tutup koperasi selama 1 jam?"
                    class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all group"
                >
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-orange-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700">Tutup 1 Jam</span>
                </button>

                {{-- Close for 2 hours --}}
                <button 
                    wire:click="closeFor(2)"
                    wire:confirm="Tutup koperasi selama 2 jam?"
                    class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all group"
                >
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-orange-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700">Tutup 2 Jam</span>
                </button>

                {{-- Close for 4 hours --}}
                <button 
                    wire:click="closeFor(4)"
                    wire:confirm="Tutup koperasi selama 4 jam?"
                    class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all group"
                >
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-orange-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700">Tutup 4 Jam</span>
                </button>

                {{-- Close until tomorrow --}}
                <button 
                    wire:click="closeUntilTomorrow"
                    wire:confirm="Tutup koperasi hingga besok pagi (07:30)?"
                    class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-red-500 hover:bg-red-50 transition-all group"
                >
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-red-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-red-700">Tutup Hingga Besok</span>
                </button>
            </div>

            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex gap-2">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-1">Informasi Tutup Sementara</p>
                        <p>Setelah durasi berakhir, status koperasi akan kembali ke mode otomatis dan mengikuti jadwal operasional serta kehadiran pengurus.</p>
                    </div>
                </div>
            </div>
        </x-layout.form-section>
    </x-ui.card>

    {{-- Manual Open Override Section --}}
    <x-ui.card>
        <x-layout.form-section 
            title="Override Buka"
            description="Izinkan koperasi buka di luar jadwal operasional jika ada pengurus yang bertugas"
        >
            <div class="space-y-4">
                {{-- Current Override Status --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            @if($currentStatus['mode'] === 'override')
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">
                                @if($currentStatus['mode'] === 'override')
                                    Override Aktif
                                @else
                                    Override Nonaktif
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">
                                @if($currentStatus['mode'] === 'override')
                                    Koperasi dapat buka di luar jadwal jika ada pengurus
                                @else
                                    Koperasi hanya buka sesuai jadwal operasional
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($currentStatus['mode'] === 'override')
                        <button 
                            wire:click="disableOpenOverride"
                            wire:confirm="Nonaktifkan override buka?"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Nonaktifkan
                        </button>
                    @else
                        <button 
                            wire:click="enableOpenOverride"
                            wire:confirm="Aktifkan override buka? Koperasi dapat buka di luar jadwal jika ada pengurus yang bertugas."
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Aktifkan
                        </button>
                    @endif
                </div>

                {{-- Info Box --}}
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Tentang Override Buka</p>
                            <p>Fitur ini memungkinkan koperasi buka di luar hari/jam operasional normal (Senin-Kamis, 07:30-16:00) jika ada pengurus yang melakukan check-in. Status tetap mengikuti kehadiran pengurus secara otomatis.</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.form-section>
    </x-ui.card>

    {{-- Manual Mode Section --}}
    <x-ui.card>
        <x-layout.form-section 
            title="Mode Manual"
            description="Kontrol penuh terhadap status koperasi, mengabaikan jadwal dan kehadiran pengurus"
        >
            <div class="space-y-4">
                {{-- Current Manual Mode Status --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            @if($currentStatus['mode'] === 'manual')
                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">
                                @if($currentStatus['mode'] === 'manual')
                                    Mode Manual Aktif
                                @else
                                    Mode Otomatis
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">
                                @if($currentStatus['mode'] === 'manual')
                                    Anda memiliki kontrol penuh terhadap status
                                @else
                                    Status mengikuti jadwal dan kehadiran pengurus
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($currentStatus['mode'] === 'manual')
                        <button 
                            wire:click="disableManualMode"
                            wire:confirm="Nonaktifkan mode manual dan kembali ke mode otomatis?"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Kembali ke Auto
                        </button>
                    @else
                        <button 
                            wire:click="enableManualMode"
                            wire:confirm="Aktifkan mode manual? Anda akan memiliki kontrol penuh terhadap status koperasi."
                            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors"
                        >
                            Aktifkan Manual
                        </button>
                    @endif
                </div>

                {{-- Manual Control Buttons (only shown when manual mode is active) --}}
                @if($currentStatus['mode'] === 'manual')
                    <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <p class="text-sm font-medium text-purple-900 mb-3">Kontrol Status Manual</p>
                        <div class="grid grid-cols-2 gap-3">
                            <button 
                                wire:click="setManualStatus(true)"
                                wire:confirm="Buka koperasi secara manual?"
                                class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
                                @if($currentStatus['is_open']) disabled @endif
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                Buka Koperasi
                            </button>
                            <button 
                                wire:click="setManualStatus(false)"
                                wire:confirm="Tutup koperasi secara manual?"
                                class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                                @if(!$currentStatus['is_open']) disabled @endif
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Tutup Koperasi
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Warning Box --}}
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium mb-1">Perhatian</p>
                            <p>Mode manual memberikan kontrol penuh kepada admin. Status tidak akan berubah otomatis berdasarkan kehadiran pengurus atau jadwal operasional. Gunakan dengan bijak.</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.form-section>
    </x-ui.card>

    {{-- Reset to Auto Section --}}
    <x-ui.card>
        <x-layout.form-section 
            title="Reset ke Mode Otomatis"
            description="Hapus semua pengaturan manual dan kembali ke mode otomatis"
        >
            <div class="space-y-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-700 mb-4">
                        Tombol ini akan menghapus semua pengaturan manual yang aktif (mode manual, override buka, tutup sementara) dan mengembalikan sistem ke mode otomatis. Status akan langsung dihitung ulang berdasarkan jadwal operasional dan kehadiran pengurus.
                    </p>
                    <button 
                        wire:click="resetToAuto"
                        wire:confirm="Reset semua pengaturan manual dan kembali ke mode otomatis?"
                        class="w-full sm:w-auto px-6 py-3 text-sm font-medium text-white bg-gray-700 rounded-lg hover:bg-gray-800 transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset ke Mode Otomatis
                    </button>
                </div>

                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Mode Otomatis</p>
                            <p>Dalam mode otomatis, status koperasi akan:</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Buka otomatis saat ada pengurus check-in pada hari dan jam operasional</li>
                                <li>Tutup otomatis saat pengurus terakhir check-out</li>
                                <li>Tutup otomatis di luar hari operasional (Jumat-Minggu)</li>
                                <li>Tutup otomatis di luar jam operasional (sebelum 07:30 atau setelah 16:00)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.form-section>
    </x-ui.card>

    {{-- Operating Hours Configuration Section --}}
    <x-ui.card>
        <x-layout.form-section 
            title="Jam Operasional"
            description="Atur jam buka dan tutup koperasi untuk setiap hari"
        >
            <form wire:submit.prevent="saveOperatingHours" class="space-y-6">
                {{-- Operating Days (Monday - Thursday) --}}
                <div class="space-y-4">
                    @php
                        $days = [
                            'monday' => 'Senin',
                            'tuesday' => 'Selasa',
                            'wednesday' => 'Rabu',
                            'thursday' => 'Kamis',
                        ];
                    @endphp

                    @foreach($days as $dayKey => $dayName)
                        <div class="p-4 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                {{-- Day Name --}}
                                <div class="flex items-center gap-3 sm:w-32">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="operatingHours.{{ $dayKey }}.is_open"
                                        id="day_{{ $dayKey }}"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                    <label for="day_{{ $dayKey }}" class="font-medium text-gray-900 cursor-pointer">
                                        {{ $dayName }}
                                    </label>
                                </div>

                                {{-- Time Inputs --}}
                                @if($operatingHours[$dayKey]['is_open'] ?? false)
                                    <div class="flex items-center gap-4 flex-1">
                                        <div class="flex-1">
                                            <label for="open_{{ $dayKey }}" class="block text-xs font-medium text-gray-700 mb-1">
                                                Jam Buka
                                            </label>
                                            <input 
                                                type="time" 
                                                wire:model="operatingHours.{{ $dayKey }}.open"
                                                id="open_{{ $dayKey }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                required
                                            >
                                        </div>

                                        <div class="flex items-center pt-6">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </div>

                                        <div class="flex-1">
                                            <label for="close_{{ $dayKey }}" class="block text-xs font-medium text-gray-700 mb-1">
                                                Jam Tutup
                                            </label>
                                            <input 
                                                type="time" 
                                                wire:model="operatingHours.{{ $dayKey }}.close"
                                                id="close_{{ $dayKey }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                required
                                            >
                                        </div>
                                    </div>
                                @else
                                    <div class="flex-1 text-sm text-gray-500 italic">
                                        Tutup
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Non-Operating Days (Friday - Sunday) --}}
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-gray-700">
                            <p class="font-medium mb-1">Hari Libur</p>
                            <p>Jumat, Sabtu, dan Minggu adalah hari libur. Koperasi tidak beroperasi pada hari-hari tersebut kecuali menggunakan fitur Override Buka.</p>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        <svg class="w-4 h-4 inline-block mr-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Perubahan akan mempengaruhi perhitungan status otomatis
                    </div>
                    <button 
                        type="submit"
                        class="px-6 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Jam Operasional
                    </button>
                </div>
            </form>
        </x-layout.form-section>
    </x-ui.card>

    {{-- Contact Information Section --}}
    <x-ui.card>
        <x-layout.form-section 
            title="Informasi Kontak"
            description="Kelola informasi kontak koperasi yang ditampilkan di halaman publik"
        >
            <form wire:submit.prevent="saveContactInfo" class="space-y-6">
                {{-- Phone Number --}}
                <div>
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model="contactPhone"
                            id="contact_phone"
                            placeholder="Contoh: 0812-3456-7890 atau +62812-3456-7890"
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Format: 08xx-xxxx-xxxx atau +628xx-xxxx-xxxx</p>
                </div>

                {{-- Email --}}
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            wire:model="contactEmail"
                            id="contact_email"
                            placeholder="Contoh: kopma@example.com"
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                </div>

                {{-- WhatsApp --}}
                <div>
                    <label for="contact_whatsapp" class="block text-sm font-medium text-gray-700 mb-2">
                        WhatsApp
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model="contactWhatsapp"
                            id="contact_whatsapp"
                            placeholder="Contoh: 0812-3456-7890 atau +62812-3456-7890"
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Format: 08xx-xxxx-xxxx atau +628xx-xxxx-xxxx</p>
                </div>

                {{-- Address --}}
                <div>
                    <label for="contact_address" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat
                    </label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <textarea 
                            wire:model="contactAddress"
                            id="contact_address"
                            rows="3"
                            placeholder="Masukkan alamat lengkap koperasi"
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>
                </div>

                {{-- About Text --}}
                <div>
                    <label for="about_text" class="block text-sm font-medium text-gray-700 mb-2">
                        Tentang Koperasi
                    </label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <textarea 
                            wire:model="aboutText"
                            id="about_text"
                            rows="5"
                            placeholder="Masukkan deskripsi tentang koperasi, visi, misi, atau informasi lainnya"
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Informasi ini akan ditampilkan di halaman "Tentang" pada website publik</p>
                </div>

                {{-- Info Box --}}
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Informasi Publik</p>
                            <p>Informasi kontak yang Anda masukkan akan ditampilkan di halaman publik website koperasi. Pastikan informasi yang dimasukkan akurat dan up-to-date.</p>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="px-6 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Informasi Kontak
                    </button>
                </div>
            </form>
        </x-layout.form-section>
    </x-ui.card>
</div>
