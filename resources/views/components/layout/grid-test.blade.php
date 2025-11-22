<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grid Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto space-y-12">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Grid Layout Component Test</h1>
            <p class="text-gray-600">Testing responsive grid behavior at all breakpoints</p>
        </div>

        <!-- Test 1: Single Column Grid -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">1. Single Column Grid (cols="1")</h2>
            <p class="text-sm text-gray-600">Should remain single column at all breakpoints</p>
            
            <x-layout.grid cols="1" gap="4">
                <div class="bg-primary-100 border-2 border-primary-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-primary-900">Item 1</p>
                </div>
                <div class="bg-primary-100 border-2 border-primary-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-primary-900">Item 2</p>
                </div>
                <div class="bg-primary-100 border-2 border-primary-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-primary-900">Item 3</p>
                </div>
            </x-layout.grid>
        </section>

        <!-- Test 2: Two Column Grid -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">2. Two Column Grid (cols="2")</h2>
            <p class="text-sm text-gray-600">Mobile: 1 col | Tablet (md): 2 cols</p>
            
            <x-layout.grid cols="2" gap="6">
                <div class="bg-secondary-100 border-2 border-secondary-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-secondary-900">Item 1</p>
                </div>
                <div class="bg-secondary-100 border-2 border-secondary-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-secondary-900">Item 2</p>
                </div>
                <div class="bg-secondary-100 border-2 border-secondary-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-secondary-900">Item 3</p>
                </div>
                <div class="bg-secondary-100 border-2 border-secondary-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-secondary-900">Item 4</p>
                </div>
            </x-layout.grid>
        </section>

        <!-- Test 3: Three Column Grid -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">3. Three Column Grid (cols="3")</h2>
            <p class="text-sm text-gray-600">Mobile: 1 col | Tablet (md): 2 cols | Desktop (lg): 3 cols</p>
            
            <x-layout.grid cols="3" gap="6">
                <div class="bg-blue-100 border-2 border-blue-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-blue-900">Item 1</p>
                </div>
                <div class="bg-blue-100 border-2 border-blue-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-blue-900">Item 2</p>
                </div>
                <div class="bg-blue-100 border-2 border-blue-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-blue-900">Item 3</p>
                </div>
                <div class="bg-blue-100 border-2 border-blue-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-blue-900">Item 4</p>
                </div>
                <div class="bg-blue-100 border-2 border-blue-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-blue-900">Item 5</p>
                </div>
                <div class="bg-blue-100 border-2 border-blue-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-blue-900">Item 6</p>
                </div>
            </x-layout.grid>
        </section>

        <!-- Test 4: Four Column Grid -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">4. Four Column Grid (cols="4")</h2>
            <p class="text-sm text-gray-600">Mobile: 1 col | Tablet (md): 2 cols | Desktop (lg): 3 cols | XL (xl): 4 cols</p>
            
            <x-layout.grid cols="4" gap="6">
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-green-900">Item 1</p>
                </div>
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-green-900">Item 2</p>
                </div>
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-green-900">Item 3</p>
                </div>
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-green-900">Item 4</p>
                </div>
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-green-900">Item 5</p>
                </div>
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-green-900">Item 6</p>
                </div>
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-green-900">Item 7</p>
                </div>
                <div class="bg-green-100 border-2 border-green-300 rounded-lg p-6 text-center">
                    <p class="font-semibold text-green-900">Item 8</p>
                </div>
            </x-layout.grid>
        </section>

        <!-- Test 5: Different Gap Sizes -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">5. Gap Size Variations (cols="3")</h2>
            
            <div class="space-y-8">
                <div>
                    <p class="text-sm text-gray-600 mb-2">Gap: 2 (0.5rem / 8px)</p>
                    <x-layout.grid cols="3" gap="2">
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 2</p>
                        </div>
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 2</p>
                        </div>
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 2</p>
                        </div>
                    </x-layout.grid>
                </div>

                <div>
                    <p class="text-sm text-gray-600 mb-2">Gap: 4 (1rem / 16px)</p>
                    <x-layout.grid cols="3" gap="4">
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 4</p>
                        </div>
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 4</p>
                        </div>
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 4</p>
                        </div>
                    </x-layout.grid>
                </div>

                <div>
                    <p class="text-sm text-gray-600 mb-2">Gap: 8 (2rem / 32px)</p>
                    <x-layout.grid cols="3" gap="8">
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 8</p>
                        </div>
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 8</p>
                        </div>
                        <div class="bg-purple-100 border-2 border-purple-300 rounded-lg p-4 text-center text-sm">
                            <p class="font-semibold text-purple-900">Gap 8</p>
                        </div>
                    </x-layout.grid>
                </div>
            </div>
        </section>

        <!-- Test 6: Real-world Example with Cards -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">6. Real-world Example: Product Cards</h2>
            <p class="text-sm text-gray-600">Using grid with actual card components</p>
            
            <x-layout.grid cols="3" gap="6">
                @for($i = 1; $i <= 6; $i++)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                        <span class="text-white text-4xl font-bold">{{ $i }}</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Product {{ $i }}</h3>
                        <p class="text-sm text-gray-600 mb-4">This is a sample product description for testing the grid layout.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-primary-600">Rp 100.000</span>
                            <button class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition-colors">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                @endfor
            </x-layout.grid>
        </section>

        <!-- Test 7: Custom Classes -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">7. Custom Classes Test</h2>
            <p class="text-sm text-gray-600">Grid with additional custom classes</p>
            
            <x-layout.grid cols="4" gap="4" class="bg-gray-100 p-6 rounded-lg">
                <div class="bg-yellow-100 border-2 border-yellow-300 rounded-lg p-4 text-center">
                    <p class="font-semibold text-yellow-900">Custom 1</p>
                </div>
                <div class="bg-yellow-100 border-2 border-yellow-300 rounded-lg p-4 text-center">
                    <p class="font-semibold text-yellow-900">Custom 2</p>
                </div>
                <div class="bg-yellow-100 border-2 border-yellow-300 rounded-lg p-4 text-center">
                    <p class="font-semibold text-yellow-900">Custom 3</p>
                </div>
                <div class="bg-yellow-100 border-2 border-yellow-300 rounded-lg p-4 text-center">
                    <p class="font-semibold text-yellow-900">Custom 4</p>
                </div>
            </x-layout.grid>
        </section>

        <!-- Breakpoint Reference -->
        <section class="mt-12 p-6 bg-blue-50 border-2 border-blue-200 rounded-lg">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Breakpoint Reference</h2>
            <div class="space-y-2 text-sm">
                <p><strong>Mobile (default):</strong> &lt; 768px - Single column for most grids</p>
                <p><strong>Tablet (md):</strong> ≥ 768px - 2 columns for cols="2", "3", "4"</p>
                <p><strong>Desktop (lg):</strong> ≥ 1024px - 3 columns for cols="3", "4"</p>
                <p><strong>XL Desktop (xl):</strong> ≥ 1280px - 4 columns for cols="4"</p>
            </div>
            
            <div class="mt-4 p-4 bg-white rounded border border-blue-300">
                <p class="text-sm font-semibold mb-2">Current Breakpoint:</p>
                <div class="flex gap-2">
                    <span class="inline-block md:hidden px-3 py-1 bg-green-500 text-white rounded-full text-xs font-semibold">Mobile</span>
                    <span class="hidden md:inline-block lg:hidden px-3 py-1 bg-green-500 text-white rounded-full text-xs font-semibold">Tablet (md)</span>
                    <span class="hidden lg:inline-block xl:hidden px-3 py-1 bg-green-500 text-white rounded-full text-xs font-semibold">Desktop (lg)</span>
                    <span class="hidden xl:inline-block px-3 py-1 bg-green-500 text-white rounded-full text-xs font-semibold">XL Desktop (xl)</span>
                </div>
            </div>
        </section>

    </div>
</body>
</html>
