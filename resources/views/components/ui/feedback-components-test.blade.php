<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Components Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto space-y-12">
        
        <!-- Spinner Component Tests -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Spinner Component</h2>
            
            <div class="space-y-6">
                <!-- Size Variants -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Size Variants</h3>
                    <div class="flex items-center space-x-6">
                        <div class="text-center">
                            <x-ui.spinner size="sm" />
                            <p class="text-xs text-gray-500 mt-2">Small</p>
                        </div>
                        <div class="text-center">
                            <x-ui.spinner size="md" />
                            <p class="text-xs text-gray-500 mt-2">Medium (default)</p>
                        </div>
                        <div class="text-center">
                            <x-ui.spinner size="lg" />
                            <p class="text-xs text-gray-500 mt-2">Large</p>
                        </div>
                    </div>
                </div>

                <!-- Color Variants -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Color Variants</h3>
                    <div class="flex items-center space-x-6">
                        <div class="text-center">
                            <x-ui.spinner color="primary" />
                            <p class="text-xs text-gray-500 mt-2">Primary (default)</p>
                        </div>
                        <div class="text-center bg-gray-800 p-4 rounded">
                            <x-ui.spinner color="white" />
                            <p class="text-xs text-white mt-2">White</p>
                        </div>
                        <div class="text-center">
                            <x-ui.spinner color="gray" />
                            <p class="text-xs text-gray-500 mt-2">Gray</p>
                        </div>
                    </div>
                </div>

                <!-- Loading State Example -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Loading State Example</h3>
                    <button class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg">
                        <x-ui.spinner size="sm" color="white" class="mr-2" />
                        Loading...
                    </button>
                </div>
            </div>
        </section>

        <!-- Skeleton Component Tests -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Skeleton Component</h2>
            
            <div class="space-y-6">
                <!-- Text Type -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Text Type (default)</h3>
                    <div class="space-y-2">
                        <x-ui.skeleton type="text" width="w-full" height="h-4" />
                        <x-ui.skeleton type="text" width="w-3/4" height="h-4" />
                        <x-ui.skeleton type="text" width="w-1/2" height="h-4" />
                    </div>
                </div>

                <!-- Circle Type -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Circle Type</h3>
                    <div class="flex items-center space-x-4">
                        <x-ui.skeleton type="circle" width="w-8" height="h-8" />
                        <x-ui.skeleton type="circle" width="w-10" height="h-10" />
                        <x-ui.skeleton type="circle" width="w-12" height="h-12" />
                        <x-ui.skeleton type="circle" width="w-16" height="h-16" />
                    </div>
                </div>

                <!-- Rectangle Type -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Rectangle Type</h3>
                    <div class="space-y-4">
                        <x-ui.skeleton type="rectangle" width="w-full" height="h-32" />
                        <x-ui.skeleton type="rectangle" width="w-64" height="h-24" />
                    </div>
                </div>

                <!-- Loading Card Example -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Loading Card Example</h3>
                    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                        <div class="flex items-center space-x-3">
                            <x-ui.skeleton type="circle" width="w-12" height="h-12" />
                            <div class="flex-1 space-y-2">
                                <x-ui.skeleton type="text" width="w-1/3" height="h-4" />
                                <x-ui.skeleton type="text" width="w-1/4" height="h-3" />
                            </div>
                        </div>
                        <x-ui.skeleton type="rectangle" width="w-full" height="h-40" />
                        <div class="space-y-2">
                            <x-ui.skeleton type="text" width="w-full" height="h-4" />
                            <x-ui.skeleton type="text" width="w-5/6" height="h-4" />
                            <x-ui.skeleton type="text" width="w-4/6" height="h-4" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Avatar Component Tests -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Avatar Component</h2>
            
            <div class="space-y-6">
                <!-- Size Variants -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Size Variants (Initials)</h3>
                    <div class="flex items-center space-x-4">
                        <div class="text-center">
                            <x-ui.avatar name="John Doe" size="sm" />
                            <p class="text-xs text-gray-500 mt-2">Small</p>
                        </div>
                        <div class="text-center">
                            <x-ui.avatar name="Jane Smith" size="md" />
                            <p class="text-xs text-gray-500 mt-2">Medium (default)</p>
                        </div>
                        <div class="text-center">
                            <x-ui.avatar name="Bob Wilson" size="lg" />
                            <p class="text-xs text-gray-500 mt-2">Large</p>
                        </div>
                        <div class="text-center">
                            <x-ui.avatar name="Alice Brown" size="xl" />
                            <p class="text-xs text-gray-500 mt-2">Extra Large</p>
                        </div>
                    </div>
                </div>

                <!-- Initials Fallback -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Initials Fallback</h3>
                    <div class="flex items-center space-x-4">
                        <x-ui.avatar name="John Doe" />
                        <x-ui.avatar name="Jane Smith" />
                        <x-ui.avatar name="Bob Wilson" />
                        <x-ui.avatar name="Alice Brown" />
                        <x-ui.avatar name="Charlie Davis" />
                    </div>
                </div>

                <!-- Single Name -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Single Name</h3>
                    <div class="flex items-center space-x-4">
                        <x-ui.avatar name="Admin" />
                        <x-ui.avatar name="User" />
                        <x-ui.avatar name="Guest" />
                    </div>
                </div>

                <!-- With Image (using placeholder) -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">With Image</h3>
                    <div class="flex items-center space-x-4">
                        <x-ui.avatar 
                            src="https://ui-avatars.com/api/?name=John+Doe&background=6366f1&color=fff" 
                            name="John Doe" 
                            size="md"
                        />
                        <x-ui.avatar 
                            src="https://ui-avatars.com/api/?name=Jane+Smith&background=22c55e&color=fff" 
                            name="Jane Smith" 
                            size="md"
                        />
                        <x-ui.avatar 
                            src="https://ui-avatars.com/api/?name=Bob+Wilson&background=ef4444&color=fff" 
                            name="Bob Wilson" 
                            size="lg"
                        />
                    </div>
                </div>

                <!-- User List Example -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">User List Example</h3>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <x-ui.avatar name="John Doe" size="md" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">John Doe</p>
                                <p class="text-xs text-gray-500">john.doe@example.com</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <x-ui.avatar name="Jane Smith" size="md" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">Jane Smith</p>
                                <p class="text-xs text-gray-500">jane.smith@example.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Combined Loading State Example -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Combined Loading State Example</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Loading State -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Loading State</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <x-ui.skeleton type="circle" width="w-10" height="h-10" />
                            <div class="flex-1 space-y-2">
                                <x-ui.skeleton type="text" width="w-1/2" height="h-4" />
                                <x-ui.skeleton type="text" width="w-1/3" height="h-3" />
                            </div>
                        </div>
                        <x-ui.skeleton type="rectangle" width="w-full" height="h-32" />
                    </div>
                </div>

                <!-- Loaded State -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Loaded State</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <x-ui.avatar name="John Doe" size="md" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">John Doe</p>
                                <p class="text-xs text-gray-500">2 hours ago</p>
                            </div>
                        </div>
                        <div class="bg-gray-100 rounded-lg h-32 flex items-center justify-center">
                            <p class="text-gray-500">Content loaded</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Button with Spinner Example -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Button with Spinner Example</h2>
            
            <div class="flex flex-wrap gap-4">
                <button class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                    <x-ui.spinner size="sm" color="white" class="mr-2" />
                    Saving...
                </button>
                
                <button class="inline-flex items-center px-4 py-2 bg-success-500 hover:bg-success-700 text-white rounded-lg font-semibold transition-colors">
                    <x-ui.spinner size="sm" color="white" class="mr-2" />
                    Processing...
                </button>
                
                <button class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-semibold transition-colors">
                    <x-ui.spinner size="sm" color="gray" class="mr-2" />
                    Loading...
                </button>
            </div>
        </section>

    </div>
</body>
</html>
