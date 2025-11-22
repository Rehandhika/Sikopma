{{-- 
    Simple Modal Example
    
    This file demonstrates a minimal working example of the modal component.
    Copy this code to any Blade view to test the modal.
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Example</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="max-w-md mx-auto p-8">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Modal Component Demo</h1>
            <p class="text-gray-600 mb-6">Click the button below to open the modal</p>
            
            <x-ui.button 
                variant="primary" 
                @click="$dispatch('open-modal-demo')"
                class="w-full"
            >
                Open Modal
            </x-ui.button>
        </div>
    </div>

    {{-- Modal Component --}}
    <x-ui.modal name="demo" title="Welcome to the Modal!" maxWidth="lg">
        <div class="space-y-4">
            <p class="text-gray-700">
                This is a fully functional modal component with Alpine.js integration.
            </p>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-900 mb-2">Features:</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>✓ Click backdrop to close</li>
                    <li>✓ Press ESC to close</li>
                    <li>✓ Smooth animations</li>
                    <li>✓ Multiple size variants</li>
                    <li>✓ Customizable content</li>
                </ul>
            </div>

            <p class="text-sm text-gray-600">
                Try closing this modal by clicking outside, pressing ESC, or using the buttons below.
            </p>
        </div>

        <x-slot:footer>
            <x-ui.button 
                variant="white" 
                @click="$dispatch('close-modal-demo')"
            >
                Cancel
            </x-ui.button>
            <x-ui.button 
                variant="primary" 
                @click="$dispatch('close-modal-demo')"
            >
                Got it!
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- Alpine.js via CDN (since Livewire is not used in this standalone page) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
