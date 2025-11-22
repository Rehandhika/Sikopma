<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Components Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Form Components Demo</h1>
            
            <form class="space-y-6">
                <!-- Select Component -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Component</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.select
                            name="country"
                            label="Country"
                            :options="[
                                'id' => 'Indonesia',
                                'my' => 'Malaysia',
                                'sg' => 'Singapore',
                                'th' => 'Thailand',
                            ]"
                            selected="id"
                            help="Select your country"
                        />
                        
                        <x-ui.select
                            name="role"
                            label="Role"
                            required
                            :options="[
                                'admin' => 'Administrator',
                                'manager' => 'Manager',
                                'staff' => 'Staff',
                            ]"
                            help="This field is required"
                        />
                        
                        <x-ui.select
                            name="status"
                            label="Status"
                            disabled
                            :options="[
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ]"
                            selected="active"
                        />
                        
                        <x-ui.select
                            name="category_error"
                            label="Category (with error)"
                            :options="[
                                'cat1' => 'Category 1',
                                'cat2' => 'Category 2',
                            ]"
                            error="Please select a valid category"
                        />
                    </div>
                </div>

                <hr class="my-6">

                <!-- Textarea Component -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Textarea Component</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.textarea
                            name="description"
                            label="Description"
                            placeholder="Enter description..."
                            rows="4"
                            help="Maximum 500 characters"
                        />
                        
                        <x-ui.textarea
                            name="notes"
                            label="Notes"
                            required
                            placeholder="Enter notes..."
                            rows="4"
                        >Default content here</x-ui.textarea>
                        
                        <x-ui.textarea
                            name="comments"
                            label="Comments (disabled)"
                            disabled
                            rows="3"
                        >This textarea is disabled</x-ui.textarea>
                        
                        <x-ui.textarea
                            name="feedback_error"
                            label="Feedback (with error)"
                            placeholder="Enter feedback..."
                            rows="3"
                            error="Feedback must be at least 10 characters"
                        />
                    </div>
                </div>

                <hr class="my-6">

                <!-- Checkbox Component -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Checkbox Component</h2>
                    
                    <div class="space-y-4">
                        <x-ui.checkbox
                            name="terms"
                            label="I agree to the terms and conditions"
                            help="You must agree to continue"
                        />
                        
                        <x-ui.checkbox
                            name="newsletter"
                            label="Subscribe to newsletter"
                            description="Get weekly updates about new features and products"
                            checked
                        />
                        
                        <x-ui.checkbox
                            name="notifications"
                            label="Enable notifications"
                            description="Receive push notifications for important updates"
                        />
                        
                        <x-ui.checkbox
                            name="disabled_check"
                            label="Disabled checkbox"
                            disabled
                            checked
                        />
                        
                        <x-ui.checkbox
                            name="error_check"
                            label="Checkbox with error"
                            error="You must accept this option"
                        />
                    </div>
                </div>

                <hr class="my-6">

                <!-- Radio Component -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Radio Component</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Method
                            </label>
                            <div class="space-y-2">
                                <x-ui.radio
                                    name="payment"
                                    value="credit_card"
                                    label="Credit Card"
                                    description="Pay with Visa, Mastercard, or American Express"
                                    checked
                                />
                                
                                <x-ui.radio
                                    name="payment"
                                    value="bank_transfer"
                                    label="Bank Transfer"
                                    description="Direct transfer to our bank account"
                                />
                                
                                <x-ui.radio
                                    name="payment"
                                    value="ewallet"
                                    label="E-Wallet"
                                    description="Pay with GoPay, OVO, or Dana"
                                />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Shipping Method (with error)
                            </label>
                            <div class="space-y-2">
                                <x-ui.radio
                                    name="shipping"
                                    value="standard"
                                    label="Standard Shipping"
                                    error="Please select a shipping method"
                                />
                                
                                <x-ui.radio
                                    name="shipping"
                                    value="express"
                                    label="Express Shipping"
                                />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Disabled Radio Group
                            </label>
                            <div class="space-y-2">
                                <x-ui.radio
                                    name="disabled_radio"
                                    value="option1"
                                    label="Option 1"
                                    disabled
                                    checked
                                />
                                
                                <x-ui.radio
                                    name="disabled_radio"
                                    value="option2"
                                    label="Option 2"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6">

                <!-- Combined Form Example -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Complete Form Example</h2>
                    
                    <div class="space-y-4">
                        <x-ui.select
                            name="user_role"
                            label="User Role"
                            required
                            :options="[
                                '' => 'Select role...',
                                'admin' => 'Administrator',
                                'user' => 'Regular User',
                            ]"
                        />
                        
                        <x-ui.textarea
                            name="bio"
                            label="Bio"
                            placeholder="Tell us about yourself..."
                            rows="4"
                            help="Maximum 200 characters"
                        />
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Preferences
                            </label>
                            <div class="space-y-2">
                                <x-ui.checkbox
                                    name="email_notifications"
                                    label="Email notifications"
                                />
                                
                                <x-ui.checkbox
                                    name="sms_notifications"
                                    label="SMS notifications"
                                />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Account Type
                            </label>
                            <div class="space-y-2">
                                <x-ui.radio
                                    name="account_type"
                                    value="personal"
                                    label="Personal"
                                    checked
                                />
                                
                                <x-ui.radio
                                    name="account_type"
                                    value="business"
                                    label="Business"
                                />
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <x-ui.button variant="white" type="button">
                                Cancel
                            </x-ui.button>
                            <x-ui.button variant="primary" type="submit">
                                Save Changes
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
