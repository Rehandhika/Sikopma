@props([
    'name' => 'image',
    'label' => 'Gambar',
    'preview' => null,
    'existingImage' => null,
    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
    'maxSize' => '5MB',
    'hint' => null,
    'error' => null,
    'required' => false,
    'aspectRatio' => 'square', // square, landscape, portrait
])

@php
    $aspectClasses = match($aspectRatio) {
        'landscape' => 'aspect-video',
        'portrait' => 'aspect-[3/4]',
        default => 'aspect-square',
    };
@endphp

<div 
    x-data="{ 
        isDragging: false,
        isUploading: false,
        progress: 0
    }"
    {{ $attributes->class(['space-y-2']) }}
>
    {{-- Label --}}
    @if($label)
        <label class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    {{-- Upload Area --}}
    <div 
        class="relative"
        x-on:dragover.prevent="isDragging = true"
        x-on:dragleave.prevent="isDragging = false"
        x-on:drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
    >
        {{-- Preview Area --}}
        @if($preview || $existingImage)
            <div class="relative group">
                <div class="w-full max-w-xs {{ $aspectClasses }} bg-gray-100 rounded-lg overflow-hidden border-2 border-gray-200">
                    <img 
                        src="{{ $preview ?? $existingImage }}" 
                        alt="Preview" 
                        class="w-full h-full object-cover"
                        loading="lazy"
                    >
                </div>
                
                {{-- Overlay Actions --}}
                <div class="absolute inset-0 max-w-xs {{ $aspectClasses }} bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2">
                    {{-- Change Image --}}
                    <label class="cursor-pointer p-2 bg-white rounded-full hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <input 
                            type="file" 
                            wire:model="{{ $name }}"
                            accept="{{ $accept }}"
                            class="hidden"
                            x-ref="fileInput"
                        >
                    </label>
                    
                    {{-- Remove Image --}}
                    @if($preview)
                        <button 
                            type="button"
                            wire:click="removeImage"
                            class="p-2 bg-white rounded-full hover:bg-gray-100 transition"
                        >
                            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    @elseif($existingImage)
                        <button 
                            type="button"
                            wire:click="deleteExistingImage"
                            wire:confirm="Hapus gambar ini?"
                            class="p-2 bg-white rounded-full hover:bg-gray-100 transition"
                        >
                            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Loading Overlay --}}
                <div 
                    wire:loading 
                    wire:target="{{ $name }}"
                    class="absolute inset-0 max-w-xs {{ $aspectClasses }} bg-white/80 rounded-lg flex items-center justify-center"
                >
                    <div class="text-center">
                        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Mengupload...</p>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty Upload Area --}}
            <label 
                class="flex flex-col items-center justify-center w-full max-w-xs {{ $aspectClasses }} border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                :class="isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50 hover:bg-gray-100'"
            >
                <div class="flex flex-col items-center justify-center p-6 text-center" wire:loading.remove wire:target="{{ $name }}">
                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mb-1 text-sm text-gray-600">
                        <span class="font-semibold text-primary-600">Klik untuk upload</span> atau drag & drop
                    </p>
                    <p class="text-xs text-gray-500">PNG, JPG, WebP, GIF (Maks. {{ $maxSize }})</p>
                </div>

                {{-- Loading State --}}
                <div wire:loading wire:target="{{ $name }}" class="flex flex-col items-center justify-center p-6">
                    <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Mengupload...</p>
                </div>

                <input 
                    type="file" 
                    wire:model="{{ $name }}"
                    accept="{{ $accept }}"
                    class="hidden"
                    x-ref="fileInput"
                >
            </label>
        @endif
    </div>

    {{-- Hint --}}
    @if($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif

    {{-- Error --}}
    @if($error)
        <p class="text-sm text-danger-600">{{ $error }}</p>
    @endif
</div>
