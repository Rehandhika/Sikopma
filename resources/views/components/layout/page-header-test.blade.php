<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Header Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto space-y-12">
        
        <!-- Test 1: Basic page header with title only -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Test 1: Basic Title Only</h2>
            <x-layout.page-header 
                title="Dashboard"
            />
        </div>

        <!-- Test 2: Page header with title and description -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Test 2: Title + Description</h2>
            <x-layout.page-header 
                title="User Management"
                description="Manage user accounts, roles, and permissions"
            />
        </div>

        <!-- Test 3: Page header with breadcrumbs -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Test 3: With Breadcrumbs</h2>
            <x-layout.page-header 
                title="Edit Profile"
                description="Update your personal information"
                :breadcrumbs="[
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Settings', 'url' => '/settings'],
                    ['label' => 'Profile']
                ]"
            />
        </div>

        <!-- Test 4: Page header with actions slot -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Test 4: With Action Buttons</h2>
            <x-layout.page-header 
                title="Products"
                description="Manage your product catalog"
            >
                <x-slot:actions>
                    <x-ui.button variant="white" size="md">
                        Export
                    </x-ui.button>
                    <x-ui.button variant="primary" size="md">
                        Add Product
                    </x-ui.button>
                </x-slot:actions>
            </x-layout.page-header>
        </div>

        <!-- Test 5: Complete page header with all features -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Test 5: Complete (Breadcrumbs + Actions)</h2>
            <x-layout.page-header 
                title="Sales Report"
                description="View and analyze sales data for the current period"
                :breadcrumbs="[
                    ['label' => 'Dashboard', 'url' => '/dashboard'],
                    ['label' => 'Reports', 'url' => '/reports'],
                    ['label' => 'Sales']
                ]"
            >
                <x-slot:actions>
                    <x-ui.button variant="white" size="md">
                        <x-ui.icon name="download" class="w-4 h-4 mr-2" />
                        Download
                    </x-ui.button>
                    <x-ui.button variant="primary" size="md">
                        <x-ui.icon name="filter" class="w-4 h-4 mr-2" />
                        Filter
                    </x-ui.button>
                </x-slot:actions>
            </x-layout.page-header>
        </div>

        <!-- Test 6: Long title (test truncation on mobile) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Test 6: Long Title (Responsive)</h2>
            <x-layout.page-header 
                title="This is a Very Long Page Title That Should Truncate on Mobile Devices"
                description="This tests the responsive behavior and text truncation"
                :breadcrumbs="[
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Very Long Category Name', 'url' => '/category'],
                    ['label' => 'Current Page']
                ]"
            >
                <x-slot:actions>
                    <x-ui.button variant="primary" size="md">
                        Action
                    </x-ui.button>
                </x-slot:actions>
            </x-layout.page-header>
        </div>

        <!-- Test 7: Multiple action buttons -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Test 7: Multiple Actions</h2>
            <x-layout.page-header 
                title="Attendance Management"
            >
                <x-slot:actions>
                    <x-ui.button variant="white" size="sm">
                        Export CSV
                    </x-ui.button>
                    <x-ui.button variant="white" size="sm">
                        Print
                    </x-ui.button>
                    <x-ui.button variant="primary" size="sm">
                        New Entry
                    </x-ui.button>
                </x-slot:actions>
            </x-layout.page-header>
        </div>

        <!-- Responsive Test Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Responsive Testing Instructions</h3>
            <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                <li>Resize browser to mobile width (&lt; 640px) - actions should stack below title</li>
                <li>Check that long titles truncate properly on mobile</li>
                <li>Verify breadcrumbs wrap nicely on narrow screens</li>
                <li>Ensure action buttons maintain proper spacing on all screen sizes</li>
                <li>Test at tablet width (768px) - layout should adapt smoothly</li>
            </ul>
        </div>

    </div>
</body>
</html>
