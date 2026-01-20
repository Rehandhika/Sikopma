<div class="space-y-6">
    <x-layout.page-header 
        title="Kelola Banner"
        description="Kelola banner promosi yang ditampilkan di halaman katalog"
    />

    {{-- Create/Edit Form --}}
    @if($showForm)
        <x-ui.card>
            <x-layout.form-section 
                title="{{ $editingBannerId ? 'Edit Banner' : 'Tambah Banner Baru' }}"
                description="Isi form di bawah untuk {{ $editingBannerId ? 'memperbarui' : 'menambahkan' }} banner"
            >
                <form wire:submit.prevent="save" class="space-y-6">
                    {{-- Title Field --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Judul Banner (Opsional)
                        </label>
                        <input 
                            type="text" 
                            wire:model="title"
                            id="title"
                            placeholder="Masukkan judul banner untuk alt text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Judul akan digunakan sebagai alt text untuk aksesibilitas</p>
                    </div>

                    {{-- Image Upload --}}
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                            Gambar Banner {{ $editingBannerId ? '(Kosongkan jika tidak ingin mengubah)' : '' }}
                        </label>
                        
                        {{-- Current Image Preview (for editing) --}}
                        @if($editingBannerId && !$imagePreview)
                            @php
                                $currentBanner = \App\Models\Banner::find($editingBannerId);
                            @endphp
                            @if($currentBanner)
                                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-2">Gambar saat ini:</p>
                                    <img 
                                        src="{{ $currentBanner->thumbnail_url }}" 
                                        alt="{{ $currentBanner->title ?: 'Banner' }}"
                                        class="h-20 w-auto rounded border border-gray-200"
                                    >
                                </div>
                            @endif
                        @endif

                        {{-- File Input --}}
                        <input 
                            type="file" 
                            wire:model="image"
                            id="image"
                            accept="image/jpeg,image/jpg,image/png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('image') border-red-500 @enderror"
                            {{ !$editingBannerId ? 'required' : '' }}
                        >
                        @error('image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        {{-- Loading indicator --}}
                        <div wire:loading wire:target="image" class="mt-2">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Mengupload gambar...</span>
                            </div>
                        </div>
                        
                        {{-- Image Preview --}}
                        @if($imagePreview)
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Preview:</p>
                                <div class="relative inline-block">
                                    <img 
                                        src="{{ $imagePreview }}" 
                                        alt="Preview"
                                        class="h-32 w-auto rounded border border-gray-200"
                                    >
                                    <button 
                                        type="button"
                                        wire:click="removeImage"
                                        class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                                        title="Hapus gambar"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                        
                        <p class="mt-1 text-xs text-gray-500">
                            Format: JPG, JPEG, PNG. Maksimal 5MB. Gambar akan dioptimalkan otomatis.
                        </p>
                    </div>

                    {{-- Priority Field --}}
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Prioritas
                        </label>
                        <input 
                            type="number" 
                            wire:model="priority"
                            id="priority"
                            min="0"
                            placeholder="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('priority') border-red-500 @enderror"
                        >
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Angka lebih kecil akan ditampilkan lebih dulu (0 = prioritas tertinggi)</p>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <button 
                            type="button"
                            wire:click="cancelEdit"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                            wire:loading.attr="disabled"
                        >
                            <svg wire:loading class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span wire:loading.remove>{{ $editingBannerId ? 'Perbarui' : 'Simpan' }}</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </x-layout.form-section>
        </x-ui.card>
    @endif

    {{-- Banner List --}}
    <x-ui.card>
        <x-layout.form-section 
            title="Daftar Banner"
            description="Kelola banner yang ada atau tambahkan banner baru"
        >
            {{-- Header Actions --}}
            <div class="flex items-center justify-between mb-6">
                <div class="text-sm text-gray-600">
                    Total: {{ $banners->total() }} banner
                </div>
                @if(!$showForm)
                    <button 
                        wire:click="create"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Banner
                    </button>
                @endif
            </div>

            {{-- Banner Grid --}}
            @if($banners->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($banners as $banner)
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                            {{-- Banner Image --}}
                            <div class="aspect-video bg-gray-100 relative">
                                <img 
                                    src="{{ $banner->thumbnail_url }}" 
                                    alt="{{ $banner->title ?: 'Banner' }}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                >
                                {{-- Status Badge --}}
                                <div class="absolute top-2 right-2">
                                    @if($banner->is_active)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Banner Info --}}
                            <div class="p-4">
                                <div class="space-y-2">
                                    {{-- Title --}}
                                    <h3 class="font-medium text-gray-900 truncate">
                                        {{ $banner->title ?: 'Banner tanpa judul' }}
                                    </h3>
                                    
                                    {{-- Meta Info --}}
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <span>Prioritas: {{ $banner->priority }}</span>
                                        <span>{{ $banner->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    
                                    {{-- Creator --}}
                                    <div class="text-xs text-gray-400">
                                        Dibuat oleh: {{ $banner->creator->name ?? 'Unknown' }}
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-gray-100">
                                    {{-- Edit Button --}}
                                    <button 
                                        wire:click="edit({{ $banner->id }})"
                                        class="flex-1 px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded hover:bg-blue-100 transition-colors flex items-center justify-center gap-1"
                                        title="Edit banner"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>

                                    {{-- Toggle Status Button --}}
                                    <button 
                                        wire:click="toggleStatus({{ $banner->id }})"
                                        wire:confirm="Ubah status banner ini?"
                                        class="flex-1 px-3 py-2 text-xs font-medium {{ $banner->is_active ? 'text-gray-700 bg-gray-50 hover:bg-gray-100' : 'text-green-700 bg-green-50 hover:bg-green-100' }} rounded transition-colors flex items-center justify-center gap-1"
                                        title="{{ $banner->is_active ? 'Nonaktifkan' : 'Aktifkan' }} banner"
                                    >
                                        @if($banner->is_active)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            Nonaktif
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Aktifkan
                                        @endif
                                    </button>

                                    {{-- Delete Button --}}
                                    <button 
                                        wire:click="delete({{ $banner->id }})"
                                        wire:confirm="Hapus banner ini? Tindakan ini tidak dapat dibatalkan."
                                        class="px-3 py-2 text-xs font-medium text-red-700 bg-red-50 rounded hover:bg-red-100 transition-colors flex items-center justify-center"
                                        title="Hapus banner"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $banners->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada banner</h3>
                    <p class="text-gray-500 mb-6">Mulai dengan menambahkan banner promosi pertama Anda.</p>
                    @if(!$showForm)
                        <button 
                            wire:click="create"
                            class="px-6 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 mx-auto"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Banner Pertama
                        </button>
                    @endif
                </div>
            @endif
        </x-layout.form-section>
    </x-ui.card>

    {{-- Info Box --}}
    <x-ui.card>
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex gap-2">
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-700">
                    <p class="font-medium mb-1">Tentang Banner</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Banner akan ditampilkan di bagian atas halaman katalog publik</li>
                        <li>Hanya banner dengan status "Aktif" yang akan ditampilkan</li>
                        <li>Banner diurutkan berdasarkan prioritas (angka kecil = prioritas tinggi)</li>
                        <li>Gambar akan dioptimalkan otomatis untuk performa yang lebih baik</li>
                        <li>Carousel akan auto-slide setiap 4-5 detik jika ada lebih dari 1 banner aktif</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-ui.card>
</div>