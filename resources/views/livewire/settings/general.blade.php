<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Pengaturan Umum</h2>

    <form wire:submit="save" class="bg-white rounded-lg shadow p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="form-label">Nama Aplikasi</label>
                <input type="text" wire:model="app_name" class="form-control" required>
                @error('app_name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="form-label">Deskripsi</label>
                <textarea wire:model="app_description" rows="3" class="form-control"></textarea>
            </div>

            <div>
                <label class="form-label">Email Kontak</label>
                <input type="email" wire:model="contact_email" class="form-control">
                @error('contact_email') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="form-label">Telepon Kontak</label>
                <input type="text" wire:model="contact_phone" class="form-control">
            </div>

            <div class="md:col-span-2">
                <label class="form-label">Alamat</label>
                <textarea wire:model="address" rows="3" class="form-control"></textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
