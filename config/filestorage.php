<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Type Configurations
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk setiap tipe file yang didukung sistem.
    | Setiap tipe memiliki disk, path, max size, MIME types, dan variants.
    |
    */
    'types' => [
        'product' => [
            'disk' => 'public',
            'base_path' => 'products',
            'max_size' => 10 * 1024 * 1024, // 10MB - increased for high quality images
            'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'convert_to_webp' => false, // Disabled to preserve original quality
            'preserve_original' => true, // Keep original without compression
            'variants' => [
                'thumbnail' => ['width' => 150, 'height' => 150, 'quality' => 100],
                'medium' => ['width' => 400, 'height' => 400, 'quality' => 100],
                'large' => ['width' => 800, 'height' => 800, 'quality' => 100],
            ],
        ],

        'banner' => [
            'disk' => 'public',
            'base_path' => 'banners',
            'max_size' => 10 * 1024 * 1024, // 10MB - increased for high quality banners
            'allowed_mimes' => ['image/jpeg', 'image/png'],
            'convert_to_webp' => false, // Keep original format
            'preserve_original' => true, // Keep original without compression
            'variants' => [
                'desktop' => ['width' => 1920, 'quality' => 100],
                'tablet' => ['width' => 768, 'quality' => 100],
                'mobile' => ['width' => 480, 'quality' => 100],
            ],
        ],

        'attendance' => [
            'disk' => 'public',
            'base_path' => 'attendance',
            'max_size' => 2 * 1024 * 1024, // 2MB
            'allowed_mimes' => ['image/jpeg', 'image/png'],
            'convert_to_webp' => true,
            'thumbnail' => ['width' => 80, 'height' => 80],
            'variants' => [],
        ],

        'profile' => [
            'disk' => 'public',
            'base_path' => 'profiles',
            'max_size' => 2 * 1024 * 1024, // 2MB
            'allowed_mimes' => ['image/jpeg', 'image/png'],
            'convert_to_webp' => true,
            'variants' => [
                'medium' => ['width' => 150, 'height' => 150, 'quality' => 80],
                'small' => ['width' => 40, 'height' => 40, 'quality' => 70],
            ],
        ],

        'leave' => [
            'disk' => 'local', // Private storage
            'base_path' => 'leave-attachments',
            'max_size' => 2 * 1024 * 1024, // 2MB
            'allowed_mimes' => ['image/jpeg', 'image/png', 'application/pdf'],
            'convert_to_webp' => false,
            'variants' => [],
        ],

        'report' => [
            'disk' => 'local', // Private storage
            'base_path' => 'reports',
            'max_size' => 10 * 1024 * 1024, // 10MB
            'allowed_mimes' => [
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv',
            ],
            'convert_to_webp' => false,
            'variants' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk caching URL file.
    |
    */
    'cache' => [
        'ttl' => 3600, // 1 hour in seconds
        'prefix' => 'file_url',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk pembersihan file temporary dan orphan.
    |
    */
    'cleanup' => [
        'temp_max_age' => 24, // hours - file temporary lebih dari ini akan dihapus
        'orphan_grace_period' => 7, // days - grace period sebelum orphan dihapus
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk monitoring storage usage.
    |
    */
    'monitoring' => [
        'threshold_warning' => 80, // percentage - trigger warning
        'threshold_critical' => 95, // percentage - trigger critical alert
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk pemrosesan gambar.
    |
    */
    'image' => [
        'max_width' => 1920, // Maximum width untuk gambar original
        'default_quality' => 85, // Default quality untuk WebP conversion
        'driver' => 'gd', // 'gd' atau 'imagick'
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi keamanan untuk file storage.
    |
    */
    'security' => [
        'signed_url_expiration' => 60, // minutes - expiration untuk signed URLs
        'validate_mime_content' => true, // Validate actual file content matches MIME
    ],
];
