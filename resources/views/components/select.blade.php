@props([
    'label' => null,
    'name' => '',
    'options' => [],
    'placeholder' => 'Pilih...',
    'required' => false,
    'error' => null,
    'help' => null,
    'tomselect' => false,
    'multiple' => false,
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

    <select
        id="{{ $name }}"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        {{ $required ? 'required' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $tomselect ? ($multiple ? 'data-multiselect' : 'data-select') : '' }}
        {{ $attributes->merge(['class' => 'form-input']) }}
    >
        @if(!$multiple && $placeholder)
        <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $value => $text)
        <option value="{{ $value }}">{{ $text }}</option>
        @endforeach

        {{ $slot }}
    </select>

    @if($help)
    <p class="form-help">{{ $help }}</p>
    @endif

    @if($error)
    <p class="form-error">{{ $error }}</p>
    @endif
</div>
