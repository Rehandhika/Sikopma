<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Modal Component Tests</h1>

        <!-- Test 1: Basic Modal -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 1: Basic Modal (Medium Width)</h2>
            <x-ui.button variant="primary" @click="$dispatch('open-modal-basic')">
                Open Basic Modal
            </x-ui.button>

            <x-ui.modal name="basic" title="Basic Modal">
                <p class="text-gray-700">This is a basic modal with default settings.</p>
                <p class="text-gray-700 mt-2">Click the backdrop or press ESC to close.</p>
            </x-ui.modal>
        </div>

        <!-- Test 2: Modal with Footer -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 2: Modal with Footer Actions</h2>
            <x-ui.button variant="secondary" @click="$dispatch('open-modal-footer')">
                Open Modal with Footer
            </x-ui.button>

            <x-ui.modal name="footer" title="Confirm Action">
                <p class="text-gray-700">Are you sure you want to proceed with this action?</p>
                <p class="text-gray-600 text-sm mt-2">This action cannot be undone.</p>

                <x-slot:footer>
                    <x-ui.button variant="white" @click="$dispatch('close-modal-footer')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button variant="danger" @click="$dispatch('close-modal-footer')">
                        Confirm
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </div>

        <!-- Test 3: Small Modal -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 3: Small Modal</h2>
            <x-ui.button variant="info" @click="$dispatch('open-modal-small')">
                Open Small Modal
            </x-ui.button>

            <x-ui.modal name="small" title="Small Modal" maxWidth="sm">
                <p class="text-gray-700">This is a small modal (max-w-sm).</p>
            </x-ui.modal>
        </div>

        <!-- Test 4: Large Modal -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 4: Large Modal</h4>
            <x-ui.button variant="success" @click="$dispatch('open-modal-large')">
                Open Large Modal
            </x-ui.button>

            <x-ui.modal name="large" title="Large Modal" maxWidth="xl">
                <p class="text-gray-700 mb-4">This is a large modal (max-w-xl).</p>
                <p class="text-gray-600">It can contain more content and wider forms.</p>
            </x-ui.modal>
        </div>

        <!-- Test 5: Extra Large Modal -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 5: Extra Large Modal (2xl)</h2>
            <x-ui.button variant="warning" @click="$dispatch('open-modal-2xl')">
                Open 2XL Modal
            </x-ui.button>

            <x-ui.modal name="2xl" title="Extra Large Modal" maxWidth="2xl">
                <p class="text-gray-700 mb-4">This is an extra large modal (max-w-2xl).</p>
                <p class="text-gray-600">Perfect for complex forms or detailed content.</p>
            </x-ui.modal>
        </div>

        <!-- Test 6: Modal with Form -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 6: Modal with Form</h2>
            <x-ui.button variant="primary" @click="$dispatch('open-modal-form')">
                Open Form Modal
            </x-ui.button>

            <x-ui.modal name="form" title="Create New User" maxWidth="lg">
                <form class="space-y-4">
                    <x-ui.input 
                        label="Full Name" 
                        name="name" 
                        placeholder="Enter full name"
                        required
                    />
                    
                    <x-ui.input 
                        label="Email Address" 
                        name="email" 
                        type="email"
                        placeholder="user@example.com"
                        required
                    />
                    
                    <x-ui.select 
                        label="Role" 
                        name="role"
                        :options="['admin' => 'Administrator', 'user' => 'User', 'guest' => 'Guest']"
                        required
                    />
                    
                    <x-ui.textarea 
                        label="Bio" 
                        name="bio"
                        placeholder="Tell us about yourself..."
                        rows="3"
                    />
                </form>

                <x-slot:footer>
                    <x-ui.button variant="white" @click="$dispatch('close-modal-form')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button variant="primary" type="submit">
                        Create User
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </div>

        <!-- Test 7: Non-closeable Modal -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 7: Non-closeable Modal</h2>
            <x-ui.button variant="danger" @click="$dispatch('open-modal-noncloseable')">
                Open Non-closeable Modal
            </x-ui.button>

            <x-ui.modal name="noncloseable" title="Important Notice" :closeable="false">
                <p class="text-gray-700 mb-4">This modal cannot be closed by clicking the backdrop or pressing ESC.</p>
                <p class="text-gray-600">You must use the button below to close it.</p>

                <x-slot:footer>
                    <x-ui.button variant="primary" @click="$dispatch('close-modal-noncloseable')">
                        I Understand
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </div>

        <!-- Test 8: Modal without Title -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 8: Modal without Title</h2>
            <x-ui.button variant="ghost" @click="$dispatch('open-modal-notitle')">
                Open Modal without Title
            </x-ui.button>

            <x-ui.modal name="notitle">
                <div class="text-center">
                    <x-ui.icon name="check-circle" class="w-16 h-16 text-success-500 mx-auto mb-4" />
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Success!</h3>
                    <p class="text-gray-600">Your action has been completed successfully.</p>
                </div>

                <x-slot:footer>
                    <x-ui.button variant="primary" @click="$dispatch('close-modal-notitle')" class="w-full">
                        Close
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </div>

        <!-- Test 9: Modal with Rich Content -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test 9: Modal with Rich Content</h2>
            <x-ui.button variant="outline" @click="$dispatch('open-modal-rich')">
                Open Rich Content Modal
            </x-ui.button>

            <x-ui.modal name="rich" title="Product Details" maxWidth="2xl">
                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-24 h-24 bg-gray-200 rounded-lg flex-shrink-0"></div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900">Product Name</h4>
                            <p class="text-gray-600 text-sm mt-1">SKU: PRD-12345</p>
                            <div class="flex items-center mt-2 space-x-2">
                                <x-ui.badge variant="success">In Stock</x-ui.badge>
                                <x-ui.badge variant="info">New</x-ui.badge>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900">$99.99</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h5 class="font-semibold text-gray-900 mb-2">Description</h5>
                        <p class="text-gray-600 text-sm">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </p>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h5 class="font-semibold text-gray-900 mb-2">Specifications</h5>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Weight: 1.5 kg</li>
                            <li>• Dimensions: 30 x 20 x 10 cm</li>
                            <li>• Material: Premium Quality</li>
                        </ul>
                    </div>
                </div>

                <x-slot:footer>
                    <x-ui.button variant="white" @click="$dispatch('close-modal-rich')">
                        Close
                    </x-ui.button>
                    <x-ui.button variant="primary">
                        Add to Cart
                    </x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Testing Instructions</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>✓ Click each button to open the corresponding modal</li>
                <li>✓ Test closing modals by clicking the backdrop (except non-closeable)</li>
                <li>✓ Test closing modals by pressing the ESC key (except non-closeable)</li>
                <li>✓ Test closing modals using the X button in the header</li>
                <li>✓ Verify smooth open/close animations</li>
                <li>✓ Test different maxWidth variants (sm, md, lg, xl, 2xl)</li>
                <li>✓ Test modals with forms and interactive content</li>
                <li>✓ Test responsive behavior on mobile devices</li>
            </ul>
        </div>
    </div>

    <!-- Alpine.js via CDN (since Livewire is not used in this standalone page) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
