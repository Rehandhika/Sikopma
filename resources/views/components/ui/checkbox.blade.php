@props([
    'label' => null,
    'name' => '',
    'value' => 1,
    'checked' => false,
    'disabled' => false,
    'required' => false,
    'error' => null,
    'help' => null,
    'inline' => false,
])

<div @class([
    'flex items-start',
    'flex-col' => !$inline,
    'flex-row' => $inline,
])>
    <div class="flex items-center h-5">
        <input
            type="checkbox"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @if($checked) checked @endif
            @if($disabled) disabled @endif
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => implode(' ', [
                    'w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500',
                    'dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-primary-400',
                    $error
                        ? 'border-danger-300 text-danger-600 focus:ring-danger-500 dark:border-danger-700'
                        : '',
                    $disabled
                        ? 'cursor-not-allowed opacity-50'
                        : 'cursor-pointer',
                ])
            ]) }}
        >
    </div>

    @if($label)
    <div @class([
        'ml-3' => !$inline,
        'ml-2' => $inline,
    ])>
        @if($label)
        <label for="{{ $name }}" @class([
            'text-sm font-medium',
            $disabled ? 'text-gray-400 dark:text-gray-500 cursor-not-allowed' : 'text-gray-700 dark:text-gray-300 cursor-pointer',
        ])>
            {{ $label }}
            @if($required)
            <span class="text-danger-500">*</span>
            @endif
        </label>
        @endif

        @if($help && !$error)
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $help }}</p>
        @endif

        @if($error)
        <p class="text-xs text-danger-600 dark:text-danger-400 mt-1 flex items-center">
            <x-ui.icon name="exclamation-circle" class="w-3 h-3 mr-1" />
            {{ $error }}
        </p>
        @endif
    </div>
    @endif
</div>
