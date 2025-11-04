<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    
    public $showModal = false;
    public $editMode = false;
    public $userId;
    
    // Form fields
    public $nim;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $password;
    public $password_confirmation;
    public $selectedRoles = [];
    public $status = 'active';

    protected function rules()
    {
        $rules = [
            'nim' => 'required|string|max:20|unique:users,nim',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'selectedRoles' => 'required|array|min:1',
            'status' => 'required|in:active,inactive',
        ];

        if (!$this->editMode) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['nim'] = 'required|string|max:20|unique:users,nim,' . $this->userId;
            $rules['email'] = 'required|email|unique:users,email,' . $this->userId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        
        $this->userId = $user->id;
        $this->nim = $user->nim;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->address = $user->address;
        $this->status = $user->status;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $user = User::findOrFail($this->userId);
                
                $user->update([
                    'nim' => $this->nim,
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'status' => $this->status,
                ]);

                if ($this->password) {
                    $user->update(['password' => Hash::make($this->password)]);
                }

                $user->syncRoles($this->selectedRoles);

                $message = 'Anggota berhasil diperbarui';
            } else {
                $user = User::create([
                    'nim' => $this->nim,
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'password' => Hash::make($this->password),
                    'status' => $this->status,
                ]);

                $user->syncRoles($this->selectedRoles);

                $message = 'Anggota berhasil ditambahkan';
            }

            $this->dispatch('alert', type: 'success', message: $message);
            $this->resetForm();
            $this->showModal = false;
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting super admin
            if ($user->hasRole('super-admin')) {
                $this->dispatch('alert', type: 'error', message: 'Super Admin tidak dapat dihapus');
                return;
            }

            // Prevent self-deletion
            if ($user->id === auth()->id()) {
                $this->dispatch('alert', type: 'error', message: 'Anda tidak dapat menghapus akun sendiri');
                return;
            }

            $user->delete();
            $this->dispatch('alert', type: 'success', message: 'Anggota berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->hasRole('super-admin')) {
                $this->dispatch('alert', type: 'error', message: 'Status Super Admin tidak dapat diubah');
                return;
            }

            $user->update([
                'status' => $user->status === 'active' ? 'inactive' : 'active'
            ]);

            $this->dispatch('alert', type: 'success', message: 'Status berhasil diubah');
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->reset([
            'userId', 'nim', 'name', 'email', 'phone', 'address',
            'password', 'password_confirmation', 'selectedRoles', 'status'
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $query = User::query()->with('roles');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('nim', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter) {
            $query->whereHas('roles', function($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $roles = Role::all();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
        ];

        return view('livewire.user.index', [
            'users' => $users,
            'roles' => $roles,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Manajemen Anggota');
    }
}
