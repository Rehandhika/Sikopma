@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
    'icon' => null,
])

<div class="space-y-1">
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
        <span class="text-danger-500">*</span>
        @endif
    </label>
    @endif

    <div class="relative">
        @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <x-ui.icon :name="$icon" class="h-5 w-5 text-gray-400" />
        </div>
        @endif
        
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => implode(' ', [
                    'block w-full rounded-lg border shadow-sm transition-colors duration-200',
                    'focus:outline-none focus:ring-2 focus:ring-offset-0',
                    'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
                    $icon ? 'pl-10' : 'px-3',
                    'py-2 text-sm',
                    $error 
                        ? 'border-danger-300 text-danger-900 placeholder-danger-300 focus:border-danger-500 focus:ring-danger-500' 
                        : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500'
                ])
            ]) }}
        >
    </div>

    @if($help && !$error)
    <p class="text-xs text-gray-500">{{ $help }}</p>
    @endif

    @if($error)
    <p class="text-xs text-danger-600 flex items-center">
        <x-ui.icon name="exclamation-circle" class="w-4 h-4 mr-1" />
        {{ $error }}
    </p>
    @endif
</div>
