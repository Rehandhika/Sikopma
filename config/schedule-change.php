<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Schedule Change Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for schedule change requests including reschedule, cancel,
    | and swap operations.
    |
    */

    'reschedule' => [
        /*
         * Minimum notice hours before the scheduled time
         * User must submit reschedule request at least this many hours before
         */
        'min_notice_hours' => env('SCHEDULE_RESCHEDULE_MIN_NOTICE_HOURS', 3),

        /*
         * Maximum number of reschedule requests per month (0 = unlimited)
         */
        'max_per_month' => env('SCHEDULE_RESCHEDULE_MAX_PER_MONTH', 0),

        /*
         * Whether reschedule requests require admin approval
         */
        'requires_admin_approval' => env('SCHEDULE_RESCHEDULE_REQUIRES_ADMIN', true),
    ],

    'cancel' => [
        /*
         * Minimum notice hours before the scheduled time
         * User must submit cancel request at least this many hours before
         */
        'min_notice_hours' => env('SCHEDULE_CANCEL_MIN_NOTICE_HOURS', 24),

        /*
         * Maximum number of cancel requests per month (0 = unlimited)
         */
        'max_per_month' => env('SCHEDULE_CANCEL_MAX_PER_MONTH', 0),

        /*
         * Whether cancel requests require admin approval
         */
        'requires_admin_approval' => env('SCHEDULE_CANCEL_REQUIRES_ADMIN', true),
    ],

    'swap' => [
        /*
         * Minimum notice hours before the scheduled time
         * User must submit swap request at least this many hours before
         */
        'min_notice_hours' => env('SCHEDULE_SWAP_MIN_NOTICE_HOURS', 24),

        /*
         * Maximum number of swap requests per month (0 = unlimited)
         */
        'max_per_month' => env('SCHEDULE_SWAP_MAX_PER_MONTH', 0),

        /*
         * Whether swap requests require target user approval
         */
        'requires_target_approval' => env('SCHEDULE_SWAP_REQUIRES_TARGET', true),

        /*
         * Whether swap requests require admin approval after target approval
         */
        'requires_admin_approval' => env('SCHEDULE_SWAP_REQUIRES_ADMIN', true),

        /*
         * Whether to validate conflicts after swap
         * If true, system will check if users will have conflicting schedules after swap
         */
        'validate_conflicts' => env('SCHEDULE_SWAP_VALIDATE_CONFLICTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Permission names used for schedule change operations
    |
    */
    'permissions' => [
        'swap' => [
            'submit' => 'ajukan_tukar_jadwal',
            'approve' => 'setujui_tukar_jadwal',
        ],
        'schedule_change' => [
            'submit' => 'ajukan_perubahan_jadwal',
            'approve' => 'setujui_perubahan_jadwal',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Enable/disable notifications for schedule change events
    |
    */
    'notifications' => [
        'enabled' => env('SCHEDULE_CHANGE_NOTIFICATIONS_ENABLED', true),
        'channels' => ['database', 'mail'], // Available: database, mail, slack
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    |
    | Enable/disable audit trail for schedule changes
    |
    */
    'audit' => [
        'enabled' => env('SCHEDULE_CHANGE_AUDIT_ENABLED', true),
        'retention_days' => env('SCHEDULE_CHANGE_AUDIT_RETENTION_DAYS', 365),
    ],
];
