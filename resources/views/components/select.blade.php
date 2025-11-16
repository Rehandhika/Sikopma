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

<div class="mb-4">
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
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
        {{ $attributes->merge(['class' => 'block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm' . ($error ? ' border-red-300 focus:border-red-500 focus:ring-red-500' : '')]) }}
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
    <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif

    @if($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
