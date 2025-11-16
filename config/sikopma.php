<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SIKOPMA Application Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for SIKOPMA (Sistem Informasi Koperasi Mahasiswa)
    | This file contains all application-specific constants and settings.
    |
    */

    /**
     * Session time labels
     */
    'sessions' => [
        1 => '08:00 - 12:00',
        2 => '12:00 - 16:00',
        3 => '16:00 - 20:00',
    ],

    /**
     * Session start and end times
     */
    'session_times' => [
        1 => [
            'start' => '08:00',
            'end' => '12:00',
        ],
        2 => [
            'start' => '12:00',
            'end' => '16:00',
        ],
        3 => [
            'start' => '16:00',
            'end' => '20:00',
        ],
    ],

    /**
     * Late threshold in minutes
     * If check-in is more than this many minutes after session start, mark as late
     */
    'late_threshold_minutes' => 15,

    /**
     * Geofence settings for attendance check-in
     * Update these coordinates to match your organization's location
     */
    'geofence' => [
        'latitude' => -7.7956,  // Example: Yogyakarta coordinates
        'longitude' => 110.3695,
        'radius_meters' => 100,  // Allowed radius in meters
    ],

    /**
     * Penalty point thresholds
     */
    'penalty' => [
        'warning_threshold' => 50,
        'suspension_threshold' => 100,
        'expiry_months' => 6,  // Penalty points expire after this many months
    ],

    /**
     * Attendance settings
     */
    'attendance' => [
        'auto_absent_after_hours' => 2,  // Mark as absent if no check-in after this many hours
        'require_geolocation' => true,
        'allow_early_checkin_minutes' => 30,  // Allow check-in this many minutes before session start
    ],

    /**
     * Schedule settings
     */
    'schedule' => [
        'min_assignments_per_week' => 2,
        'max_assignments_per_week' => 5,
        'advance_generation_days' => 14,  // Generate schedule this many days in advance
    ],

    /**
     * Leave request settings
     */
    'leave' => [
        'max_days_per_month' => 5,
        'min_advance_notice_days' => 3,  // Must request leave at least this many days in advance
        'require_document' => true,  // Require supporting document for leave
    ],

    /**
     * Swap request settings
     */
    'swap' => [
        'require_target_approval' => true,
        'require_admin_approval' => true,
        'min_advance_notice_days' => 2,
    ],

    /**
     * Product/Inventory settings
     */
    'inventory' => [
        'low_stock_multiplier' => 1.0,  // Alert when stock <= min_stock * multiplier
        'auto_reorder' => false,
    ],

    /**
     * Sales/Cashier settings
     */
    'sales' => [
        'invoice_prefix' => 'INV',
        'allow_negative_stock' => false,
        'require_payment_proof' => false,
    ],

    /**
     * Purchase settings
     */
    'purchase' => [
        'invoice_prefix' => 'PO',
        'require_approval' => false,
        'auto_update_stock' => true,
    ],

    /**
     * Report settings
     */
    'reports' => [
        'default_format' => 'pdf',  // pdf, excel, csv
        'storage_path' => 'reports',
        'auto_delete_after_days' => 30,
    ],

    /**
     * Notification settings
     */
    'notifications' => [
        'auto_delete_read_after_days' => 30,
        'max_unread_display' => 10,
    ],

    /**
     * Pagination settings
     */
    'pagination' => [
        'per_page' => 15,
        'per_page_options' => [10, 15, 25, 50, 100],
    ],

    /**
     * Cache settings (in minutes)
     */
    'cache' => [
        'dashboard_stats' => 5,
        'user_stats' => 10,
        'product_list' => 15,
        'reports' => 60,
    ],

    /**
     * Date/Time formats
     */
    'formats' => [
        'date' => 'd/m/Y',
        'datetime' => 'd/m/Y H:i',
        'time' => 'H:i',
        'database_date' => 'Y-m-d',
        'database_datetime' => 'Y-m-d H:i:s',
    ],

    /**
     * File upload settings
     */
    'uploads' => [
        'max_size_mb' => 5,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'path' => 'uploads',
    ],

];
