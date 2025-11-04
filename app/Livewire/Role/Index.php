<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Index extends Component
{
    public $showModal = false;
    public $editMode = false;
    public $roleId;
    
    public $name;
    public $description;
    public $selectedPermissions = [];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'selectedPermissions' => 'array',
        ];

        if ($this->editMode) {
            $rules['name'] = 'required|string|max:255|unique:roles,name,' . $this->roleId;
        }

        return $rules;
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->description = $role->description ?? '';
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $role = Role::findOrFail($this->roleId);
                $role->update([
                    'name' => $this->name,
                ]);
                $message = 'Role berhasil diperbarui';
            } else {
                $role = Role::create([
                    'name' => $this->name,
                    'guard_name' => 'web',
                ]);
                $message = 'Role berhasil ditambahkan';
            }

            if (!empty($this->selectedPermissions)) {
                $role->syncPermissions($this->selectedPermissions);
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
            $role = Role::findOrFail($id);
            
            // Prevent deleting system roles
            if (in_array($role->name, ['super-admin', 'ketua', 'wakil-ketua', 'bph', 'anggota'])) {
                $this->dispatch('alert', type: 'error', message: 'Role sistem tidak dapat dihapus');
                return;
            }

            // Check if role is assigned to users
            if ($role->users()->count() > 0) {
                $this->dispatch('alert', type: 'error', message: 'Role masih digunakan oleh ' . $role->users()->count() . ' user');
                return;
            }

            $role->delete();
            $this->dispatch('alert', type: 'success', message: 'Role berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->reset(['roleId', 'name', 'description', 'selectedPermissions']);
        $this->resetValidation();
    }

    public function render()
    {
        $roles = Role::withCount('users')->get();
        $permissions = Permission::all();

        return view('livewire.role.index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ])->layout('layouts.app')->title('Manajemen Role');
    }
}
