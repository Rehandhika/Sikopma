<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MenuAccessService
{
    /**
     * Get all menu items with access state for current user
     *
     * @return array Menu items with 'accessible' boolean flag
     */
    public function getMenuWithAccessState(): array
    {
        $user = Auth::user();

        if (! $user) {
            return $this->markAllMenusLocked(config('menu.items', []));
        }

        $menuItems = config('menu.items', []);

        return $this->processMenuItems($menuItems, $user);
    }

    /**
     * Check if user can access specific menu item
     */
    public function canAccess(string $menuKey): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Super Admin has full access
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $menuItem = $this->findMenuItemByKey($menuKey, config('menu.items', []));

        if (! $menuItem) {
            return false;
        }

        return $this->checkMenuAccess($menuItem, $user);
    }

    /**
     * Invalidate cache for specific user
     */
    public function invalidateUserCache(int $userId): void
    {
        $cachePrefix = config('menu.cache.prefix', 'menu_access');
        Cache::forget("{$cachePrefix}:{$userId}");

        // Also clear Spatie Permission cache for this user
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Process menu items and add accessible flag
     *
     * @param  mixed  $user
     */
    protected function processMenuItems(array $menuItems, $user): array
    {
        $result = [];

        foreach ($menuItems as $item) {
            // Handle divider items
            if (isset($item['type']) && $item['type'] === 'divider') {
                $result[] = $item;

                continue;
            }

            $processedItem = $this->processMenuItem($item, $user);
            $result[] = $processedItem;
        }

        return $result;
    }

    /**
     * Process a single menu item
     *
     * @param  mixed  $user
     */
    protected function processMenuItem(array $item, $user): array
    {
        $processedItem = $item;

        // Check if user has access to this menu item
        $accessible = $this->checkMenuAccess($item, $user);

        // Process children if they exist
        if (isset($item['children']) && is_array($item['children'])) {
            $processedChildren = [];
            $hasAccessibleChild = false;

            foreach ($item['children'] as $child) {
                $childAccessible = $this->checkMenuAccess($child, $user);
                $processedChild = $child;
                $processedChild['accessible'] = $childAccessible;
                $processedChildren[] = $processedChild;

                if ($childAccessible) {
                    $hasAccessibleChild = true;
                }
            }

            $processedItem['children'] = $processedChildren;

            // Parent is accessible if it has at least one accessible child
            // OR if the parent itself has access (for parent-only menus)
            $processedItem['accessible'] = $accessible || $hasAccessibleChild;
        } else {
            $processedItem['accessible'] = $accessible;
        }

        return $processedItem;
    }

    /**
     * Check if user has access to a menu item
     *
     * @param  mixed  $user
     */
    protected function checkMenuAccess(array $menuItem, $user): bool
    {
        // Super Admin has full access
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Check role restriction if specified
        if (isset($menuItem['roles']) && ! empty($menuItem['roles'])) {
            if (! $user->hasAnyRole($menuItem['roles'])) {
                return false;
            }
        }

        // If no permissions required, menu is accessible to all authenticated users
        $permissions = $menuItem['permissions'] ?? [];

        if (empty($permissions)) {
            return true;
        }

        // Get permission logic (default: 'any' = OR)
        $logic = $menuItem['permission_logic'] ?? 'any';

        return $this->checkPermissions($permissions, $logic, $user);
    }

    /**
     * Check permissions based on logic type
     *
     * @param  string  $logic  'any' (OR) or 'all' (AND)
     * @param  mixed  $user
     */
    protected function checkPermissions(array $permissions, string $logic, $user): bool
    {
        if ($logic === 'all') {
            // User must have ALL permissions
            return $user->hasAllPermissions($permissions);
        }

        // Default: 'any' - User must have at least ONE permission
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Check if user is Super Admin
     *
     * @param  mixed  $user
     */
    protected function isSuperAdmin($user): bool
    {
        $superAdminRole = config('menu.super_admin_role', 'Super Admin');

        return $user->hasRole($superAdminRole);
    }

    /**
     * Find menu item by key (recursive search)
     */
    protected function findMenuItemByKey(string $key, array $menuItems): ?array
    {
        foreach ($menuItems as $item) {
            if (isset($item['key']) && $item['key'] === $key) {
                return $item;
            }

            // Search in children
            if (isset($item['children']) && is_array($item['children'])) {
                $found = $this->findMenuItemByKey($key, $item['children']);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Mark all menus as locked (for unauthenticated users)
     */
    protected function markAllMenusLocked(array $menuItems): array
    {
        $result = [];

        foreach ($menuItems as $item) {
            if (isset($item['type']) && $item['type'] === 'divider') {
                $result[] = $item;

                continue;
            }

            $item['accessible'] = false;

            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = array_map(function ($child) {
                    $child['accessible'] = false;

                    return $child;
                }, $item['children']);
            }

            $result[] = $item;
        }

        return $result;
    }
}
