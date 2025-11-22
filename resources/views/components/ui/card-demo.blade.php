<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Component Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Card Component Demo</h1>

        <!-- Basic Card -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Basic Card</h2>
            <x-ui.card>
                <p class="text-gray-700">This is a basic card with default padding and shadow.</p>
            </x-ui.card>
        </section>

        <!-- Card with Title -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card with Title</h2>
            <x-ui.card title="Card Title">
                <p class="text-gray-700">This card has a title in the header section.</p>
            </x-ui.card>
        </section>

        <!-- Card with Title and Subtitle -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card with Title and Subtitle</h2>
            <x-ui.card title="User Profile" subtitle="Manage your personal information">
                <p class="text-gray-700">This card has both a title and subtitle in the header.</p>
            </x-ui.card>
        </section>

        <!-- Card with Footer -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card with Footer</h2>
            <x-ui.card title="Confirm Action" subtitle="Please review before proceeding">
                <p class="text-gray-700">Are you sure you want to continue with this action?</p>
                
                <x-slot:footer>
                    <div class="flex justify-end space-x-3">
                        <x-ui.button variant="white">Cancel</x-ui.button>
                        <x-ui.button variant="primary">Confirm</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </section>

        <!-- Shadow Variants -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Shadow Variants</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-ui.card title="No Shadow" shadow="none">
                    <p class="text-sm text-gray-600">shadow="none"</p>
                </x-ui.card>
                
                <x-ui.card title="Small Shadow" shadow="sm">
                    <p class="text-sm text-gray-600">shadow="sm"</p>
                </x-ui.card>
                
                <x-ui.card title="Medium Shadow" shadow="md">
                    <p class="text-sm text-gray-600">shadow="md" (default)</p>
                </x-ui.card>
                
                <x-ui.card title="Large Shadow" shadow="lg">
                    <p class="text-sm text-gray-600">shadow="lg"</p>
                </x-ui.card>
            </div>
        </section>

        <!-- No Padding -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card without Padding</h2>
            <x-ui.card title="Image Card" :padding="false">
                <img src="https://via.placeholder.com/800x400" alt="Placeholder" class="w-full">
                <div class="px-6 py-4">
                    <p class="text-gray-700">This card has padding disabled for the content area, useful for images.</p>
                </div>
            </x-ui.card>
        </section>

        <!-- Composition with Form Components -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card with Form Components</h2>
            <x-ui.card title="Login Form" subtitle="Enter your credentials">
                <form class="space-y-4">
                    <x-ui.input 
                        label="Email" 
                        name="email" 
                        type="email" 
                        placeholder="you@example.com"
                        required
                    />
                    
                    <x-ui.input 
                        label="Password" 
                        name="password" 
                        type="password" 
                        placeholder="••••••••"
                        required
                    />
                    
                    <x-ui.checkbox name="remember" label="Remember me" />
                </form>
                
                <x-slot:footer>
                    <div class="flex justify-between items-center">
                        <a href="#" class="text-sm text-primary-600 hover:text-primary-700">Forgot password?</a>
                        <x-ui.button variant="primary" type="submit">Sign In</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </section>

        <!-- Composition with Button Variants -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card with Button Variants</h2>
            <x-ui.card title="Actions" subtitle="Choose an action to perform">
                <p class="text-gray-700 mb-4">Select one of the following actions:</p>
                <div class="flex flex-wrap gap-2">
                    <x-ui.button variant="primary" size="sm">Primary</x-ui.button>
                    <x-ui.button variant="secondary" size="sm">Secondary</x-ui.button>
                    <x-ui.button variant="success" size="sm">Success</x-ui.button>
                    <x-ui.button variant="danger" size="sm">Danger</x-ui.button>
                    <x-ui.button variant="warning" size="sm">Warning</x-ui.button>
                    <x-ui.button variant="info" size="sm">Info</x-ui.button>
                </div>
            </x-ui.card>
        </section>

        <!-- Nested Cards -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Nested Cards</h2>
            <x-ui.card title="Parent Card" shadow="lg">
                <p class="text-gray-700 mb-4">This is a parent card containing nested cards.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.card title="Child Card 1" shadow="sm">
                        <p class="text-sm text-gray-600">Nested card content</p>
                    </x-ui.card>
                    
                    <x-ui.card title="Child Card 2" shadow="sm">
                        <p class="text-sm text-gray-600">Nested card content</p>
                    </x-ui.card>
                </div>
            </x-ui.card>
        </section>

        <!-- Card with Custom Classes -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card with Custom Classes</h2>
            <x-ui.card title="Custom Styled Card" class="border-2 border-primary-500">
                <p class="text-gray-700">This card has custom border styling applied via the class attribute.</p>
            </x-ui.card>
        </section>

        <!-- Card Grid Layout -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card Grid Layout</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.card title="Feature 1" subtitle="Description">
                    <p class="text-gray-600">Feature details go here.</p>
                    <x-slot:footer>
                        <x-ui.button variant="outline" size="sm" class="w-full">Learn More</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
                
                <x-ui.card title="Feature 2" subtitle="Description">
                    <p class="text-gray-600">Feature details go here.</p>
                    <x-slot:footer>
                        <x-ui.button variant="outline" size="sm" class="w-full">Learn More</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
                
                <x-ui.card title="Feature 3" subtitle="Description">
                    <p class="text-gray-600">Feature details go here.</p>
                    <x-slot:footer>
                        <x-ui.button variant="outline" size="sm" class="w-full">Learn More</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </section>

        <!-- Card with Select and Textarea -->
        <section>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Card with All Form Components</h2>
            <x-ui.card title="Contact Form" subtitle="Send us a message">
                <form class="space-y-4">
                    <x-ui.input 
                        label="Name" 
                        name="name" 
                        placeholder="Your name"
                        required
                    />
                    
                    <x-ui.select 
                        label="Subject" 
                        name="subject"
                        :options="[
                            '' => 'Select a subject',
                            'general' => 'General Inquiry',
                            'support' => 'Technical Support',
                            'billing' => 'Billing Question'
                        ]"
                        required
                    />
                    
                    <x-ui.textarea 
                        label="Message" 
                        name="message" 
                        placeholder="Your message here..."
                        rows="4"
                        required
                    />
                    
                    <div class="space-y-2">
                        <x-ui.checkbox name="newsletter" label="Subscribe to newsletter" />
                        <x-ui.checkbox name="terms" label="I agree to the terms and conditions" required />
                    </div>
                </form>
                
                <x-slot:footer>
                    <div class="flex justify-end space-x-3">
                        <x-ui.button variant="white">Cancel</x-ui.button>
                        <x-ui.button variant="primary">Send Message</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </section>
    </div>
</body>
</html>
