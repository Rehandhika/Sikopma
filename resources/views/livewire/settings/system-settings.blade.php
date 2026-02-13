<div class="space-y-6">
    <x-layout.page-header 
        title="Pengaturan Sistem"
        description="Kelola pengaturan sistem dan mode maintenance"
    />

    {{-- System Info --}}
    <x-ui.card>
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><span class="text-gray-500">PHP:</span> <span class="font-medium">{{ $systemInfo['php_version'] }}</span></div>
                <div><span class="text-gray-500">Laravel:</span> <span class="font-medium">{{ $systemInfo['laravel_version'] }}</span></div>
                <div><span class="text-gray-500">Zona Waktu:</span> <span class="font-medium">{{ $systemInfo['timezone'] }}</span></div>
                <div>
                    <span class="text-gray-500">Waktu:</span> 
                    <span class="font-medium">{{ $systemInfo['server_time'] }}</span>
                </div>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <form wire:submit="save" class="space-y-6">
            {{-- Maintenance Mode --}}
            <x-layout.form-section 
                title="Mode Maintenance"
                description="Aktifkan untuk mencegah akses pengguna saat pemeliharaan"
            >
                {{-- Warning Banner when Maintenance Active --}}
                @if($maintenance_mode)
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-red-800">Mode Maintenance Aktif</p>
                                <p class="text-sm text-red-700">Pengguna biasa tidak dapat mengakses sistem saat ini.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    {{-- Toggle Switch --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full {{ $maintenance_mode ? 'bg-red-100' : 'bg-gray-200' }} flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $maintenance_mode ? 'text-red-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium {{ $maintenance_mode ? 'text-red-700' : 'text-gray-700' }}">
                                    {{ $maintenance_mode ? 'Maintenance Aktif' : 'Sistem Normal' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $maintenance_mode ? 'Pengguna tidak dapat mengakses sistem' : 'Sistem dapat diakses normal' }}
                                </p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            wire:click="toggleMaintenance" 
                            wire:confirm="{{ $maintenance_mode ? 'Nonaktifkan mode maintenance?' : 'Aktifkan mode maintenance? Pengguna biasa tidak akan bisa mengakses sistem.' }}"
                            class="px-4 py-2 text-sm font-medium rounded-lg {{ $maintenance_mode ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }}"
                        >
                            {{ $maintenance_mode ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </div>

                    {{-- Custom Message Input --}}
                    <div>
                        <label for="maintenance_message" class="block text-sm font-medium text-gray-700 mb-1">
                            Pesan Maintenance
                        </label>
                        <textarea 
                            id="maintenance_message"
                            wire:model="maintenance_message" 
                            rows="3"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Contoh: Sistem sedang dalam pemeliharaan untuk peningkatan performa..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Pesan ini akan ditampilkan kepada pengguna saat maintenance aktif.</p>
                    </div>

                    {{-- Estimated End Time Input --}}
                    <div>
                        <label for="maintenance_estimated_end" class="block text-sm font-medium text-gray-700 mb-1">
                            Estimasi Waktu Selesai (Opsional)
                        </label>
                        <input 
                            type="datetime-local" 
                            id="maintenance_estimated_end"
                            wire:model="maintenance_estimated_end" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <p class="mt-1 text-xs text-gray-500">Waktu perkiraan maintenance selesai. Akan ditampilkan kepada pengguna.</p>
                    </div>
                </div>
            </x-layout.form-section>

            {{-- Actions --}}
            <div class="flex justify-between pt-4 border-t border-gray-200">
                <button type="button" wire:click="clearCache" wire:confirm="Bersihkan cache?" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Bersihkan Cache
                </button>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </x-ui.card>
</div>
