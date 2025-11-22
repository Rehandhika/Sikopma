<div class="max-w-2xl mx-auto p-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Input Component Livewire Test</h2>
        
        <form wire:submit="save" class="space-y-6">
            <!-- Basic Input with wire:model -->
            <x-ui.input 
                label="Name"
                name="name"
                type="text"
                placeholder="Enter your name"
                wire:model="name"
                :required="true"
                :error="$errors->first('name')"
            />

            <!-- Email with icon and wire:model -->
            <x-ui.input 
                label="Email"
                name="email"
                type="email"
                placeholder="Enter your email"
                icon="user"
                wire:model="email"
                :required="true"
                :error="$errors->first('email')"
                help="We'll never share your email"
            />

            <!-- Password with wire:model -->
            <x-ui.input 
                label="Password"
                name="password"
                type="password"
                placeholder="Enter password"
                wire:model="password"
                :required="true"
                :error="$errors->first('password')"
                help="Password must be at least 8 characters"
            />

            <!-- Phone with icon -->
            <x-ui.input 
                label="Phone Number"
                name="phone"
                type="tel"
                placeholder="Enter phone number"
                icon="phone"
                wire:model="phone"
                :error="$errors->first('phone')"
            />

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <button 
                    type="button"
                    wire:click="reset"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Reset
                </button>
                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50"
                >
                    <span wire:loading.remove>Save</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>

        <!-- Display Values -->
        @if($name || $email || $password || $phone)
        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-semibold text-gray-900 mb-2">Current Values:</h3>
            <ul class="space-y-1 text-sm text-gray-600">
                <li><strong>Name:</strong> {{ $name }}</li>
                <li><strong>Email:</strong> {{ $email }}</li>
                <li><strong>Password:</strong> {{ str_repeat('*', strlen($password)) }}</li>
                <li><strong>Phone:</strong> {{ $phone }}</li>
            </ul>
        </div>
        @endif
    </div>
</div>
