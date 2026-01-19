<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\DateTimeSettingsService;

$service = new DateTimeSettingsService();

echo "=== Test Custom DateTime Feature ===\n\n";

// Test 1: Check current state
echo "1. Current State:\n";
echo "   - Custom DateTime Enabled: " . ($service->isCustomDateTimeEnabled() ? 'Yes' : 'No') . "\n";
echo "   - System Now: " . $service->now()->format('Y-m-d H:i:s') . "\n";
echo "   - Real Now: " . $service->realNow()->format('Y-m-d H:i:s') . "\n\n";

// Test 2: Enable custom datetime
echo "2. Enabling Custom DateTime (2025-06-15 07:00):\n";
$service->enableCustomDateTime('2025-06-15', '07:00');
echo "   - Custom DateTime Enabled: " . ($service->isCustomDateTimeEnabled() ? 'Yes' : 'No') . "\n";
echo "   - System Now: " . $service->now()->format('Y-m-d H:i:s') . "\n";
echo "   - Real Now: " . $service->realNow()->format('Y-m-d H:i:s') . "\n";
echo "   - Custom Settings: " . json_encode($service->getCustomDateTimeSettings()) . "\n\n";

// Test 3: Verify helper functions
echo "3. Testing Helper Functions:\n";
echo "   - system_now(): " . system_now()->format('Y-m-d H:i:s') . "\n";
echo "   - real_now(): " . real_now()->format('Y-m-d H:i:s') . "\n";
echo "   - is_custom_datetime_enabled(): " . (is_custom_datetime_enabled() ? 'Yes' : 'No') . "\n\n";

// Test 4: Disable custom datetime
echo "4. Disabling Custom DateTime:\n";
$service->disableCustomDateTime();
echo "   - Custom DateTime Enabled: " . ($service->isCustomDateTimeEnabled() ? 'Yes' : 'No') . "\n";
echo "   - System Now: " . $service->now()->format('Y-m-d H:i:s') . "\n";
echo "   - Real Now: " . $service->realNow()->format('Y-m-d H:i:s') . "\n\n";

echo "=== All Tests Completed ===\n";
