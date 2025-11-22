@props([
    'label' => null,
    'name' => '',
    'checked' => false,
    'disabled' => false,
    'error' => null,
    'description' => null,
])

<div class="space-y-1">
    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input
                type="checkbox"
                id="{{ $name }}"
                name="{{ $name }}"
                value="1"
                @if($checked) checked @endif
                @if($disabled) disabled @endif
                {{ $attributes->merge([
                    'class' => implode(' ', [
                        'w-4 h-4 rounded transition-colors duration-200',
                        'focus:ring-2 focus:ring-offset-0',
                        'disabled:opacity-50 disabled:cursor-not-allowed',
                        $error
                            ? 'text-danger-600 border-danger-300 focus:ring-danger-500'
                            : 'text-primary-600 border-gray-300 focus:ring-primary-500'
                    ])
                ]) }}
            >
        </div>
        @if($label || $description)
        <div class="ml-2">
            @if($label)
            <label for="{{ $name }}" class="text-sm text-gray-700 select-none block">
                {{ $label }}
            </label>
            @endif
            @if($description)
            <p class="text-xs text-gray-500 mt-0.5">{{ $description }}</p>
            @endif
        </div>
        @endif
    </div>

    @if($error)
    <p class="text-xs text-danger-600 flex items-center ml-6">
        <x-ui.icon name="exclamation-circle" class="w-4 h-4 mr-1" />
        {{ $error }}
    </p>
    @endif
</div>
