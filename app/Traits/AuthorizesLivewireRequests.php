<?php

namespace App\Traits;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;

/**
 * Trait for consistent permission checking in Livewire components.
 *
 * Usage:
 * - Add `use AuthorizesLivewireRequests;` to your Livewire component
 * - Call `$this->authorizePermission('permission_name')` before critical actions
 * - Call `$this->authorizeModelAction('action', $model)` for model-level authorization
 */
trait AuthorizesLivewireRequests
{
    /**
     * Authorize a permission for the current user.
     * Throws AuthorizationException if not authorized.
     *
     * @param  string  $permission  The permission to check
     * @param  string|null  $message  Custom error message
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizePermission(string $permission, ?string $message = null): bool
    {
        if (Gate::denies($permission)) {
            $this->dispatch('toast', message: $message ?? 'Anda tidak memiliki izin untuk melakukan aksi ini.', type: 'error');
            
            abort(403, $message ?? 'Unauthorized action.');
        }
        
        return true;
    }

    /**
     * Authorize an action on a model using Laravel Policies.
     * Throws AuthorizationException if not authorized.
     *
     * @param  string  $action  The action to authorize (e.g., 'update', 'delete')
     * @param  mixed  $model  The model instance or class name
     * @param  string|null  $message  Custom error message
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeModelAction(string $action, mixed $model, ?string $message = null): bool
    {
        if (Gate::denies($action, $model)) {
            $this->dispatch('toast', message: $message ?? 'Anda tidak memiliki izin untuk melakukan aksi ini.', type: 'error');
            
            abort(403, $message ?? 'Unauthorized action.');
        }
        
        return true;
    }

    /**
     * Check if user has a permission without throwing exception.
     * Useful for conditional UI rendering.
     *
     * @param  string  $permission  The permission to check
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        return Gate::allows($permission);
    }

    /**
     * Check if user has any of the given permissions.
     *
     * @param  array  $permissions  Array of permissions to check
     * @return bool
     */
    protected function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (Gate::allows($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has all of the given permissions.
     *
     * @param  array  $permissions  Array of permissions to check
     * @return bool
     */
    protected function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (Gate::denies($permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Authorize multiple permissions (all must be granted).
     *
     * @param  array  $permissions  Array of permissions to check
     * @param  string|null  $message  Custom error message
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeAllPermissions(array $permissions, ?string $message = null): bool
    {
        foreach ($permissions as $permission) {
            $this->authorizePermission($permission, $message);
        }
        
        return true;
    }

    /**
     * Authorize at least one permission from the given array.
     *
     * @param  array  $permissions  Array of permissions to check
     * @param  string|null  $message  Custom error message
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeAnyPermission(array $permissions, ?string $message = null): bool
    {
        if (!$this->hasAnyPermission($permissions)) {
            $this->dispatch('toast', message: $message ?? 'Anda tidak memiliki izin untuk melakukan aksi ini.', type: 'error');
            
            abort(403, $message ?? 'Unauthorized action.');
        }
        
        return true;
    }
}
