<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\MenuAccessService;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\PermissionRegistrar;

/**
 * Listener to invalidate permission cache when permissions are changed.
 *
 * This listener handles Spatie Permission events for permission attachment
 * and detachment, ensuring that menu access states are updated
 * immediately when permissions change.
 *
 * @see Requirements 2.3, 8.1, 8.2
 */
class InvalidatePermissionCacheOnPermissionChange
{
    public function __construct(
        protected MenuAccessService $menuAccessService
    ) {}

    /**
     * Handle the event.
     *
     * @param  PermissionAttached|PermissionDetached  $event
     */
    public function handle($event): void
    {
        $this->invalidateCache($event);
    }

    /**
     * Invalidate permission cache for the affected model.
     *
     * @param  PermissionAttached|PermissionDetached  $event
     */
    protected function invalidateCache($event): void
    {
        try {
            $model = $event->model;

            // Always clear Spatie Permission's global cache
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // If the model is a User, clear their specific menu cache
            if ($model instanceof User) {
                $this->menuAccessService->invalidateUserCache($model->id);

                Log::info('Permission cache invalidated for user on permission change', [
                    'user_id' => $model->id,
                    'user_name' => $model->name ?? 'Unknown',
                    'permission' => $event->permission->name ?? 'Unknown',
                    'event' => class_basename($event),
                ]);
            } else {
                // If permission is attached to a Role, we need to invalidate
                // cache for all users with that role
                $this->invalidateCacheForUsersWithRole($model);

                Log::info('Permission cache invalidated for role on permission change', [
                    'role' => $model->name ?? 'Unknown',
                    'permission' => $event->permission->name ?? 'Unknown',
                    'event' => class_basename($event),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to invalidate permission cache on permission change', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
            ]);
        }
    }

    /**
     * Invalidate cache for all users with a specific role.
     *
     * @param  mixed  $role
     */
    protected function invalidateCacheForUsersWithRole($role): void
    {
        if (! method_exists($role, 'users')) {
            return;
        }

        try {
            // Get all users with this role and invalidate their cache
            $users = $role->users()->get();

            foreach ($users as $user) {
                $this->menuAccessService->invalidateUserCache($user->id);
            }

            Log::info('Permission cache invalidated for users with role', [
                'role' => $role->name ?? 'Unknown',
                'affected_users_count' => $users->count(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not invalidate cache for users with role', [
                'role' => $role->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
