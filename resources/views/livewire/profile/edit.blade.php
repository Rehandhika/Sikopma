<div class="max-w-4xl mx-auto space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Edit Profil</h2>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button wire:click="$set('activeTab', 'profile')" 
                        class="px-6 py-3 text-sm font-medium {{ $activeTab === 'profile' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Informasi Profil
                </button>
                <button wire:click="$set('activeTab', 'password')" 
                        class="px-6 py-3 text-sm font-medium {{ $activeTab === 'password' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Ubah Password
                </button>
            </nav>
        </div>

        <!-- Profile Tab -->
        @if($activeTab === 'profile')
            <form wire:submit="updateProfile" class="p-6 space-y-6">
                <!-- Photo Upload -->
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        @if($current_photo)
                            <img src="{{ Storage::url($current_photo) }}" alt="Profile" class="w-24 h-24 rounded-full object-cover">
                        @elseif($photo)
                            <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-24 h-24 rounded-full object-cover">
                        @else
                            <div class="w-24 h-24 bg-indigo-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-3xl">{{ substr($name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="btn btn-white cursor-pointer">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Upload Foto
                            <input type="file" wire:model="photo" accept="image/*" class="hidden">
                        </label>
                        @if($current_photo)
                            <button type="button" wire:click="deletePhoto" class="ml-2 text-sm text-red-600 hover:text-red-800">
                                Hapus Foto
                            </button>
                        @endif
                        @error('photo') <span class="block text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG. Max 2MB</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label">NIM <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="nim" class="form-control" required>
                        @error('nim') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="form-control" required>
                        @error('name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="email" class="form-control" required>
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label">No. Telepon</label>
                        <input type="text" wire:model="phone" class="form-control" placeholder="08xxxxxxxxxx">
                        @error('phone') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Alamat</label>
                        <textarea wire:model="address" rows="3" class="form-control" placeholder="Alamat lengkap"></textarea>
                        @error('address') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="text-sm text-gray-500">
                        <strong>Role:</strong> 
                        @foreach($user->roles as $role)
                            <span class="badge badge-secondary ml-1">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        @endif

        <!-- Password Tab -->
        @if($activeTab === 'password')
            <form wire:submit="updatePassword" class="p-6 space-y-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <strong>Perhatian:</strong> Pastikan password baru Anda kuat dan mudah diingat. Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol.
                        </div>
                    </div>
                </div>

                <div class="max-w-md space-y-4">
                    <div>
                        <label class="form-label">Password Saat Ini <span class="text-red-500">*</span></label>
                        <input type="password" wire:model="current_password" class="form-control" required>
                        @error('current_password') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label">Password Baru <span class="text-red-500">*</span></label>
                        <input type="password" wire:model="new_password" class="form-control" required>
                        @error('new_password') <span class="form-error">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
                    </div>

                    <div>
                        <label class="form-label">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                        <input type="password" wire:model="new_password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Ubah Password
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
