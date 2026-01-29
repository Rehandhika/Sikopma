<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Services\ActivityLogService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

#[Title('Manajemen Role & Permission')]
class Index extends Component
{
    // Modal state
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $roleId = null;
    
    // Form fields
    public string $name = '';
    public string $description = '';
    public array $selectedPermissions = [];
    
    // Search & filter
    public string $search = '';

    // System roles that cannot be deleted
    protected array $systemRoles = ['super-admin', 'ketua', 'wakil-ketua', 'bph', 'anggota'];

    protected function rules(): array
    {
        $uniqueRule = $this->editMode 
            ? 'unique:roles,name,' . $this->roleId 
            : 'unique:roles,name';

        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', $uniqueRule],
            'description' => ['nullable', 'string', 'max:500'],
            'selectedPermissions' => ['array'],
        ];
    }

    protected array $messages = [
        'name.required' => 'Nama role wajib diisi',
        'name.regex' => 'Nama role hanya boleh huruf kecil, angka, dan tanda hubung',
        'name.unique' => 'Nama role sudah digunakan',
    ];

    public function updatedSearch(): void
    {
        // Reset any state if needed
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->withCount('users')
            ->with('permissions:id,name')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByRaw("FIELD(name, 'super-admin', 'ketua', 'wakil-ketua', 'bph', 'anggota') DESC")
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function permissions()
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'other');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $role = Role::with('permissions:id,name')->findOrFail($id);
        
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->description = $role->description ?? '';
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $role = Role::findOrFail($this->roleId);
                $role->update(['name' => $this->name]);
                $message = 'Role berhasil diperbarui';
                
                // Log activity
                ActivityLogService::logRoleUpdated($this->name);
            } else {
                $role = Role::create([
                    'name' => $this->name,
                    'guard_name' => 'web',
                ]);
                $message = 'Role berhasil ditambahkan';
                
                // Log activity
                ActivityLogService::logRoleCreated($this->name);
            }

            $role->syncPermissions($this->selectedPermissions);

            $this->dispatch('toast', message: $message, type: 'success');
            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Terjadi kesalahan: ' . $e->getMessage(), type: 'error');
        }
    }

    public function delete(int $id): void
    {
        try {
            $role = Role::findOrFail($id);
            
            if (in_array($role->name, $this->systemRoles)) {
                $this->dispatch('toast', message: 'Role sistem tidak dapat dihapus', type: 'error');
                return;
            }

            if ($role->users()->count() > 0) {
                $this->dispatch('toast', message: "Role masih digunakan oleh {$role->users()->count()} user", type: 'error');
                return;
            }

            $role->delete();
            
            // Log activity
            ActivityLogService::logRoleDeleted($role->name);
            
            $this->dispatch('toast', message: 'Role berhasil dihapus', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Terjadi kesalahan: ' . $e->getMessage(), type: 'error');
        }
    }

    public function togglePermission(string $permission): void
    {
        if (in_array($permission, $this->selectedPermissions)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, [$permission]));
        } else {
            $this->selectedPermissions[] = $permission;
        }
    }

    public function selectAllInGroup(string $group): void
    {
        $groupPermissions = $this->permissions[$group]->pluck('name')->toArray();
        $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupPermissions));
    }

    public function deselectAllInGroup(string $group): void
    {
        $groupPermissions = $this->permissions[$group]->pluck('name')->toArray();
        $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $groupPermissions));
    }

    public function selectAll(): void
    {
        $this->selectedPermissions = Permission::pluck('name')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedPermissions = [];
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['roleId', 'name', 'description', 'selectedPermissions']);
        $this->resetValidation();
    }

    public function isSystemRole(string $name): bool
    {
        return in_array($name, $this->systemRoles);
    }

    public function render()
    {
        return view('livewire.role.index')->layout('layouts.app');
    }
}
