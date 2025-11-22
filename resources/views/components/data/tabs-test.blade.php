<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabs Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto space-y-12">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Tabs Component Test</h1>
            <p class="text-gray-600">Testing tabs component with Alpine.js integration</p>
        </div>

        <!-- Test 1: Basic Tabs -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Basic Tabs</h2>
            
            <x-data.tabs>
                <x-slot:tabs>
                    <x-data.tab name="Profile" :index="0" />
                    <x-data.tab name="Settings" :index="1" />
                    <x-data.tab name="Notifications" :index="2" />
                </x-slot:tabs>

                <x-data.tab :index="0" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Profile Information</h3>
                        <p class="text-gray-600">This is the profile tab content. You can add any content here including forms, cards, or other components.</p>
                    </div>
                </x-data.tab>

                <x-data.tab :index="1" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Settings</h3>
                        <p class="text-gray-600">Configure your application settings here. This panel demonstrates smooth transitions when switching tabs.</p>
                    </div>
                </x-data.tab>

                <x-data.tab :index="2" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Notifications</h3>
                        <p class="text-gray-600">Manage your notification preferences. Alpine.js handles the tab switching seamlessly.</p>
                    </div>
                </x-data.tab>
            </x-data.tabs>
        </section>

        <!-- Test 2: Tabs with Icons -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Tabs with Icons</h2>
            
            <x-data.tabs>
                <x-slot:tabs>
                    <x-data.tab name="Dashboard" :index="0" icon="chart-bar" />
                    <x-data.tab name="Users" :index="1" icon="users" />
                    <x-data.tab name="Reports" :index="2" icon="document-text" />
                </x-slot:tabs>

                <x-data.tab :index="0" panel>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-primary-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-primary-600">1,234</div>
                            <div class="text-sm text-gray-600">Total Users</div>
                        </div>
                        <div class="bg-success-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-success-600">$45,678</div>
                            <div class="text-sm text-gray-600">Revenue</div>
                        </div>
                        <div class="bg-warning-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-warning-600">89</div>
                            <div class="text-sm text-gray-600">Pending Tasks</div>
                        </div>
                    </div>
                </x-data.tab>

                <x-data.tab :index="1" panel>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center text-white font-semibold">JD</div>
                                <div>
                                    <div class="font-medium text-gray-900">John Doe</div>
                                    <div class="text-sm text-gray-500">john@example.com</div>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-success-100 text-success-700 text-xs rounded-full">Active</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-secondary-500 rounded-full flex items-center justify-center text-white font-semibold">JS</div>
                                <div>
                                    <div class="font-medium text-gray-900">Jane Smith</div>
                                    <div class="text-sm text-gray-500">jane@example.com</div>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-success-100 text-success-700 text-xs rounded-full">Active</span>
                        </div>
                    </div>
                </x-data.tab>

                <x-data.tab :index="2" panel>
                    <div class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center">
                        <x-ui.icon name="document-text" class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                        <p class="text-gray-600">No reports available yet</p>
                    </div>
                </x-data.tab>
            </x-data.tabs>
        </section>

        <!-- Test 3: Tabs with Badges -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Tabs with Badges</h2>
            
            <x-data.tabs>
                <x-slot:tabs>
                    <x-data.tab name="All" :index="0" badge="24" />
                    <x-data.tab name="Unread" :index="1" badge="5" />
                    <x-data.tab name="Archived" :index="2" badge="19" />
                </x-slot:tabs>

                <x-data.tab :index="0" panel>
                    <div class="space-y-2">
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">All Messages (24)</div>
                            <p class="text-sm text-gray-600 mt-1">Showing all messages including read and unread.</p>
                        </div>
                    </div>
                </x-data.tab>

                <x-data.tab :index="1" panel>
                    <div class="space-y-2">
                        <div class="p-3 bg-primary-50 border-l-4 border-primary-500 rounded-lg">
                            <div class="font-medium text-gray-900">Unread Messages (5)</div>
                            <p class="text-sm text-gray-600 mt-1">You have 5 unread messages that need attention.</p>
                        </div>
                    </div>
                </x-data.tab>

                <x-data.tab :index="2" panel>
                    <div class="space-y-2">
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">Archived Messages (19)</div>
                            <p class="text-sm text-gray-600 mt-1">Messages that have been archived for later reference.</p>
                        </div>
                    </div>
                </x-data.tab>
            </x-data.tabs>
        </section>

        <!-- Test 4: Tabs with Icons and Badges -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">4. Tabs with Icons and Badges</h2>
            
            <x-data.tabs :defaultTab="1">
                <x-slot:tabs>
                    <x-data.tab name="Inbox" :index="0" icon="inbox" badge="12" />
                    <x-data.tab name="Important" :index="1" icon="star" badge="3" />
                    <x-data.tab name="Sent" :index="2" icon="paper-airplane" />
                </x-slot:tabs>

                <x-data.tab :index="0" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Inbox (12 messages)</h3>
                        <p class="text-gray-600">Your inbox contains 12 new messages.</p>
                    </div>
                </x-data.tab>

                <x-data.tab :index="1" panel>
                    <div class="p-4 bg-warning-50 rounded-lg border-l-4 border-warning-500">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Important (3 messages)</h3>
                        <p class="text-gray-600">These messages are marked as important and require immediate attention.</p>
                    </div>
                </x-data.tab>

                <x-data.tab :index="2" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Sent Messages</h3>
                        <p class="text-gray-600">View all messages you have sent.</p>
                    </div>
                </x-data.tab>
            </x-data.tabs>
        </section>

        <!-- Test 5: Complex Content with Forms -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">5. Tabs with Form Content</h2>
            
            <x-data.tabs>
                <x-slot:tabs>
                    <x-data.tab name="Personal Info" :index="0" icon="user" />
                    <x-data.tab name="Security" :index="1" icon="lock-closed" />
                    <x-data.tab name="Preferences" :index="2" icon="cog" />
                </x-slot:tabs>

                <x-data.tab :index="0" panel>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Enter your name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Enter your email">
                        </div>
                        <button class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </x-data.tab>

                <x-data.tab :index="1" panel>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <button class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            Update Password
                        </button>
                    </div>
                </x-data.tab>

                <x-data.tab :index="2" panel>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">Email Notifications</div>
                                <div class="text-sm text-gray-500">Receive email updates</div>
                            </div>
                            <input type="checkbox" class="w-5 h-5 text-primary-600 rounded focus:ring-primary-500">
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">Push Notifications</div>
                                <div class="text-sm text-gray-500">Receive push notifications</div>
                            </div>
                            <input type="checkbox" class="w-5 h-5 text-primary-600 rounded focus:ring-primary-500" checked>
                        </div>
                    </div>
                </x-data.tab>
            </x-data.tabs>
        </section>

        <!-- Test 6: Many Tabs (Overflow Test) -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">6. Many Tabs (Responsive Test)</h2>
            
            <x-data.tabs>
                <x-slot:tabs>
                    <x-data.tab name="Tab 1" :index="0" />
                    <x-data.tab name="Tab 2" :index="1" />
                    <x-data.tab name="Tab 3" :index="2" />
                    <x-data.tab name="Tab 4" :index="3" />
                    <x-data.tab name="Tab 5" :index="4" />
                    <x-data.tab name="Tab 6" :index="5" />
                </x-slot:tabs>

                <x-data.tab :index="0" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">Content for Tab 1</div>
                </x-data.tab>
                <x-data.tab :index="1" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">Content for Tab 2</div>
                </x-data.tab>
                <x-data.tab :index="2" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">Content for Tab 3</div>
                </x-data.tab>
                <x-data.tab :index="3" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">Content for Tab 4</div>
                </x-data.tab>
                <x-data.tab :index="4" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">Content for Tab 5</div>
                </x-data.tab>
                <x-data.tab :index="5" panel>
                    <div class="p-4 bg-gray-50 rounded-lg">Content for Tab 6</div>
                </x-data.tab>
            </x-data.tabs>
        </section>

        <!-- Features Summary -->
        <section class="bg-primary-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">✅ Component Features</h2>
            <ul class="space-y-2 text-gray-700">
                <li class="flex items-start">
                    <span class="text-primary-600 mr-2">•</span>
                    <span><strong>Alpine.js Integration:</strong> Smooth tab switching with reactive state management</span>
                </li>
                <li class="flex items-start">
                    <span class="text-primary-600 mr-2">•</span>
                    <span><strong>Active State Styling:</strong> Clear visual indication of active tab with border and color changes</span>
                </li>
                <li class="flex items-start">
                    <span class="text-primary-600 mr-2">•</span>
                    <span><strong>Icon Support:</strong> Optional icons for better visual hierarchy</span>
                </li>
                <li class="flex items-start">
                    <span class="text-primary-600 mr-2">•</span>
                    <span><strong>Badge Support:</strong> Display counts or notifications on tabs</span>
                </li>
                <li class="flex items-start">
                    <span class="text-primary-600 mr-2">•</span>
                    <span><strong>Smooth Transitions:</strong> Fade and slide animations when switching panels</span>
                </li>
                <li class="flex items-start">
                    <span class="text-primary-600 mr-2">•</span>
                    <span><strong>Default Tab:</strong> Configurable initial active tab</span>
                </li>
                <li class="flex items-start">
                    <span class="text-primary-600 mr-2">•</span>
                    <span><strong>Accessibility:</strong> Proper ARIA attributes and semantic HTML</span>
                </li>
                <li class="flex items-start">
                    <span class="text-primary-600 mr-2">•</span>
                    <span><strong>Responsive:</strong> Works well on all screen sizes</span>
                </li>
            </ul>
        </section>
    </div>
</body>
</html>
