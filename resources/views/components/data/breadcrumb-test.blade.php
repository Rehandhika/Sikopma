<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breadcrumb Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto space-y-12">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Breadcrumb Component Tests</h1>
        </div>

        <!-- Test 1: Simple 2-level breadcrumb -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 1: Simple 2-level Breadcrumb</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Dashboard', 'url' => null],
            ]" />
        </div>

        <!-- Test 2: 3-level breadcrumb with links -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 2: 3-level Breadcrumb</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Products', 'url' => '/products'],
                ['label' => 'Electronics', 'url' => null],
            ]" />
        </div>

        <!-- Test 3: 4-level breadcrumb (deeper navigation) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 3: 4-level Breadcrumb</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Settings', 'url' => '/settings'],
                ['label' => 'User Management', 'url' => '/settings/users'],
                ['label' => 'Edit User', 'url' => null],
            ]" />
        </div>

        <!-- Test 4: 5-level breadcrumb (very deep navigation) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 4: 5-level Breadcrumb (Deep Navigation)</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => 'Sales', 'url' => '/reports/sales'],
                ['label' => '2024', 'url' => '/reports/sales/2024'],
                ['label' => 'January', 'url' => null],
            ]" />
        </div>

        <!-- Test 5: Breadcrumb with icons -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 5: Breadcrumb with Icons</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/', 'icon' => 'home'],
                ['label' => 'Users', 'url' => '/users', 'icon' => 'users'],
                ['label' => 'Profile', 'url' => null, 'icon' => 'user'],
            ]" />
        </div>

        <!-- Test 6: Single item (current page only) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 6: Single Item (Current Page Only)</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => null],
            ]" />
        </div>

        <!-- Test 7: Long labels -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 7: Long Labels</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Administration Panel', 'url' => '/admin'],
                ['label' => 'System Configuration Settings', 'url' => '/admin/config'],
                ['label' => 'Advanced Security Options', 'url' => null],
            ]" />
        </div>

        <!-- Test 8: Responsive behavior preview -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 8: Responsive Behavior</h2>
            <p class="text-sm text-gray-600 mb-4">Resize the browser window to see spacing adjustments between items.</p>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/', 'icon' => 'home'],
                ['label' => 'Attendance', 'url' => '/attendance', 'icon' => 'calendar'],
                ['label' => 'History', 'url' => '/attendance/history', 'icon' => 'clock'],
                ['label' => 'Details', 'url' => null, 'icon' => 'document'],
            ]" />
        </div>

        <!-- Test 9: All items are links (no current page) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 9: All Items Are Links</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Products', 'url' => '/products'],
                ['label' => 'Categories', 'url' => '/products/categories'],
            ]" />
        </div>

        <!-- Test 10: Mixed - some with icons, some without -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test 10: Mixed Icons</h2>
            <x-data.breadcrumb :items="[
                ['label' => 'Home', 'url' => '/', 'icon' => 'home'],
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => 'Sales Report', 'url' => '/reports/sales', 'icon' => 'chart-bar'],
                ['label' => 'Details', 'url' => null],
            ]" />
        </div>

        <!-- Visual Guidelines -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-blue-900 mb-4">Visual Guidelines</h2>
            <ul class="space-y-2 text-sm text-blue-800">
                <li><strong>Links:</strong> Should be gray-500 and turn primary-600 on hover</li>
                <li><strong>Current page:</strong> Should be gray-900 (darker, no hover effect)</li>
                <li><strong>Separator:</strong> Chevron-right icon in gray-400</li>
                <li><strong>Spacing:</strong> 1 unit on mobile (space-x-1), 2 units on desktop (md:space-x-2)</li>
                <li><strong>Icons:</strong> Optional, 4x4 size with 1.5 margin-right</li>
                <li><strong>Font:</strong> text-sm font-medium</li>
            </ul>
        </div>

        <!-- Accessibility Notes -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-green-900 mb-4">Accessibility Features</h2>
            <ul class="space-y-2 text-sm text-green-800">
                <li>✓ Uses semantic <code>&lt;nav&gt;</code> element</li>
                <li>✓ Includes <code>aria-label="Breadcrumb"</code></li>
                <li>✓ Uses ordered list <code>&lt;ol&gt;</code> for proper structure</li>
                <li>✓ Current page is not a link (proper semantic)</li>
                <li>✓ Clear visual distinction between links and current page</li>
                <li>✓ Keyboard navigable (tab through links)</li>
            </ul>
        </div>
    </div>
</body>
</html>
