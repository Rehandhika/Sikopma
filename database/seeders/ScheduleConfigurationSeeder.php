<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            // User workload limits
            [
                'key' => 'max_assignments_per_user',
                'value' => '4',
                'type' => 'integer',
                'description' => 'Maximum number of assignments per user per week',
            ],
            [
                'key' => 'min_assignments_per_user',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Minimum number of assignments per user per week',
            ],

            // Multi-user slot settings
            [
                'key' => 'max_users_per_slot',
                'value' => 'null',
                'type' => 'integer',
                'description' => 'Maximum users per slot (null = unlimited)',
            ],
            [
                'key' => 'min_users_per_slot',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Minimum users per slot',
            ],
            [
                'key' => 'target_users_per_slot',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Target number of users per slot for auto-assignment',
            ],
            [
                'key' => 'allow_empty_slots',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Allow slots with no users assigned',
            ],
            [
                'key' => 'warn_on_empty_slots',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Show warning when slots are empty',
            ],
            [
                'key' => 'overstaffed_threshold',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Number of users that triggers overstaffed warning',
            ],

            // Coverage settings
            [
                'key' => 'min_coverage_rate',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Minimum percentage of slots that must have at least 1 user',
            ],

            // Shift constraints
            [
                'key' => 'max_consecutive_shifts',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Maximum number of consecutive shifts allowed',
            ],

            // Scoring weights for auto-assignment algorithm
            [
                'key' => 'availability_match_score',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Score bonus for user availability match',
            ],
            [
                'key' => 'workload_penalty_score',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Score penalty per existing assignment',
            ],
            [
                'key' => 'consecutive_penalty_score',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Score penalty for consecutive shifts',
            ],
            [
                'key' => 'day_variety_bonus_score',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Score bonus for assigning to new day',
            ],
            [
                'key' => 'preference_bonus_score',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Score bonus for user preference match',
            ],
            [
                'key' => 'slot_coverage_bonus',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Score bonus for filling empty slots',
            ],

            // Performance settings
            [
                'key' => 'enable_caching',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable caching for schedule data',
            ],
            [
                'key' => 'cache_ttl',
                'value' => '3600',
                'type' => 'integer',
                'description' => 'Cache time-to-live in seconds',
            ],
            [
                'key' => 'max_algorithm_iterations',
                'value' => '1000',
                'type' => 'integer',
                'description' => 'Maximum iterations for auto-assignment algorithm',
            ],
            [
                'key' => 'enable_backtracking',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable backtracking in auto-assignment algorithm',
            ],
        ];

        foreach ($configurations as $config) {
            DB::table('schedule_configurations')->updateOrInsert(
                ['key' => $config['key']],
                [
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'description' => $config['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
