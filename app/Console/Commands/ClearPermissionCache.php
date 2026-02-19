<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Command to clear permission-related caches.
 *
 * This command clears all permission caches including:
 * - Spatie permission cache
 * - Menu access cache
 * - User permission cache
 *
 * Usage:
 *   php artisan permission:clear-cache
 *   php artisan permission:clear-cache --user=1
 */
class ClearPermissionCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:clear-cache
                            {--user= : Clear cache for specific user ID}
                            {--all : Clear all permission-related caches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear permission and role caches for the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Clearing permission caches...');

        // Clear Spatie permission cache
        $this->clearSpatieCache();

        // Clear menu access cache
        $this->clearMenuCache();

        // Clear user-specific cache if specified
        if ($userId = $this->option('user')) {
            $this->clearUserCache((int) $userId);
        }

        // Clear all user permission caches if --all flag is set
        if ($this->option('all')) {
            $this->clearAllUserCaches();
        }

        $this->info('Permission caches cleared successfully!');

        return Command::SUCCESS;
    }

    /**
     * Clear Spatie Laravel Permission package cache.
     */
    protected function clearSpatieCache(): void
    {
        $this->info('  - Clearing Spatie permission cache...');
        
        // Clear the permission cache tag if using cache tags
        if (config('permission.cache.use_tags', true)) {
            Cache::tags(config('permission.cache.tag_name', 'spatie.permission'))->flush();
        } else {
            // Clear specific cache keys
            Cache::forget('spatie.permission.cache');
        }

        // Also clear via app() if available
        if (app()->bound('permission.cache')) {
            app('permission.cache')->forgetCachedPermissions();
        }

        $this->line('    <fg=green>✓</> Spatie permission cache cleared');
    }

    /**
     * Clear menu access cache.
     */
    protected function clearMenuCache(): void
    {
        $this->info('  - Clearing menu access cache...');

        $cachePrefix = config('menu.cache.prefix', 'menu_access');
        $cacheEnabled = config('menu.cache.enabled', true);

        if ($cacheEnabled) {
            // Clear menu cache for all users by pattern
            // Note: This requires cache tags support or manual key management
            if (config('cache.default') === 'redis' || config('cache.default') === 'memcached') {
                // For Redis/Memcached, we can use pattern-based deletion
                Cache::forget($cachePrefix . '_*');
            }
            
            // Clear the main menu cache
            Cache::forget('menu_items');
            Cache::forget('menu_access_all');
        }

        $this->line('    <fg=green>✓</> Menu access cache cleared');
    }

    /**
     * Clear permission cache for a specific user.
     *
     * @param  int  $userId
     */
    protected function clearUserCache(int $userId): void
    {
        $this->info("  - Clearing cache for user ID: {$userId}...");

        // Clear user-specific permission cache
        Cache::forget("user.{$userId}.permissions");
        Cache::forget("user.{$userId}.roles");
        Cache::forget("user.{$userId}.menu_access");
        Cache::forget("user.{$userId}.can_*");

        $this->line("    <fg=green>✓</> Cache cleared for user ID: {$userId}");
    }

    /**
     * Clear permission caches for all users.
     * This is more aggressive and should be used sparingly.
     */
    protected function clearAllUserCaches(): void
    {
        $this->info('  - Clearing all user permission caches...');

        // This will clear all caches with user-related tags
        if (config('cache.default') === 'redis') {
            // For Redis, we can flush by pattern
            Cache::tags(['user_permissions', 'user_roles', 'menu_access'])->flush();
        } else {
            // For file/database cache, we need to clear all or manage keys manually
            $this->warn('    Note: Full user cache clear may not be supported for current cache driver.');
        }

        $this->line('    <fg=green>✓</> All user permission caches cleared');
    }
}
