<div class="p-6">
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Ajukan Cuti/Izin</h1>
            <p class="mt-1 text-sm text-gray-600">Buat pengajuan cuti atau izin dengan mengisi formulir berikut</p>
        </div>

        {{-- Form --}}
        <form wire:submit="submit">
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                {{-- Leave Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Jenis Cuti/Izin *</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button type="button" wire:click="$set('leave_type', 'permission')" 
                                @class([
                                    'p-4 border-2 rounded-lg transition text-center',
                                    'border-blue-600 bg-blue-50 text-blue-600' => $leave_type === 'permission',
                                    'border-gray-300 text-gray-700 hover:border-gray-400' => $leave_type !== 'permission',
                                ])>
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium">Izin</span>
                        </button>
                        
                        <button type="button" wire:click="$set('leave_type', 'sick')" 
                                @class([
                                    'p-4 border-2 rounded-lg transition text-center',
                                    'border-red-600 bg-red-50 text-red-600' => $leave_type === 'sick',
                                    'border-gray-300 text-gray-700 hover:border-gray-400' => $leave_type !== 'sick',
                                ])>
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span class="text-sm font-medium">Sakit</span>
                        </button>
                        
                        <button type="button" wire:click="$set('leave_type', 'emergency')" 
                                @class([
                                    'p-4 border-2 rounded-lg transition text-center',
                                    'border-yellow-600 bg-yellow-50 text-yellow-600' => $leave_type === 'emergency',
                                    'border-gray-300 text-gray-700 hover:border-gray-400' => $leave_type !== 'emergency',
                                ])>
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-sm font-medium">Darurat</span>
                        </button>
                        
                        <button type="button" wire:click="$set('leave_type', 'other')" 
                                @class([
                                    'p-4 border-2 rounded-lg transition text-center',
                                    'border-purple-600 bg-purple-50 text-purple-600' => $leave_type === 'other',
                                    'border-gray-300 text-gray-700 hover:border-gray-400' => $leave_type !== 'other',
                                ])>
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="text-sm font-medium">Lainnya</span>
                        </button>
                    </div>
                    @error('leave_type') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Date Range --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                        <input wire:model.live="start_date" type="date" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                        <input wire:model.live="end_date" type="date" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Total Days Display --}}
                @if($totalDays > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-blue-800">
                            Total durasi: <strong>{{ $totalDays }} hari</strong>
                        </span>
                    </div>
                </div>
                @endif

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan *</label>
                    <textarea wire:model="reason" rows="4" 
                        placeholder="Jelaskan alasan cuti/izin Anda (minimal 10 karakter)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">{{ strlen($reason) }}/500 karakter</p>
                </div>

                {{-- Attachment --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Lampiran (Opsional)
                        <span class="text-gray-500 font-normal">- Surat keterangan dokter, dll</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition">
                        <div class="space-y-1 text-center">
                            @if ($attachment)
                                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-gray-600">{{ $attachment->getClientOriginalName() }}</p>
                                <button type="button" wire:click="$set('attachment', null)" class="text-sm text-red-600 hover:text-red-800">
                                    Hapus file
                                </button>
                            @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                        <span>Upload file</span>
                                        <input wire:model="attachment" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">JPG, PNG, PDF max 2MB</p>
                            @endif
                        </div>
                    </div>
                    @error('attachment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <div wire:loading wire:target="attachment" class="text-sm text-blue-600 mt-2">
                        Uploading...
                    </div>
                </div>

                {{-- Info Box --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium mb-1">Catatan Penting:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Pengajuan akan ditinjau oleh admin</li>
                                <li>Pastikan mengisi alasan dengan jelas</li>
                                <li>Untuk sakit lebih dari 2 hari, lampirkan surat keterangan dokter</li>
                                <li>Pengajuan darurat akan diprioritaskan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('leave.my-requests') }}" 
                       class="px-6 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </a>
                    <button type="submit" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="submit">Ajukan Permohonan</span>
                        <span wire:loading wire:target="submit">Mengirim...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Loading Overlay --}}
    <div wire:loading.flex wire:target="submit" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-700 font-medium">Memproses pengajuan...</p>
        </div>
    </div>
</div>
