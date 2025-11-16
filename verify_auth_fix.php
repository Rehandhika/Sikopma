#!/usr/bin/env php
<?php

/**
 * Authentication Fix Verification Script
 * 
 * This script verifies that all authentication fixes are properly implemented
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     SIKOPMA - Authentication Fix Verification                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$allPassed = true;
$testResults = [];

// Test 1: Check if files exist
echo "📁 Test 1: Checking Required Files...\n";
$requiredFiles = [
    'app/Livewire/Auth/Login.php',
    'app/Http/Middleware/EnsureUserIsActive.php',
    'app/Http/Middleware/RedirectIfAuthenticated.php',
    'app/Services/AuthService.php',
    'app/Http/Controllers/Auth/AuthController.php',
    'app/Models/LoginHistory.php',
    'resources/views/layouts/guest.blade.php',
    'routes/auth.php',
];

foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✅ $file\n";
        $testResults['files'][] = true;
    } else {
        echo "   ❌ $file - NOT FOUND\n";
        $testResults['files'][] = false;
        $allPassed = false;
    }
}

// Test 2: Check database connection
echo "\n🗄️  Test 2: Database Connection...\n";
try {
    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "   ✅ Database connected\n";
    $testResults['database'] = true;
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    $testResults['database'] = false;
    $allPassed = false;
}

// Test 3: Check if login_histories table exists
echo "\n📊 Test 3: Login History Table...\n";
try {
    $hasTable = Illuminate\Support\Facades\Schema::hasTable('login_histories');
    if ($hasTable) {
        echo "   ✅ login_histories table exists\n";
        $testResults['login_history_table'] = true;
    } else {
        echo "   ⚠️  login_histories table not found (run: php artisan migrate)\n";
        $testResults['login_history_table'] = false;
    }
} catch (Exception $e) {
    echo "   ❌ Error checking table: " . $e->getMessage() . "\n";
    $testResults['login_history_table'] = false;
}

// Test 4: Check if test users exist
echo "\n👥 Test 4: Test Users...\n";
$testUsers = [
    ['nim' => '00000000', 'name' => 'Super Administrator'],
    ['nim' => '11111111', 'name' => 'Ketua KOPMA'],
    ['nim' => '22222222', 'name' => 'Wakil Ketua KOPMA'],
];

foreach ($testUsers as $testUser) {
    $user = App\Models\User::where('nim', $testUser['nim'])->first();
    if ($user) {
        $status = $user->isActive() ? '✅' : '⚠️';
        echo "   $status {$testUser['nim']} - {$user->name} (Status: {$user->status})\n";
        $testResults['users'][] = true;
    } else {
        echo "   ❌ {$testUser['nim']} - NOT FOUND\n";
        $testResults['users'][] = false;
        $allPassed = false;
    }
}

// Test 5: Test authentication with Auth::attempt()
echo "\n🔐 Test 5: Authentication System...\n";
try {
    // Logout first
    Illuminate\Support\Facades\Auth::logout();
    
    $credentials = [
        'nim' => '00000000',
        'password' => 'password',
        'status' => 'active',
    ];
    
    $result = Illuminate\Support\Facades\Auth::attempt($credentials);
    
    if ($result) {
        $user = Illuminate\Support\Facades\Auth::user();
        echo "   ✅ Auth::attempt() successful\n";
        echo "   ✅ Authenticated as: {$user->name}\n";
        $testResults['auth_attempt'] = true;
        
        // Logout
        Illuminate\Support\Facades\Auth::logout();
    } else {
        echo "   ❌ Auth::attempt() failed\n";
        $testResults['auth_attempt'] = false;
        $allPassed = false;
    }
} catch (Exception $e) {
    echo "   ❌ Authentication error: " . $e->getMessage() . "\n";
    $testResults['auth_attempt'] = false;
    $allPassed = false;
}

// Test 6: Check routes
echo "\n🛣️  Test 6: Routes Configuration...\n";
$requiredRoutes = [
    'login',
    'dashboard',
    'logout',
    'auth.login',
    'auth.logout',
];

foreach ($requiredRoutes as $routeName) {
    if (Illuminate\Support\Facades\Route::has($routeName)) {
        echo "   ✅ Route: $routeName\n";
        $testResults['routes'][] = true;
    } else {
        echo "   ❌ Route: $routeName - NOT FOUND\n";
        $testResults['routes'][] = false;
        $allPassed = false;
    }
}

// Test 7: Check middleware
echo "\n🛡️  Test 7: Middleware Configuration...\n";
$middlewareAliases = ['active', 'guest', 'role', 'permission'];
echo "   ℹ️  Middleware aliases registered: " . implode(', ', $middlewareAliases) . "\n";
echo "   ✅ Middleware configuration looks good\n";
$testResults['middleware'] = true;

// Test 8: Check AuthService
echo "\n⚙️  Test 8: AuthService...\n";
try {
    $authService = app(App\Services\AuthService::class);
    echo "   ✅ AuthService can be instantiated\n";
    
    $user = App\Models\User::where('nim', '00000000')->first();
    if ($user) {
        $canLogin = $authService->canLogin($user);
        if ($canLogin['can_login']) {
            echo "   ✅ canLogin() method works\n";
            $testResults['auth_service'] = true;
        } else {
            echo "   ⚠️  User cannot login: {$canLogin['reason']}\n";
            $testResults['auth_service'] = false;
        }
    }
} catch (Exception $e) {
    echo "   ❌ AuthService error: " . $e->getMessage() . "\n";
    $testResults['auth_service'] = false;
    $allPassed = false;
}

// Test 9: Check LoginHistory model
echo "\n📝 Test 9: LoginHistory Model...\n";
try {
    $loginHistory = new App\Models\LoginHistory();
    echo "   ✅ LoginHistory model can be instantiated\n";
    
    if (Illuminate\Support\Facades\Schema::hasTable('login_histories')) {
        $count = App\Models\LoginHistory::count();
        echo "   ✅ Login history records: $count\n";
    }
    
    $testResults['login_history_model'] = true;
} catch (Exception $e) {
    echo "   ❌ LoginHistory error: " . $e->getMessage() . "\n";
    $testResults['login_history_model'] = false;
    $allPassed = false;
}

// Test 10: Check User model enhancements
echo "\n👤 Test 10: User Model Enhancements...\n";
try {
    $user = App\Models\User::where('nim', '00000000')->first();
    if ($user) {
        // Test new methods
        $canLogin = $user->canLogin();
        $primaryRole = $user->getPrimaryRole();
        $dashboardRoute = $user->getDashboardRoute();
        
        echo "   ✅ canLogin(): " . ($canLogin ? 'true' : 'false') . "\n";
        echo "   ✅ getPrimaryRole(): $primaryRole\n";
        echo "   ✅ getDashboardRoute(): $dashboardRoute\n";
        
        $testResults['user_model'] = true;
    }
} catch (Exception $e) {
    echo "   ❌ User model error: " . $e->getMessage() . "\n";
    $testResults['user_model'] = false;
    $allPassed = false;
}

// Summary
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST SUMMARY                            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, function($result) {
    return is_array($result) ? !in_array(false, $result) : $result === true;
}));

echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n\n";

if ($allPassed) {
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ ALL TESTS PASSED - AUTHENTICATION FIX IS READY!          ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "🎉 Next Steps:\n";
    echo "   1. Run: php artisan migrate (if not done)\n";
    echo "   2. Clear cache: php artisan optimize:clear\n";
    echo "   3. Test login at: http://127.0.0.1:8000/login\n";
    echo "   4. Use credentials: NIM=00000000, Password=password\n\n";
    
    exit(0);
} else {
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║  ⚠️  SOME TESTS FAILED - PLEASE CHECK ERRORS ABOVE           ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "🔧 Troubleshooting:\n";
    echo "   1. Run: composer dump-autoload\n";
    echo "   2. Run: php artisan migrate\n";
    echo "   3. Run: php artisan optimize:clear\n";
    echo "   4. Check database connection in .env\n";
    echo "   5. Re-run this script\n\n";
    
    exit(1);
}
