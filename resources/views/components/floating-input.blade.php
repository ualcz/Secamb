@props([
    'id',
    'name',
    'label',
    'placeholder' => '',
    'type' => 'text',
    'value' => ''
])

@php
    $inputId = $id ?? $name;
@endphp

<style>
    .floating-group {
  position: relative;
  margin-bottom: 1.5rem;
  width: 100%;
}

.floating-input {
  width: 100%;
  padding: 1.25rem 0.75rem 0.5rem;
  font-size: 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  outline: none;
  background-color: transparent;
  transition: border-color 0.2s ease;
  font-weight: 500;
}

.floating-input:focus {
  border-color: #2563eb;
}

.floating-input::placeholder {
  color: transparent;
  transition: color 0.2s ease;
}

.floating-input:focus::placeholder {
  color: #9ca3af; /* Cor sutil para o exemplo/dica */
}

.floating-label {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #a7aebce4;
  font-size: 1rem;
  pointer-events: none;
  transition: all 0.2s ease;
  background-color: white;
  padding: 0 0.25rem;
  font-weight: 200;
}

.floating-input:focus + .floating-label,
.floating-input:not(:placeholder-shown) + .floating-label {
  top: 0;
  transform: translateY(-50%) scale(0.85);
  color: #2563eb;
  font-weight: 500;
}

.floating-input:not(:focus):not(:placeholder-shown) + .floating-label {
  color: #6b7280;
}
</style>

<div class="floating-group">
    <input
        type="{{ $type }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder ?: ' ' }}"
        {{ $attributes->merge(['class' => 'floating-input' . ($errors->has($name) ? ' border-red-500' : '')]) }}
    />
    <label for="{{ $inputId }}" class="floating-label">
        {{ $label }}
    </label>

    @error($name)
        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
    @enderror
</div>
