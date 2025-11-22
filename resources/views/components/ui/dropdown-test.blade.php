<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto space-y-12">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Dropdown Component Tests</h1>
            <p class="text-gray-600">Testing dropdown component with Alpine.js integration</p>
        </div>

        <!-- Test 1: Basic Dropdown (Right Aligned) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Basic Dropdown (Right Aligned)</h2>
            <div class="flex justify-end">
                <x-ui.dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-ui.button variant="primary">
                            Options
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </x-ui.button>
                    </x-slot:trigger>

                    <x-ui.dropdown-item href="#profile">
                        Profile
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#settings">
                        Settings
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#logout">
                        Logout
                    </x-ui.dropdown-item>
                </x-ui.dropdown>
            </div>
        </div>

        <!-- Test 2: Left Aligned Dropdown -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Left Aligned Dropdown</h2>
            <div class="flex justify-start">
                <x-ui.dropdown align="left" width="48">
                    <x-slot:trigger>
                        <x-ui.button variant="secondary">
                            Actions
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </x-ui.button>
                    </x-slot:trigger>

                    <x-ui.dropdown-item href="#edit">
                        Edit
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#duplicate">
                        Duplicate
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#delete" class="text-danger-600 hover:bg-danger-50">
                        Delete
                    </x-ui.dropdown-item>
                </x-ui.dropdown>
            </div>
        </div>

        <!-- Test 3: Dropdown with Icons -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Dropdown with Icons</h2>
            <div class="flex justify-center">
                <x-ui.dropdown align="right" width="56">
                    <x-slot:trigger>
                        <x-ui.button variant="white">
                            Menu with Icons
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </x-ui.button>
                    </x-slot:trigger>

                    <x-ui.dropdown-item href="#dashboard" icon="check-circle">
                        Dashboard
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#inbox" icon="inbox">
                        Inbox
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#info" icon="information-circle">
                        Information
                    </x-ui.dropdown-item>
                </x-ui.dropdown>
            </div>
        </div>

        <!-- Test 4: Different Width Variants -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">4. Width Variants</h2>
            <div class="flex justify-around">
                <!-- Width 48 -->
                <x-ui.dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-ui.button variant="info" size="sm">
                            Width 48
                        </x-ui.button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="#">Option 1</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#">Option 2</x-ui.dropdown-item>
                </x-ui.dropdown>

                <!-- Width 56 -->
                <x-ui.dropdown align="right" width="56">
                    <x-slot:trigger>
                        <x-ui.button variant="warning" size="sm">
                            Width 56
                        </x-ui.button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="#">Option 1</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#">Option 2</x-ui.dropdown-item>
                </x-ui.dropdown>

                <!-- Width 64 -->
                <x-ui.dropdown align="right" width="64">
                    <x-slot:trigger>
                        <x-ui.button variant="success" size="sm">
                            Width 64
                        </x-ui.button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="#">Option 1</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#">Option 2</x-ui.dropdown-item>
                </x-ui.dropdown>
            </div>
        </div>

        <!-- Test 5: Dropdown with Button Actions (no href) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">5. Dropdown with Button Actions</h2>
            <div class="flex justify-center">
                <x-ui.dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-ui.button variant="outline">
                            Actions
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </x-ui.button>
                    </x-slot:trigger>

                    <x-ui.dropdown-item @click="alert('Save clicked')">
                        Save
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item @click="alert('Export clicked')">
                        Export
                    </x-ui.dropdown-item>
                    <x-ui.dropdown-item @click="alert('Print clicked')">
                        Print
                    </x-ui.dropdown-item>
                </x-ui.dropdown>
            </div>
        </div>

        <!-- Test 6: Navigation Dropdown Example -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">6. Navigation Dropdown Example</h2>
            <div class="flex items-center justify-between bg-gray-800 text-white p-4 rounded-lg">
                <div class="text-lg font-semibold">SIKOPMA</div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="hover:text-gray-300">Dashboard</a>
                    <a href="#" class="hover:text-gray-300">Reports</a>
                    
                    <!-- User Dropdown -->
                    <x-ui.dropdown align="right" width="48">
                        <x-slot:trigger>
                            <button class="flex items-center space-x-2 hover:text-gray-300">
                                <x-ui.avatar name="John Doe" size="sm" />
                                <span>John Doe</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </x-slot:trigger>

                        <x-ui.dropdown-item href="#profile" icon="check-circle">
                            My Profile
                        </x-ui.dropdown-item>
                        <x-ui.dropdown-item href="#settings" icon="information-circle">
                            Settings
                        </x-ui.dropdown-item>
                        <div class="border-t border-gray-200 my-1"></div>
                        <x-ui.dropdown-item href="#logout" icon="x-circle" class="text-danger-600 hover:bg-danger-50">
                            Logout
                        </x-ui.dropdown-item>
                    </x-ui.dropdown>
                </div>
            </div>
        </div>

        <!-- Test 7: Click-Away Functionality Test -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">7. Click-Away Test</h2>
            <p class="text-sm text-gray-600 mb-4">Open the dropdown and click anywhere outside to test click-away functionality</p>
            <div class="flex justify-center">
                <x-ui.dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-ui.button variant="primary">
                            Test Click-Away
                        </x-ui.button>
                    </x-slot:trigger>

                    <x-ui.dropdown-item href="#">Item 1</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#">Item 2</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#">Item 3</x-ui.dropdown-item>
                </x-ui.dropdown>
            </div>
        </div>

        <!-- Test 8: Multiple Dropdowns -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">8. Multiple Dropdowns (Independent)</h2>
            <p class="text-sm text-gray-600 mb-4">Each dropdown should work independently</p>
            <div class="flex justify-around">
                <x-ui.dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-ui.button variant="primary">
                            Dropdown 1
                        </x-ui.button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="#">Option A</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#">Option B</x-ui.dropdown-item>
                </x-ui.dropdown>

                <x-ui.dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-ui.button variant="secondary">
                            Dropdown 2
                        </x-ui.button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="#">Option X</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#">Option Y</x-ui.dropdown-item>
                </x-ui.dropdown>

                <x-ui.dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-ui.button variant="success">
                            Dropdown 3
                        </x-ui.button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="#">Option 1</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="#">Option 2</x-ui.dropdown-item>
                </x-ui.dropdown>
            </div>
        </div>

        <!-- Feature Checklist -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-blue-900 mb-4">✓ Feature Checklist</h2>
            <ul class="space-y-2 text-sm text-blue-800">
                <li>✓ Alignment options (left, right)</li>
                <li>✓ Width variants (48, 56, 64)</li>
                <li>✓ Click-away to close functionality (@click.away)</li>
                <li>✓ Smooth transitions (enter/leave animations)</li>
                <li>✓ Support for links (href) and buttons</li>
                <li>✓ Icon support in dropdown items</li>
                <li>✓ Custom styling support via attributes</li>
                <li>✓ Multiple independent dropdowns</li>
                <li>✓ Navigation menu integration</li>
                <li>✓ Action menu integration</li>
            </ul>
        </div>
    </div>
</body>
</html>
