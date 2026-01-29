<?php

namespace App\Observers;

use App\Models\User;
use App\Services\MenuAccessService;
use Illuminate\Support\Facades\Log;

/**
 * Observer for User model to handle permission cache invalidation
 * when user roles are updated.
 *
 * This observer listens to User model events and clears the permission
 * cache to ensure menu access states are updated immediately.
 *
 * @see Requirements 2.3, 8.1, 8.2
 */
class UserRoleObserver
{
    public function __construct(
        protected MenuAccessService $menuAccessService
    ) {}

    /**
     * Handle the User "updated" event.
     *
     * This is triggered when user attributes are updated.
     * We check if roles might have changed and invalidate cache.
     */
    public function updated(User $user): void
    {
        // Invalidate user's menu access cache
        $this->invalidateUserPermissionCache($user);
    }

    /**
     * Handle the User "deleted" event.
     *
     * Clean up cache when user is deleted.
     */
    public function deleted(User $user): void
    {
        $this->invalidateUserPermissionCache($user);
    }

    /**
     * Invalidate permission cache for a specific user.
     */
    protected function invalidateUserPermissionCache(User $user): void
    {
        try {
            // Clear menu access cache for this user
            $this->menuAccessService->invalidateUserCache($user->id);

            Log::info('User permission cache invalidated', [
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate user permission cache', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
