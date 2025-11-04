<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\{Hash, Storage};
use Illuminate\Validation\Rule;

class Edit extends Component
{
    use WithFileUploads;

    public $user;
    
    // Profile fields
    public $name;
    public $email;
    public $nim;
    public $phone;
    public $address;
    public $photo;
    public $current_photo;
    
    // Password fields
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    
    public $activeTab = 'profile';

    public function mount()
    {
        $this->user = auth()->user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->nim = $this->user->nim;
        $this->phone = $this->user->phone;
        $this->address = $this->user->address;
        $this->current_photo = $this->user->photo;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'nim' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($this->user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048', // 2MB max
        ]);

        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'nim' => $this->nim,
                'phone' => $this->phone,
                'address' => $this->address,
            ];

            // Handle photo upload
            if ($this->photo) {
                // Delete old photo
                if ($this->current_photo) {
                    Storage::disk('public')->delete($this->current_photo);
                }
                
                // Store new photo
                $path = $this->photo->store('photos', 'public');
                $data['photo'] = $path;
                $this->current_photo = $path;
            }

            $this->user->update($data);

            $this->dispatch('alert', type: 'success', message: 'Profil berhasil diperbarui');
            $this->photo = null;
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Verify current password
            if (!Hash::check($this->current_password, $this->user->password)) {
                $this->addError('current_password', 'Password saat ini tidak sesuai');
                return;
            }

            // Update password
            $this->user->update([
                'password' => Hash::make($this->new_password),
            ]);

            // Reset password fields
            $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

            $this->dispatch('alert', type: 'success', message: 'Password berhasil diubah');
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deletePhoto()
    {
        try {
            if ($this->current_photo) {
                Storage::disk('public')->delete($this->current_photo);
                $this->user->update(['photo' => null]);
                $this->current_photo = null;
                
                $this->dispatch('alert', type: 'success', message: 'Foto profil berhasil dihapus');
            }
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.profile.edit')
            ->layout('layouts.app')
            ->title('Edit Profil');
    }
}
