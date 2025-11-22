<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Component Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Alert Component Demo</h1>
            <p class="text-gray-600">Testing all alert variants with different configurations</p>
        </div>

        <!-- Success Alerts -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Success Alerts</h2>
            
            <x-ui.alert variant="success">
                <strong class="font-semibold">Success!</strong> Your changes have been saved successfully.
            </x-ui.alert>

            <x-ui.alert variant="success" :dismissible="true">
                <strong class="font-semibold">Well done!</strong> You successfully completed the task.
            </x-ui.alert>

            <x-ui.alert variant="success" :icon="false">
                <strong class="font-semibold">No icon:</strong> This alert doesn't have an icon.
            </x-ui.alert>
        </section>

        <!-- Danger Alerts -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Danger Alerts</h2>
            
            <x-ui.alert variant="danger">
                <strong class="font-semibold">Error!</strong> There was a problem processing your request.
            </x-ui.alert>

            <x-ui.alert variant="danger" :dismissible="true">
                <div>
                    <strong class="font-semibold">Validation failed!</strong>
                    <ul class="mt-2 ml-4 list-disc text-sm">
                        <li>Email field is required</li>
                        <li>Password must be at least 8 characters</li>
                    </ul>
                </div>
            </x-ui.alert>
        </section>

        <!-- Warning Alerts -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Warning Alerts</h2>
            
            <x-ui.alert variant="warning">
                <strong class="font-semibold">Warning!</strong> Your session will expire in 5 minutes.
            </x-ui.alert>

            <x-ui.alert variant="warning" :dismissible="true">
                <strong class="font-semibold">Attention needed:</strong> Please review your profile information before proceeding.
            </x-ui.alert>
        </section>

        <!-- Info Alerts -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Info Alerts</h2>
            
            <x-ui.alert variant="info">
                <strong class="font-semibold">Info:</strong> New features have been added to the dashboard.
            </x-ui.alert>

            <x-ui.alert variant="info" :dismissible="true">
                <div>
                    <strong class="font-semibold">Did you know?</strong>
                    <p class="mt-1 text-sm">You can customize your dashboard layout by dragging and dropping widgets.</p>
                </div>
            </x-ui.alert>
        </section>

        <!-- Complex Content -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Complex Content</h2>
            
            <x-ui.alert variant="success" :dismissible="true">
                <div class="space-y-2">
                    <div class="font-semibold">Order Completed Successfully!</div>
                    <p class="text-sm">Your order #12345 has been processed and will be shipped within 2-3 business days.</p>
                    <div class="mt-3 flex gap-3">
                        <button class="text-sm font-medium underline hover:no-underline">View Order</button>
                        <button class="text-sm font-medium underline hover:no-underline">Track Shipment</button>
                    </div>
                </div>
            </x-ui.alert>

            <x-ui.alert variant="warning" :dismissible="true">
                <div class="space-y-2">
                    <div class="font-semibold">Storage Almost Full</div>
                    <p class="text-sm">You're using 95% of your storage quota. Consider upgrading your plan or deleting unused files.</p>
                    <div class="mt-3">
                        <button class="px-3 py-1.5 text-sm font-medium bg-warning-600 text-white rounded-lg hover:bg-warning-700 transition-colors">
                            Upgrade Now
                        </button>
                    </div>
                </div>
            </x-ui.alert>
        </section>

        <!-- Different Page Contexts -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">In Different Contexts</h2>
            
            <!-- In a card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Form Submission</h3>
                <x-ui.alert variant="danger" :dismissible="true" class="mb-4">
                    <strong class="font-semibold">Please fix the following errors:</strong>
                    <ul class="mt-2 ml-4 list-disc text-sm">
                        <li>Username is already taken</li>
                        <li>Email format is invalid</li>
                    </ul>
                </x-ui.alert>
                <form class="space-y-4">
                    <input type="text" placeholder="Username" class="w-full px-3 py-2 border rounded-lg">
                    <input type="email" placeholder="Email" class="w-full px-3 py-2 border rounded-lg">
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg">Submit</button>
                </form>
            </div>

            <!-- In a narrow container -->
            <div class="max-w-md">
                <x-ui.alert variant="info" :dismissible="true">
                    <strong class="font-semibold">Tip:</strong> This alert works well in narrow containers too.
                </x-ui.alert>
            </div>
        </section>

        <!-- Transition Demo -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Transition Demo</h2>
            <p class="text-sm text-gray-600">Click the dismiss button to see the smooth transition animation</p>
            
            <div x-data="{ showAlert: true }">
                <button 
                    @click="showAlert = true" 
                    x-show="!showAlert"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Show Alert Again
                </button>
                
                <div x-show="showAlert">
                    <x-ui.alert variant="success" :dismissible="true">
                        <strong class="font-semibold">Dismissible Alert:</strong> Click the X button to see the smooth fade-out animation.
                    </x-ui.alert>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
