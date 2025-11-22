<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge Component Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Badge Component Demo</h1>
            <p class="text-gray-600">Testing badge component in different contexts</p>
        </div>

        <!-- All Variants -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">All Variants (Medium Size)</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.badge variant="primary">Primary</x-ui.badge>
                <x-ui.badge variant="secondary">Secondary</x-ui.badge>
                <x-ui.badge variant="success">Success</x-ui.badge>
                <x-ui.badge variant="danger">Danger</x-ui.badge>
                <x-ui.badge variant="warning">Warning</x-ui.badge>
                <x-ui.badge variant="info">Info</x-ui.badge>
                <x-ui.badge variant="gray">Gray</x-ui.badge>
            </div>
        </div>

        <!-- All Sizes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">All Sizes (Primary Variant)</h2>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge variant="primary" size="sm">Small</x-ui.badge>
                <x-ui.badge variant="primary" size="md">Medium</x-ui.badge>
                <x-ui.badge variant="primary" size="lg">Large</x-ui.badge>
            </div>
        </div>

        <!-- Rounded (Pill Shape) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Rounded (Pill Shape)</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.badge variant="primary" rounded>Primary</x-ui.badge>
                <x-ui.badge variant="secondary" rounded>Secondary</x-ui.badge>
                <x-ui.badge variant="success" rounded>Success</x-ui.badge>
                <x-ui.badge variant="danger" rounded>Danger</x-ui.badge>
                <x-ui.badge variant="warning" rounded>Warning</x-ui.badge>
                <x-ui.badge variant="info" rounded>Info</x-ui.badge>
                <x-ui.badge variant="gray" rounded>Gray</x-ui.badge>
            </div>
        </div>

        <!-- In Card Context -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Badge in Card Context</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-gray-900">User Profile</h3>
                        <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
                    </div>
                    <p class="text-sm text-gray-600">John Doe</p>
                    <p class="text-xs text-gray-500">john@example.com</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-gray-900">Order #1234</h3>
                        <x-ui.badge variant="warning" size="sm">Pending</x-ui.badge>
                    </div>
                    <p class="text-sm text-gray-600">Total: $99.99</p>
                    <p class="text-xs text-gray-500">2 items</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-gray-900">Task #567</h3>
                        <x-ui.badge variant="danger" size="sm">Urgent</x-ui.badge>
                    </div>
                    <p class="text-sm text-gray-600">Fix critical bug</p>
                    <p class="text-xs text-gray-500">Due: Today</p>
                </div>
            </div>
        </div>

        <!-- In Table Context -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Badge in Table Context</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">Alice Johnson</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <x-ui.badge variant="primary" size="sm">Admin</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Engineering</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">Bob Smith</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <x-ui.badge variant="secondary" size="sm">Manager</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <x-ui.badge variant="warning" size="sm">Away</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Sales</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">Carol White</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <x-ui.badge variant="info" size="sm">Developer</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <x-ui.badge variant="danger" size="sm">Inactive</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Engineering</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">David Brown</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <x-ui.badge variant="gray" size="sm">Guest</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Support</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- In List Context -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Badge in List Context</h2>
            <ul class="space-y-3">
                <li class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <span class="text-primary-600 font-semibold">AJ</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Alice Johnson</p>
                            <p class="text-sm text-gray-500">alice@example.com</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-ui.badge variant="primary" size="sm" rounded>Admin</x-ui.badge>
                        <x-ui.badge variant="success" size="sm" rounded>Verified</x-ui.badge>
                    </div>
                </li>
                
                <li class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-secondary-100 rounded-full flex items-center justify-center">
                            <span class="text-secondary-600 font-semibold">BS</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Bob Smith</p>
                            <p class="text-sm text-gray-500">bob@example.com</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-ui.badge variant="secondary" size="sm" rounded>Manager</x-ui.badge>
                        <x-ui.badge variant="warning" size="sm" rounded>Pending</x-ui.badge>
                    </div>
                </li>
                
                <li class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-info-100 rounded-full flex items-center justify-center">
                            <span class="text-info-600 font-semibold">CW</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Carol White</p>
                            <p class="text-sm text-gray-500">carol@example.com</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-ui.badge variant="info" size="sm" rounded>Developer</x-ui.badge>
                        <x-ui.badge variant="danger" size="sm" rounded>Blocked</x-ui.badge>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Status Indicators -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Status Indicators</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <span class="text-gray-700">Order Processing</span>
                    <x-ui.badge variant="info">In Progress</x-ui.badge>
                </div>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <span class="text-gray-700">Payment Received</span>
                    <x-ui.badge variant="success">Completed</x-ui.badge>
                </div>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <span class="text-gray-700">Shipment Delayed</span>
                    <x-ui.badge variant="warning">Attention Required</x-ui.badge>
                </div>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <span class="text-gray-700">Order Cancelled</span>
                    <x-ui.badge variant="danger">Failed</x-ui.badge>
                </div>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <span class="text-gray-700">Draft Order</span>
                    <x-ui.badge variant="gray">Not Started</x-ui.badge>
                </div>
            </div>
        </div>

        <!-- Multiple Badges -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Multiple Badges (Tags)</h2>
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Product: Laptop Pro 15"</h3>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <x-ui.badge variant="primary" size="sm">Electronics</x-ui.badge>
                        <x-ui.badge variant="secondary" size="sm">Computers</x-ui.badge>
                        <x-ui.badge variant="info" size="sm">Premium</x-ui.badge>
                        <x-ui.badge variant="success" size="sm">In Stock</x-ui.badge>
                        <x-ui.badge variant="warning" size="sm">Limited</x-ui.badge>
                    </div>
                    <p class="text-sm text-gray-600">High-performance laptop for professionals</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Article: Getting Started with Tailwind</h3>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <x-ui.badge variant="primary" size="sm" rounded>CSS</x-ui.badge>
                        <x-ui.badge variant="secondary" size="sm" rounded>Tailwind</x-ui.badge>
                        <x-ui.badge variant="info" size="sm" rounded>Tutorial</x-ui.badge>
                        <x-ui.badge variant="success" size="sm" rounded>Beginner</x-ui.badge>
                    </div>
                    <p class="text-sm text-gray-600">Learn the basics of Tailwind CSS framework</p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
