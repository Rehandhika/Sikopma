<div class="space-y-6">
    <x-layout.page-header 
        title="Pengaturan Sistem"
        description="Kelola pengaturan sistem dan konfigurasi aplikasi"
    />

    {{-- Custom DateTime Warning Banner --}}
    @if($systemInfo['custom_datetime_active'])
        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                        Mode Waktu Custom Aktif
                    </p>
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        Sistem menggunakan waktu custom: <strong>{{ $systemInfo['server_time'] }}</strong> (Waktu nyata: {{ $systemInfo['real_time'] }})
                    </p>
                </div>
                <button 
                    type="button"
                    wire:click="resetToRealTime"
                    class="px-3 py-1.5 text-sm font-medium text-amber-700 dark:text-amber-200 bg-amber-100 dark:bg-amber-800 hover:bg-amber-200 dark:hover:bg-amber-700 rounded-md transition-colors"
                >
                    Reset ke Waktu Nyata
                </button>
            </div>
        </div>
    @endif

    {{-- System Info Card --}}
    <x-ui.card>
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Informasi Sistem</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">PHP:</span>
                    <span class="ml-1 font-medium">{{ $systemInfo['php_version'] }}</span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Laravel:</span>
                    <span class="ml-1 font-medium">{{ $systemInfo['laravel_version'] }}</span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Zona Waktu:</span>
                    <span class="ml-1 font-medium">{{ $systemInfo['timezone'] }}</span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Waktu Sistem:</span>
                    <span class="ml-1 font-medium {{ $systemInfo['custom_datetime_active'] ? 'text-amber-600 dark:text-amber-400' : '' }}">
                        {{ $systemInfo['server_time'] }}
                        @if($systemInfo['custom_datetime_active'])
                            <span class="text-xs">(custom)</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <form wire:submit="save" class="space-y-6">
            {{-- Custom DateTime Section for Audit/Development --}}
            <x-layout.form-section 
                title="Waktu Custom (Audit/Development)"
                description="Atur waktu sistem secara manual untuk keperluan audit dan pengembangan"
            >
                <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-purple-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-purple-700 dark:text-purple-300">
                            <p class="font-medium mb-1">Fitur ini untuk keperluan audit dan pengembangan</p>
                            <p>Mengaktifkan waktu custom akan mempengaruhi seluruh fungsi sistem yang bergantung pada waktu, termasuk absensi, laporan, dan status toko.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <x-ui.checkbox
                            label="Aktifkan Waktu Custom"
                            name="use_custom_datetime"
                            wire:model.live="use_custom_datetime"
                        />
                        <p class="mt-1 text-sm text-gray-500">Jika diaktifkan, sistem akan menggunakan tanggal dan waktu yang Anda tentukan</p>
                    </div>

                    @if($use_custom_datetime)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div>
                                <x-ui.input
                                    label="Tanggal Custom"
                                    name="custom_date"
                                    type="date"
                                    wire:model.live="custom_date"
                                    help="Pilih tanggal yang ingin digunakan sistem"
                                />
                            </div>

                            <div>
                                <x-ui.input
                                    label="Waktu Custom"
                                    name="custom_time"
                                    type="time"
                                    wire:model.live="custom_time"
                                    help="Pilih waktu yang ingin digunakan sistem (format 24 jam)"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <div class="p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Preview Waktu Custom:</span>
                                        @if($custom_date && $custom_time)
                                            <span class="ml-2 text-purple-600 dark:text-purple-400 font-mono">
                                                {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i', $custom_date . ' ' . $custom_time)->format($datetime_format) }}
                                            </span>
                                        @else
                                            <span class="ml-2 text-gray-400">Belum diatur</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-layout.form-section>

            {{-- DateTime Settings Section --}}
            <x-layout.form-section 
                title="Pengaturan Waktu & Tanggal"
                description="Konfigurasi zona waktu, format tanggal dan waktu untuk seluruh sistem"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-ui.select
                            label="Zona Waktu"
                            name="timezone"
                            wire:model.live="timezone"
                            :options="$timezoneOptions"
                            help="Zona waktu yang digunakan di seluruh sistem"
                        />
                    </div>

                    <div>
                        <x-ui.select
                            label="Bahasa/Locale"
                            name="locale"
                            wire:model.live="locale"
                            :options="$localeOptions"
                            help="Bahasa untuk format tanggal dan waktu"
                        />
                    </div>

                    <div>
                        <x-ui.select
                            label="Format Tanggal"
                            name="date_format"
                            wire:model.live="date_format"
                            :options="$dateFormatOptions"
                            help="Format tampilan tanggal"
                        />
                    </div>

                    <div>
                        <x-ui.select
                            label="Format Waktu"
                            name="time_format"
                            wire:model.live="time_format"
                            :options="$timeFormatOptions"
                            help="Format tampilan waktu"
                        />
                    </div>

                    <div>
                        <x-ui.select
                            label="Format Tanggal & Waktu"
                            name="datetime_format"
                            wire:model.live="datetime_format"
                            :options="$datetimeFormatOptions"
                            help="Format tampilan tanggal dan waktu gabungan"
                        />
                    </div>

                    <div>
                        <x-ui.select
                            label="Hari Pertama Minggu"
                            name="first_day_of_week"
                            wire:model="first_day_of_week"
                            :options="$firstDayOfWeekOptions"
                            help="Hari pertama dalam kalender mingguan"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.checkbox
                            label="Gunakan Format 24 Jam"
                            name="use_24_hour"
                            wire:model="use_24_hour"
                        />
                        <p class="mt-1 text-sm text-gray-500">Jika dinonaktifkan, akan menggunakan format 12 jam (AM/PM)</p>
                    </div>
                </div>

                {{-- Preview Section --}}
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">Preview Format</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-blue-600 dark:text-blue-400">Tanggal:</span>
                            <span class="ml-1 font-medium" wire:poll.5s>{{ now()->setTimezone($timezone)->format($date_format) }}</span>
                        </div>
                        <div>
                            <span class="text-blue-600 dark:text-blue-400">Waktu:</span>
                            <span class="ml-1 font-medium" wire:poll.5s>{{ now()->setTimezone($timezone)->format($time_format) }}</span>
                        </div>
                        <div>
                            <span class="text-blue-600 dark:text-blue-400">Tanggal & Waktu:</span>
                            <span class="ml-1 font-medium" wire:poll.5s>{{ now()->setTimezone($timezone)->format($datetime_format) }}</span>
                        </div>
                    </div>
                </div>
            </x-layout.form-section>

            {{-- Security Settings Section --}}
            <x-layout.form-section 
                title="Pengaturan Keamanan"
                description="Konfigurasi keamanan dan autentikasi"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-ui.input
                            label="Durasi Sesi (menit)"
                            name="session_lifetime"
                            type="number"
                            wire:model="session_lifetime"
                            placeholder="120"
                            help="Durasi sesi pengguna sebelum logout otomatis"
                        />
                    </div>

                    <div>
                        <x-ui.input
                            label="Maksimal Percobaan Login"
                            name="max_login_attempts"
                            type="number"
                            wire:model="max_login_attempts"
                            placeholder="5"
                            help="Jumlah maksimal percobaan login sebelum akun dikunci"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.checkbox
                            label="Aktifkan Verifikasi Email"
                            name="email_verification"
                            wire:model="email_verification"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.checkbox
                            label="Aktifkan Two-Factor Authentication"
                            name="two_factor_auth"
                            wire:model="two_factor_auth"
                        />
                    </div>
                </div>
            </x-layout.form-section>

            {{-- Notification Settings Section --}}
            <x-layout.form-section 
                title="Pengaturan Notifikasi"
                description="Konfigurasi sistem notifikasi"
            >
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <x-ui.checkbox
                            label="Notifikasi Email"
                            name="email_notifications"
                            wire:model="email_notifications"
                        />
                    </div>

                    <div>
                        <x-ui.checkbox
                            label="Notifikasi Browser"
                            name="browser_notifications"
                            wire:model="browser_notifications"
                        />
                    </div>

                    <div>
                        <x-ui.checkbox
                            label="Notifikasi SMS"
                            name="sms_notifications"
                            wire:model="sms_notifications"
                        />
                    </div>
                </div>
            </x-layout.form-section>

            {{-- Maintenance Settings Section --}}
            <x-layout.form-section 
                title="Pengaturan Maintenance"
                description="Mode pemeliharaan dan backup"
            >
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <x-ui.checkbox
                            label="Mode Maintenance"
                            name="maintenance_mode"
                            wire:model="maintenance_mode"
                        />
                        <p class="mt-2 text-sm text-gray-500">
                            Aktifkan mode maintenance untuk mencegah akses pengguna saat pemeliharaan sistem
                        </p>
                    </div>

                    <div>
                        <x-ui.checkbox
                            label="Backup Otomatis"
                            name="auto_backup"
                            wire:model="auto_backup"
                        />
                    </div>

                    <div>
                        <x-ui.select
                            label="Frekuensi Backup"
                            name="backup_frequency"
                            wire:model="backup_frequency"
                            :options="[
                                'daily' => 'Harian',
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                            ]"
                        />
                    </div>
                </div>
            </x-layout.form-section>

            <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-ui.button 
                    type="button" 
                    variant="secondary"
                    icon="trash"
                    wire:click="clearCache"
                    wire:confirm="Apakah Anda yakin ingin membersihkan cache?"
                >
                    Bersihkan Cache
                </x-ui.button>

                <x-ui.button 
                    type="submit" 
                    variant="primary"
                    icon="check"
                >
                    Simpan Pengaturan
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
