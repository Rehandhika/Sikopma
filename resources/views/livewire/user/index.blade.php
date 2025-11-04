<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Manajemen Anggota</h2>
        <button wire:click="create" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Anggota
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">Total Anggota</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">Tidak Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Cari nama, NIM, atau email..." class="form-control">
            </div>
            <div>
                <select wire:model.live="roleFilter" class="form-control">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="statusFilter" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="font-medium">{{ $user->nim }}</td>
                        <td>
                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                            @if($user->phone)
                                <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    <span class="badge badge-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'ketua' ? 'primary' : 'secondary') }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <button wire:click="toggleStatus({{ $user->id }})" 
                                    class="badge {{ $user->status === 'active' ? 'badge-secondary' : 'badge-gray' }} cursor-pointer hover:opacity-75">
                                {{ $user->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </button>
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button wire:click="edit({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                @if(!$user->hasRole('super-admin') && $user->id !== auth()->id())
                                    <button wire:click="delete({{ $user->id }})" 
                                            wire:confirm="Yakin ingin menghapus anggota ini?"
                                            class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            Tidak ada data anggota
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $users->links() }}</div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50" 
             x-data @click.self="$wire.set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editMode ? 'Edit Anggota' : 'Tambah Anggota' }}
                    </h3>
                </div>
                
                <form wire:submit="save" class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <input type="text" wire:model="phone" class="form-control">
                            @error('phone') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Alamat</label>
                            <textarea wire:model="address" rows="2" class="form-control"></textarea>
                            @error('address') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Password {{ $editMode ? '(Kosongkan jika tidak diubah)' : '' }} <span class="text-red-500">{{ $editMode ? '' : '*' }}</span></label>
                            <input type="password" wire:model="password" class="form-control" {{ $editMode ? '' : 'required' }}>
                            @error('password') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" wire:model="password_confirmation" class="form-control">
                        </div>

                        <div>
                            <label class="form-label">Status <span class="text-red-500">*</span></label>
                            <select wire:model="status" class="form-control" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                            @error('status') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Role <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2">
                                @foreach($roles as $role)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" wire:model="selectedRoles" value="{{ $role->name }}" 
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">{{ ucfirst($role->name) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedRoles') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
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
