<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Manajemen Role</h2>
        <button wire:click="create" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Role
        </button>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 capitalize">{{ $role->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $role->users_count }} anggota</p>
                    </div>
                    <span class="badge {{ in_array($role->name, ['super-admin', 'ketua', 'wakil-ketua', 'bph', 'anggota']) ? 'badge-primary' : 'badge-secondary' }}">
                        {{ in_array($role->name, ['super-admin', 'ketua', 'wakil-ketua', 'bph', 'anggota']) ? 'System' : 'Custom' }}
                    </span>
                </div>

                <div class="mb-4">
                    <div class="text-sm text-gray-600 mb-2">Permissions:</div>
                    <div class="flex flex-wrap gap-1">
                        @forelse($role->permissions->take(5) as $permission)
                            <span class="badge badge-gray text-xs">{{ $permission->name }}</span>
                        @empty
                            <span class="text-sm text-gray-400">No permissions</span>
                        @endforelse
                        @if($role->permissions->count() > 5)
                            <span class="badge badge-info text-xs">+{{ $role->permissions->count() - 5 }} more</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-4 border-t border-gray-200">
                    <button wire:click="edit({{ $role->id }})" class="flex-1 btn btn-white text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </button>
                    @if(!in_array($role->name, ['super-admin', 'ketua', 'wakil-ketua', 'bph', 'anggota']))
                        <button wire:click="delete({{ $role->id }})" 
                                wire:confirm="Yakin ingin menghapus role ini?"
                                class="btn btn-danger text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50" 
             x-data @click.self="$wire.set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editMode ? 'Edit Role' : 'Tambah Role' }}
                    </h3>
                </div>
                
                <form wire:submit="save" class="px-6 py-4 space-y-4">
                    <div>
                        <label class="form-label">Nama Role <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="form-control" required 
                               placeholder="contoh: manager, supervisor">
                        @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Gunakan huruf kecil dan tanpa spasi</p>
                    </div>

                    <div>
                        <label class="form-label">Deskripsi</label>
                        <textarea wire:model="description" rows="2" class="form-control" 
                                  placeholder="Deskripsi singkat tentang role ini"></textarea>
                        @error('description') <span class="form-error">{{ $message }}</span> @enderror>
                    </div>

                    <div>
                        <label class="form-label">Permissions</label>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto">
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($permissions as $permission)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" 
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('selectedPermissions') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="$set('showModal', false)" class="btn btn-white">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $editMode ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
