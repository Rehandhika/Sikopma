@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'error' => null,
    'help' => null,
])

<div class="form-group">
    @if($label)
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
        <span class="text-red-600">*</span>
        @endif
    </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-input' . ($error ? ' border-red-300 focus:border-red-500 focus:ring-red-500' : '')]) }}
    >

    @if($help)
    <p class="form-help">{{ $help }}</p>
    @endif

    @if($error)
    <p class="form-error">{{ $error }}</p>
    @endif
</div>
