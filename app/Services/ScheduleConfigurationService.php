<?php

namespace App\Services;

use App\Models\ScheduleConfiguration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ScheduleConfigurationService
 *
 * Service for managing schedule system configuration values with caching support.
 * Provides methods to get, set, and manage configuration values with automatic
 * type casting and cache invalidation.
 *
 * Features:
 * - Automatic caching with configurable TTL
 * - Type casting (integer, float, boolean, json, string)
 * - Cache invalidation on updates
 * - Bulk operations (getAll, getMany)
 * - Default configuration initialization
 */
class ScheduleConfigurationService
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    protected const CACHE_TTL = 3600;

    /**
     * Cache key prefix
     */
    protected const CACHE_PREFIX = 'schedule_config_';

    /**
     * Get a configuration value with caching
     *
     * @param  string  $key  Configuration key
     * @param  mixed  $default  Default value if key not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = $this->getCacheKey($key);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $config = ScheduleConfiguration::where('key', $key)->first();

            if (! $config) {
                Log::debug("Configuration key not found: {$key}, using default", [
                    'key' => $key,
                    'default' => $default,
                ]);

                return $default;
            }

            return $this->castValue($config->value, $config->type);
        });
    }

    /**
     * Set a configuration value with cache invalidation
     *
     * @param  string  $key  Configuration key
     * @param  mixed  $value  Configuration value
     * @param  string  $type  Value type (integer, float, boolean, json, string)
     * @param  string|null  $description  Optional description
     */
    public function set(string $key, $value, string $type = 'string', ?string $description = null): ScheduleConfiguration
    {
        // Convert value to string for storage
        $stringValue = $this->convertToString($value, $type);

        $config = ScheduleConfiguration::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'description' => $description,
            ]
        );

        // Invalidate cache for this specific key
        $this->invalidateCache($key);

        Log::info("Configuration updated: {$key}", [
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'admin' => auth()->user()?->name ?? 'System',
        ]);

        return $config;
    }

    /**
     * Get all configuration values with caching
     */
    public function getAll(): array
    {
        $cacheKey = $this->getCacheKey('all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return ScheduleConfiguration::all()
                ->mapWithKeys(function ($config) {
                    return [$config->key => $this->castValue($config->value, $config->type)];
                })
                ->toArray();
        });
    }

    /**
     * Get all configurations grouped by type
     */
    public function getAllGrouped(): array
    {
        $cacheKey = $this->getCacheKey('all_grouped');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return ScheduleConfiguration::all()
                ->groupBy('type')
                ->map(function ($group) {
                    return $group->mapWithKeys(function ($config) {
                        return [
                            $config->key => [
                                'value' => $this->castValue($config->value, $config->type),
                                'description' => $config->description,
                            ],
                        ];
                    });
                })
                ->toArray();
        });
    }

    /**
     * Get multiple configuration values at once
     *
     * @param  array  $keys  Array of configuration keys
     * @param  mixed  $default  Default value for missing keys
     */
    public function getMany(array $keys, $default = null): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * Check if a configuration key exists
     *
     * @param  string  $key  Configuration key
     */
    public function has(string $key): bool
    {
        $cacheKey = $this->getCacheKey("exists_{$key}");

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key) {
            return ScheduleConfiguration::where('key', $key)->exists();
        });
    }

    /**
     * Delete a configuration key
     *
     * @param  string  $key  Configuration key
     */
    public function delete(string $key): bool
    {
        $deleted = ScheduleConfiguration::where('key', $key)->delete();

        if ($deleted) {
            $this->invalidateCache($key);

            Log::info("Configuration deleted: {$key}", [
                'key' => $key,
                'admin' => auth()->user()?->name ?? 'System',
            ]);
        }

        return (bool) $deleted;
    }

    /**
     * Invalidate cache for a specific key
     *
     * @param  string  $key  Configuration key
     */
    public function invalidateCache(string $key): void
    {
        // Invalidate specific key cache
        Cache::forget($this->getCacheKey($key));
        Cache::forget($this->getCacheKey("exists_{$key}"));

        // Invalidate bulk caches
        Cache::forget($this->getCacheKey('all'));
        Cache::forget($this->getCacheKey('all_grouped'));

        Log::debug("Cache invalidated for configuration: {$key}");
    }

    /**
     * Clear all configuration caches
     */
    public function clearAllCache(): void
    {
        // Get all configuration keys
        $keys = ScheduleConfiguration::pluck('key');

        foreach ($keys as $key) {
            Cache::forget($this->getCacheKey($key));
            Cache::forget($this->getCacheKey("exists_{$key}"));
        }

        // Clear bulk caches
        Cache::forget($this->getCacheKey('all'));
        Cache::forget($this->getCacheKey('all_grouped'));

        Log::info('All configuration caches cleared', [
            'admin' => auth()->user()?->name ?? 'System',
        ]);
    }

    /**
     * Cast value from string to appropriate type
     *
     * @param  string  $value  Stored value
     * @param  string  $type  Target type
     * @return mixed
     */
    protected function castValue($value, string $type)
    {
        // Handle NULL values
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => $this->castToBoolean($value),
            'json' => $this->castToJson($value),
            default => $value,
        };
    }

    /**
     * Convert value to string for storage
     *
     * @param  mixed  $value  Value to convert
     * @param  string  $type  Value type
     */
    protected function convertToString($value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Cast string to boolean
     *
     * @param  mixed  $value  Value to cast
     */
    protected function castToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower((string) $value);

        return in_array($value, ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Cast string to JSON array
     *
     * @param  mixed  $value  Value to cast
     */
    protected function castToJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get cache key with prefix
     *
     * @param  string  $key  Configuration key
     */
    protected function getCacheKey(string $key): string
    {
        return self::CACHE_PREFIX.$key;
    }

    /**
     * Get default configuration values for schedule system
     */
    public function getDefaults(): array
    {
        return [
            // User workload limits
            'max_assignments_per_user' => 4,
            'min_assignments_per_user' => 1,

            // Multi-user slot settings
            'max_users_per_slot' => null, // null = unlimited
            'min_users_per_slot' => 0,
            'target_users_per_slot' => 1,
            'allow_empty_slots' => true,
            'warn_on_empty_slots' => true,
            'overstaffed_threshold' => 3,

            // Coverage settings
            'min_coverage_rate' => 50, // % of slots that must have at least 1 user

            // Shift constraints
            'max_consecutive_shifts' => 2,

            // Scoring weights
            'availability_match_score' => 100,
            'workload_penalty_score' => 10,
            'consecutive_penalty_score' => 20,
            'day_variety_bonus_score' => 10,
            'preference_bonus_score' => 50,
            'slot_coverage_bonus' => 30,

            // Performance
            'enable_caching' => true,
            'cache_ttl' => 3600,
            'max_algorithm_iterations' => 1000,
            'enable_backtracking' => true,
        ];
    }

    /**
     * Initialize default configurations if they don't exist
     */
    public function initializeDefaults(): void
    {
        $defaults = $this->getDefaults();

        foreach ($defaults as $key => $value) {
            if (! $this->has($key)) {
                $type = $this->inferType($value);
                $description = $this->getDefaultDescription($key);

                $this->set($key, $value, $type, $description);
            }
        }

        Log::info('Default configurations initialized', [
            'count' => count($defaults),
            'admin' => auth()->user()?->name ?? 'System',
        ]);
    }

    /**
     * Infer type from value
     *
     * @param  mixed  $value
     */
    protected function inferType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_array($value)) {
            return 'json';
        }

        return 'string';
    }

    /**
     * Get default description for configuration key
     */
    protected function getDefaultDescription(string $key): string
    {
        $descriptions = [
            'max_assignments_per_user' => 'Maximum number of assignments per user per week',
            'min_assignments_per_user' => 'Minimum number of assignments per user per week',
            'max_users_per_slot' => 'Maximum users allowed per slot (null = unlimited)',
            'min_users_per_slot' => 'Minimum users required per slot',
            'target_users_per_slot' => 'Target number of users per slot for auto-assignment',
            'allow_empty_slots' => 'Allow slots with no users assigned',
            'warn_on_empty_slots' => 'Show warning for empty slots',
            'overstaffed_threshold' => 'Number of users that triggers overstaffed warning',
            'min_coverage_rate' => 'Minimum percentage of slots that must be filled',
            'max_consecutive_shifts' => 'Maximum consecutive shifts allowed',
            'availability_match_score' => 'Score bonus for matching user availability',
            'workload_penalty_score' => 'Score penalty per existing assignment',
            'consecutive_penalty_score' => 'Score penalty for consecutive shifts',
            'day_variety_bonus_score' => 'Score bonus for assigning to new day',
            'preference_bonus_score' => 'Score bonus for matching user preference',
            'slot_coverage_bonus' => 'Score bonus for filling empty slot',
            'enable_caching' => 'Enable caching for schedule data',
            'cache_ttl' => 'Cache time-to-live in seconds',
            'max_algorithm_iterations' => 'Maximum iterations for auto-assignment algorithm',
            'enable_backtracking' => 'Enable backtracking in auto-assignment algorithm',
        ];

        return $descriptions[$key] ?? "Configuration for {$key}";
    }
}
