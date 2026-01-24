<div class="min-h-screen bg-white flex items-center justify-center px-4">
  <div class="w-full max-w-sm">
    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
      <!-- Header -->
      <div class="px-8 pt-10 pb-6 text-center">
        <!-- Logo -->
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl shadow-md mb-4 overflow-hidden">
          <img src="{{ asset('images/logo.png') }}" alt="SIKOPMA" class="w-16 h-16 object-cover">
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Masuk</h1>
        <p class="text-sm text-gray-500">Gunakan akun Sipadu Anda</p>
      </div>

      <!-- Form -->
      <form wire:submit="login" class="px-8 pb-8 space-y-4">
        <!-- Username -->
        <x-ui.input
          label="Username"
          name="nim"
          type="text"
          placeholder="Masukkan NIM Anda"
          wire:model="nim"
          :error="$errors->first('nim')"
          autofocus
        />

        <!-- Password -->
        <x-ui.input
          label="Password"
          name="password"
          type="password"
          placeholder="Masukkan password Anda"
          wire:model="password"
          :error="$errors->first('password')"
        />

        <!-- Remember + Help -->
        <div class="flex items-center justify-between">
          <x-ui.checkbox
            label="Ingat saya di perangkat ini"
            name="remember"
            wire:model="remember"
          />
          <button type="button" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </button>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
          <x-ui.button
            type="submit"
            variant="primary"
            wire:loading.attr="disabled"
          >
            <span wire:loading.remove>MASUK</span>
            <span wire:loading>Memproses...</span>
          </x-ui.button>
        </div>
      </form>
    </div>
  </div>
</div>
