<?php

namespace App\Listeners;

use App\Services\MenuAccessService;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;
use Spatie\Permission\PermissionRegistrar;

/**
 * Listener to invalidate permission cache when user roles are changed.
 *
 * This listener handles Spatie Permission events for role attachment
 * and detachment, ensuring that menu access states are updated
 * immediately when a user's role changes.
 *
 * @see Requirements 2.3, 8.1, 8.2
 */
class InvalidatePermissionCacheOnRoleChange
{
    public function __construct(
        protected MenuAccessService $menuAccessService
    ) {}

    /**
     * Handle the event.
     *
     * @param  RoleAttached|RoleDetached  $event
     */
    public function handle($event): void
    {
        $this->invalidateCache($event);
    }

    /**
     * Invalidate permission cache for the affected user.
     *
     * @param  RoleAttached|RoleDetached  $event
     */
    protected function invalidateCache($event): void
    {
        try {
            // Get the model (user) from the event
            $model = $event->model;

            if (! $model || ! isset($model->id)) {
                Log::warning('Role change event received but model is invalid', [
                    'event' => get_class($event),
                ]);

                return;
            }

            // Clear menu access cache for this user
            $this->menuAccessService->invalidateUserCache($model->id);

            // Also clear Spatie Permission's global cache
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            Log::info('Permission cache invalidated on role change', [
                'user_id' => $model->id,
                'user_name' => $model->name ?? 'Unknown',
                'role' => $event->role->name ?? 'Unknown',
                'event' => class_basename($event),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate permission cache on role change', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
            ]);
        }
    }
}
