<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Button Component Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto space-y-12">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Button Component Demo</h1>
            <p class="text-gray-600">Testing all button variants, sizes, and states</p>
        </div>

        <!-- Variants -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Variants</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary">Primary</x-ui.button>
                <x-ui.button variant="secondary">Secondary</x-ui.button>
                <x-ui.button variant="success">Success</x-ui.button>
                <x-ui.button variant="danger">Danger</x-ui.button>
                <x-ui.button variant="warning">Warning</x-ui.button>
                <x-ui.button variant="info">Info</x-ui.button>
                <x-ui.button variant="white">White</x-ui.button>
                <x-ui.button variant="outline">Outline</x-ui.button>
                <x-ui.button variant="ghost">Ghost</x-ui.button>
            </div>
        </section>

        <!-- Sizes -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Sizes</h2>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button variant="primary" size="sm">Small</x-ui.button>
                <x-ui.button variant="primary" size="md">Medium</x-ui.button>
                <x-ui.button variant="primary" size="lg">Large</x-ui.button>
            </div>
        </section>

        <!-- With Icons -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">With Icons</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" icon="plus">Add Item</x-ui.button>
                <x-ui.button variant="success" icon="check-circle">Save</x-ui.button>
                <x-ui.button variant="danger" icon="trash">Delete</x-ui.button>
                <x-ui.button variant="info" icon="eye">View</x-ui.button>
                <x-ui.button variant="outline" icon="pencil">Edit</x-ui.button>
            </div>
        </section>

        <!-- Loading State -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Loading State</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" :loading="true">Loading...</x-ui.button>
                <x-ui.button variant="secondary" :loading="true">Processing</x-ui.button>
                <x-ui.button variant="success" :loading="true">Saving</x-ui.button>
            </div>
        </section>

        <!-- Disabled State -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Disabled State</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" :disabled="true">Disabled Primary</x-ui.button>
                <x-ui.button variant="secondary" :disabled="true">Disabled Secondary</x-ui.button>
                <x-ui.button variant="success" :disabled="true">Disabled Success</x-ui.button>
                <x-ui.button variant="danger" :disabled="true">Disabled Danger</x-ui.button>
            </div>
        </section>

        <!-- Button Types -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Button Types</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" type="button">Button</x-ui.button>
                <x-ui.button variant="success" type="submit">Submit</x-ui.button>
                <x-ui.button variant="danger" type="reset">Reset</x-ui.button>
            </div>
        </section>

        <!-- As Link -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">As Link (href)</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" href="#">Link Button</x-ui.button>
                <x-ui.button variant="outline" href="#" icon="arrow-right">Go to Page</x-ui.button>
            </div>
        </section>

        <!-- Combination Tests -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Combinations</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" size="sm" icon="plus">Small with Icon</x-ui.button>
                <x-ui.button variant="success" size="lg" icon="check-circle">Large with Icon</x-ui.button>
                <x-ui.button variant="outline" size="sm" :loading="true">Small Loading</x-ui.button>
                <x-ui.button variant="danger" size="lg" :disabled="true" icon="trash">Large Disabled</x-ui.button>
            </div>
        </section>

        <!-- All Variants in All Sizes -->
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">All Variants × All Sizes</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Small</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button variant="primary" size="sm">Primary</x-ui.button>
                        <x-ui.button variant="secondary" size="sm">Secondary</x-ui.button>
                        <x-ui.button variant="success" size="sm">Success</x-ui.button>
                        <x-ui.button variant="danger" size="sm">Danger</x-ui.button>
                        <x-ui.button variant="warning" size="sm">Warning</x-ui.button>
                        <x-ui.button variant="info" size="sm">Info</x-ui.button>
                        <x-ui.button variant="white" size="sm">White</x-ui.button>
                        <x-ui.button variant="outline" size="sm">Outline</x-ui.button>
                        <x-ui.button variant="ghost" size="sm">Ghost</x-ui.button>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Medium</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button variant="primary" size="md">Primary</x-ui.button>
                        <x-ui.button variant="secondary" size="md">Secondary</x-ui.button>
                        <x-ui.button variant="success" size="md">Success</x-ui.button>
                        <x-ui.button variant="danger" size="md">Danger</x-ui.button>
                        <x-ui.button variant="warning" size="md">Warning</x-ui.button>
                        <x-ui.button variant="info" size="md">Info</x-ui.button>
                        <x-ui.button variant="white" size="md">White</x-ui.button>
                        <x-ui.button variant="outline" size="md">Outline</x-ui.button>
                        <x-ui.button variant="ghost" size="md">Ghost</x-ui.button>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Large</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button variant="primary" size="lg">Primary</x-ui.button>
                        <x-ui.button variant="secondary" size="lg">Secondary</x-ui.button>
                        <x-ui.button variant="success" size="lg">Success</x-ui.button>
                        <x-ui.button variant="danger" size="lg">Danger</x-ui.button>
                        <x-ui.button variant="warning" size="lg">Warning</x-ui.button>
                        <x-ui.button variant="info" size="lg">Info</x-ui.button>
                        <x-ui.button variant="white" size="lg">White</x-ui.button>
                        <x-ui.button variant="outline" size="lg">Outline</x-ui.button>
                        <x-ui.button variant="ghost" size="lg">Ghost</x-ui.button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
