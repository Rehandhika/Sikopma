<div class="max-w-4xl mx-auto p-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Form Components Validation Test</h1>
        
        @if (session()->has('success'))
            <x-ui.alert variant="success" dismissible class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <form wire:submit.prevent="submit" class="space-y-6">
            <!-- Select Components -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.select
                    name="country"
                    label="Country"
                    wire:model.live="country"
                    :options="[
                        '' => 'Select country...',
                        'id' => 'Indonesia',
                        'my' => 'Malaysia',
                        'sg' => 'Singapore',
                        'th' => 'Thailand',
                    ]"
                    required
                    :error="$errors->first('country')"
                />
                
                <x-ui.select
                    name="role"
                    label="Role"
                    wire:model.live="role"
                    :options="[
                        '' => 'Select role...',
                        'admin' => 'Administrator',
                        'manager' => 'Manager',
                        'staff' => 'Staff',
                    ]"
                    required
                    :error="$errors->first('role')"
                />
            </div>

            <!-- Textarea Components -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.textarea
                    name="description"
                    label="Description"
                    wire:model.live="description"
                    placeholder="Enter description..."
                    rows="4"
                    help="Maximum 500 characters"
                    :error="$errors->first('description')"
                />
                
                <x-ui.textarea
                    name="notes"
                    label="Notes"
                    wire:model.live="notes"
                    placeholder="Enter notes (min 10 characters)..."
                    rows="4"
                    required
                    :error="$errors->first('notes')"
                />
            </div>

            <!-- Checkbox Components -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Preferences
                </label>
                <div class="space-y-3">
                    <x-ui.checkbox
                        name="terms"
                        label="I agree to the terms and conditions"
                        wire:model.live="terms"
                        :error="$errors->first('terms')"
                    />
                    
                    <x-ui.checkbox
                        name="newsletter"
                        label="Subscribe to newsletter"
                        description="Get weekly updates about new features and products"
                        wire:model="newsletter"
                    />
                </div>
            </div>

            <!-- Radio Components -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Method <span class="text-danger-500">*</span>
                </label>
                <div class="space-y-2">
                    <x-ui.radio
                        name="payment"
                        value="credit_card"
                        label="Credit Card"
                        description="Pay with Visa, Mastercard, or American Express"
                        wire:model.live="payment"
                        :error="$errors->first('payment')"
                    />
                    
                    <x-ui.radio
                        name="payment"
                        value="bank_transfer"
                        label="Bank Transfer"
                        description="Direct transfer to our bank account"
                        wire:model.live="payment"
                    />
                    
                    <x-ui.radio
                        name="payment"
                        value="ewallet"
                        label="E-Wallet"
                        description="Pay with GoPay, OVO, or Dana"
                        wire:model.live="payment"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Shipping Method <span class="text-danger-500">*</span>
                </label>
                <div class="space-y-2">
                    <x-ui.radio
                        name="shipping"
                        value="standard"
                        label="Standard Shipping (3-5 days)"
                        wire:model.live="shipping"
                        :error="$errors->first('shipping')"
                    />
                    
                    <x-ui.radio
                        name="shipping"
                        value="express"
                        label="Express Shipping (1-2 days)"
                        wire:model.live="shipping"
                    />
                </div>
            </div>

            <!-- Additional Fields -->
            <x-ui.textarea
                name="bio"
                label="Bio"
                wire:model.live="bio"
                placeholder="Tell us about yourself..."
                rows="3"
                help="Maximum 200 characters"
                :error="$errors->first('bio')"
            />

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Notification Preferences
                </label>
                <div class="space-y-2">
                    <x-ui.checkbox
                        name="email_notifications"
                        label="Email notifications"
                        wire:model="email_notifications"
                    />
                    
                    <x-ui.checkbox
                        name="sms_notifications"
                        label="SMS notifications"
                        wire:model="sms_notifications"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Account Type <span class="text-danger-500">*</span>
                </label>
                <div class="space-y-2">
                    <x-ui.radio
                        name="account_type"
                        value="personal"
                        label="Personal"
                        wire:model="account_type"
                    />
                    
                    <x-ui.radio
                        name="account_type"
                        value="business"
                        label="Business"
                        wire:model="account_type"
                    />
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <x-ui.button variant="white" type="button" wire:click="$refresh">
                    Reset
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    Submit Form
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
