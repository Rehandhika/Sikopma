<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Section Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Form Section Component Tests</h1>

        <!-- Test 1: Form section with title and description -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Test 1: With Title and Description</h2>
            
            <x-layout.form-section 
                title="Personal Information"
                description="Update your personal details and contact information."
            >
                <x-ui.input 
                    label="Full Name"
                    name="full_name"
                    placeholder="Enter your full name"
                    required
                />
                
                <x-ui.input 
                    label="Email Address"
                    name="email"
                    type="email"
                    placeholder="your.email@example.com"
                    required
                />
                
                <x-ui.input 
                    label="Phone Number"
                    name="phone"
                    type="tel"
                    placeholder="+62 812 3456 7890"
                />
            </x-layout.form-section>
        </div>

        <!-- Test 2: Form section with title only -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Test 2: With Title Only</h2>
            
            <x-layout.form-section title="Account Settings">
                <x-ui.input 
                    label="Username"
                    name="username"
                    placeholder="Choose a username"
                    required
                />
                
                <x-ui.input 
                    label="Current Password"
                    name="current_password"
                    type="password"
                    placeholder="Enter current password"
                />
                
                <x-ui.input 
                    label="New Password"
                    name="new_password"
                    type="password"
                    placeholder="Enter new password"
                />
            </x-layout.form-section>
        </div>

        <!-- Test 3: Form section without title (just spacing) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Test 3: Without Title (Spacing Only)</h2>
            
            <x-layout.form-section>
                <x-ui.input 
                    label="Street Address"
                    name="address"
                    placeholder="123 Main Street"
                />
                
                <div class="grid grid-cols-2 gap-4">
                    <x-ui.input 
                        label="City"
                        name="city"
                        placeholder="Jakarta"
                    />
                    
                    <x-ui.input 
                        label="Postal Code"
                        name="postal_code"
                        placeholder="12345"
                    />
                </div>
                
                <x-ui.select 
                    label="Country"
                    name="country"
                    :options="[
                        '' => 'Select a country',
                        'ID' => 'Indonesia',
                        'MY' => 'Malaysia',
                        'SG' => 'Singapore',
                    ]"
                />
            </x-layout.form-section>
        </div>

        <!-- Test 4: Multiple form sections in one form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Test 4: Multiple Sections in One Form</h2>
            
            <form class="space-y-8">
                <x-layout.form-section 
                    title="Basic Information"
                    description="Provide your basic details."
                >
                    <x-ui.input 
                        label="First Name"
                        name="first_name"
                        required
                    />
                    
                    <x-ui.input 
                        label="Last Name"
                        name="last_name"
                        required
                    />
                </x-layout.form-section>

                <x-layout.form-section 
                    title="Contact Details"
                    description="How can we reach you?"
                >
                    <x-ui.input 
                        label="Email"
                        name="contact_email"
                        type="email"
                        required
                    />
                    
                    <x-ui.input 
                        label="Phone"
                        name="contact_phone"
                        type="tel"
                    />
                </x-layout.form-section>

                <x-layout.form-section 
                    title="Preferences"
                    description="Customize your experience."
                >
                    <x-ui.checkbox 
                        label="Receive email notifications"
                        name="email_notifications"
                    />
                    
                    <x-ui.checkbox 
                        label="Receive SMS notifications"
                        name="sms_notifications"
                    />
                </x-layout.form-section>

                <div class="flex justify-end space-x-3">
                    <x-ui.button variant="white" type="button">
                        Cancel
                    </x-ui.button>
                    <x-ui.button variant="primary" type="submit">
                        Save Changes
                    </x-ui.button>
                </div>
            </form>
        </div>

        <!-- Test 5: Form section with custom classes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Test 5: With Custom Classes</h2>
            
            <x-layout.form-section 
                title="Custom Styled Section"
                description="This section has custom background and padding."
                class="bg-gray-50 p-6 rounded-lg"
            >
                <x-ui.input 
                    label="Field 1"
                    name="field1"
                />
                
                <x-ui.input 
                    label="Field 2"
                    name="field2"
                />
            </x-layout.form-section>
        </div>

        <!-- Test 6: Form section with textarea and select -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Test 6: With Different Form Elements</h2>
            
            <x-layout.form-section 
                title="Feedback Form"
                description="Share your thoughts with us."
            >
                <x-ui.select 
                    label="Category"
                    name="category"
                    :options="[
                        '' => 'Select a category',
                        'bug' => 'Bug Report',
                        'feature' => 'Feature Request',
                        'general' => 'General Feedback',
                    ]"
                    required
                />
                
                <x-ui.textarea 
                    label="Message"
                    name="message"
                    placeholder="Tell us what you think..."
                    rows="5"
                    required
                />
                
                <x-ui.checkbox 
                    label="I agree to the terms and conditions"
                    name="agree_terms"
                />
            </x-layout.form-section>
        </div>

        <!-- Test 7: Consistent spacing verification -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Test 7: Spacing Consistency Check</h2>
            <p class="text-sm text-gray-600 mb-4">All fields should have consistent 1rem (16px) spacing between them.</p>
            
            <x-layout.form-section 
                title="Spacing Test"
                description="Verify consistent spacing between form fields."
            >
                <x-ui.input label="Field 1" name="spacing1" />
                <x-ui.input label="Field 2" name="spacing2" />
                <x-ui.input label="Field 3" name="spacing3" />
                <x-ui.input label="Field 4" name="spacing4" />
                <x-ui.input label="Field 5" name="spacing5" />
            </x-layout.form-section>
        </div>
    </div>
</body>
</html>
