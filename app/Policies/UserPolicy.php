<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('lihat_pengguna');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // User can view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('lihat_pengguna');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('kelola_pengguna');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // User can update their own profile (limited fields)
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('kelola_pengguna');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot delete Super Admin (handled by model, but double-check here)
        $superAdminRole = config('roles.super_admin_role', 'Super Admin');
        if ($model->hasRole($superAdminRole)) {
            return false;
        }

        return $user->can('kelola_pengguna');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('kelola_pengguna');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('kelola_pengguna');
    }

    /**
     * Determine whether the user can change the user's role.
     */
    public function changeRole(User $user, User $model): bool
    {
        // Cannot change own role
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('kelola_peran');
    }

    /**
     * Determine whether the user can change the user's status.
     */
    public function changeStatus(User $user, User $model): bool
    {
        // Cannot change own status
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot change Super Admin status
        $superAdminRole = config('roles.super_admin_role', 'Super Admin');
        if ($model->hasRole($superAdminRole)) {
            return false;
        }

        return $user->can('kelola_pengguna');
    }

    /**
     * Determine whether the user can reset the user's password.
     */
    public function resetPassword(User $user, User $model): bool
    {
        // Cannot reset own password through admin panel
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('kelola_pengguna');
    }
}
