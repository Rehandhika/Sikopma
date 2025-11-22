<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Components Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- Spinner Component Tests -->
        <div class="bg-white rounded-lg shadow-md p-6">
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
                            <p class="text-xs text-gray-500 mt-2">Medium</p>
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
                            <p class="text-xs text-gray-500 mt-2">Primary</p>
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
        </div>

        <!-- Skeleton Component Tests -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Skeleton Component</h2>
            
            <div class="space-y-6">
                <!-- Text Type -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Text Type</h3>
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

                <!-- Card Loading Example -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Card Loading Example</h3>
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
        </div>

        <!-- Avatar Component Tests -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Avatar Component</h2>
            
            <div class="space-y-6">
                <!-- Size Variants -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Size Variants</h3>
                    <div class="flex items-center space-x-4">
                        <div class="text-center">
                            <x-ui.avatar name="John Doe" size="sm" />
                            <p class="text-xs text-gray-500 mt-2">Small</p>
                        </div>
                        <div class="text-center">
                            <x-ui.avatar name="John Doe" size="md" />
                            <p class="text-xs text-gray-500 mt-2">Medium</p>
                        </div>
                        <div class="text-center">
                            <x-ui.avatar name="John Doe" size="lg" />
                            <p class="text-xs text-gray-500 mt-2">Large</p>
                        </div>
                        <div class="text-center">
                            <x-ui.avatar name="John Doe" size="xl" />
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
                        <x-ui.avatar name="Alice Johnson" />
                        <x-ui.avatar name="Charlie Brown" />
                    </div>
                </div>

                <!-- With Image -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">With Image</h3>
                    <div class="flex items-center space-x-4">
                        <x-ui.avatar 
                            src="https://ui-avatars.com/api/?name=John+Doe&background=6366f1&color=fff" 
                            name="John Doe" 
                        />
                        <x-ui.avatar 
                            src="https://ui-avatars.com/api/?name=Jane+Smith&background=22c55e&color=fff" 
                            name="Jane Smith" 
                        />
                        <x-ui.avatar 
                            src="https://ui-avatars.com/api/?name=Bob+Wilson&background=ef4444&color=fff" 
                            name="Bob Wilson" 
                        />
                    </div>
                </div>

                <!-- User List Example -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">User List Example</h3>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg">
                            <x-ui.avatar name="John Doe" size="md" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">John Doe</p>
                                <p class="text-xs text-gray-500">john.doe@example.com</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg">
                            <x-ui.avatar name="Jane Smith" size="md" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">Jane Smith</p>
                                <p class="text-xs text-gray-500">jane.smith@example.com</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg">
                            <x-ui.avatar name="Bob Wilson" size="md" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">Bob Wilson</p>
                                <p class="text-xs text-gray-500">bob.wilson@example.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Combined Loading State Example -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Combined Loading State Example</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Loading Card -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-center py-8">
                        <div class="text-center">
                            <x-ui.spinner size="lg" class="mx-auto mb-3" />
                            <p class="text-sm text-gray-600">Loading data...</p>
                        </div>
                    </div>
                </div>

                <!-- Skeleton Card -->
                <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                    <div class="flex items-center space-x-3">
                        <x-ui.skeleton type="circle" width="w-10" height="h-10" />
                        <div class="flex-1 space-y-2">
                            <x-ui.skeleton type="text" width="w-2/3" height="h-4" />
                            <x-ui.skeleton type="text" width="w-1/2" height="h-3" />
                        </div>
                    </div>
                    <x-ui.skeleton type="rectangle" width="w-full" height="h-32" />
                    <div class="space-y-2">
                        <x-ui.skeleton type="text" width="w-full" height="h-3" />
                        <x-ui.skeleton type="text" width="w-4/5" height="h-3" />
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
