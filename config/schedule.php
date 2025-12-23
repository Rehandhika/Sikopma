<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-User Slots Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for multi-user slot support in schedule management.
    | This allows multiple users to be assigned to the same time slot.
    |
    */
    'multi_user_slots' => [
        'enabled' => true,
        'max_users_per_slot' => null, // null = unlimited
        'min_users_per_slot' => 0,
        'allow_empty_slots' => true,
        'warn_on_empty_slots' => true,
        'overstaffed_threshold' => 3, // Warn if more than 3 users in one slot
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Define the time slots for each session.
    |
    */
    'sessions' => [
        1 => [
            'start' => '07:30',
            'end' => '10:00',
            'label' => 'Sesi 1 (Pagi)',
        ],
        2 => [
            'start' => '10:20',
            'end' => '12:50',
            'label' => 'Sesi 2 (Siang)',
        ],
        3 => [
            'start' => '13:30',
            'end' => '16:00',
            'label' => 'Sesi 3 (Sore)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workload Configuration
    |--------------------------------------------------------------------------
    |
    | Define workload limits and recommendations for users.
    |
    */
    'workload' => [
        'max_assignments_per_user' => 4,
        'min_assignments_per_user' => 1,
        'recommended_assignments' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Coverage Configuration
    |--------------------------------------------------------------------------
    |
    | Define coverage requirements and thresholds.
    |
    */
    'coverage' => [
        'min_coverage_for_publish' => 50, // Minimum 50% coverage to publish
        'target_coverage' => 80, // Target 80% coverage
        'warn_below_coverage' => 70, // Warn if below 70%
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching and performance optimization.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'tags' => ['schedules'],
    ],
];
