<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Component Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Input Component Demo</h1>
            
            <!-- Basic Input -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Input</h2>
                <x-ui.input 
                    label="Email Address"
                    name="email"
                    type="email"
                    placeholder="Enter your email"
                />
            </div>

            <!-- Required Input -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Required Input</h2>
                <x-ui.input 
                    label="Username"
                    name="username"
                    type="text"
                    placeholder="Enter username"
                    :required="true"
                />
            </div>

            <!-- Input with Help Text -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Input with Help Text</h2>
                <x-ui.input 
                    label="Password"
                    name="password"
                    type="password"
                    placeholder="Enter password"
                    help="Password must be at least 8 characters long"
                />
            </div>

            <!-- Input with Error -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Input with Error State</h2>
                <x-ui.input 
                    label="Phone Number"
                    name="phone"
                    type="tel"
                    placeholder="Enter phone number"
                    error="Phone number is required"
                />
            </div>

            <!-- Input with Leading Icon -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Input with Leading Icon</h2>
                <x-ui.input 
                    label="Search"
                    name="search"
                    type="text"
                    placeholder="Search..."
                    icon="magnifying-glass"
                />
            </div>

            <!-- Input with Icon and Error -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Input with Icon and Error</h2>
                <x-ui.input 
                    label="Email"
                    name="email_error"
                    type="email"
                    placeholder="Enter email"
                    icon="user"
                    error="Invalid email format"
                />
            </div>

            <!-- Disabled Input -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Disabled Input</h2>
                <x-ui.input 
                    label="Disabled Field"
                    name="disabled"
                    type="text"
                    placeholder="This field is disabled"
                    :disabled="true"
                    value="Cannot edit this"
                />
            </div>

            <!-- Different Input Types -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Different Input Types</h2>
                <div class="space-y-4">
                    <x-ui.input 
                        label="Date"
                        name="date"
                        type="date"
                    />
                    <x-ui.input 
                        label="Number"
                        name="number"
                        type="number"
                        placeholder="Enter a number"
                    />
                    <x-ui.input 
                        label="URL"
                        name="url"
                        type="url"
                        placeholder="https://example.com"
                        icon="link"
                    />
                </div>
            </div>

            <!-- Livewire wire:model Test -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Livewire Compatible (wire:model)</h2>
                <x-ui.input 
                    label="Livewire Field"
                    name="livewire_field"
                    type="text"
                    placeholder="This supports wire:model"
                    wire:model="testField"
                    help="This input is compatible with Livewire wire:model directive"
                />
            </div>

            <!-- All Features Combined -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">All Features Combined</h2>
                <x-ui.input 
                    label="Full Featured Input"
                    name="full_featured"
                    type="text"
                    placeholder="Enter value"
                    icon="user"
                    :required="true"
                    help="This input has all features enabled"
                />
            </div>
        </div>
    </div>
</body>
</html>
