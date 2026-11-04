@props([
    'name',
    'label',
    'showValue' => 'sim',
    'target',
    'required' => ''
])

<div class="emp-form-group emp-col-6"
     style="box-sizing:border-box;border-radius:12px;border:1px solid #e2e8f0;padding:20px;">

    <label>{{ $label }}</label>

    <div class="flex gap-10" style="justify-content:center;">
        <div>
            <input
                type="radio"
                name="{{ $name }}"
                value="nao"
                id="{{ $name }}_nao"
                checked
                data-target="{{ $target }}"
                data-show-value="{{ $showValue }}"
                data-required="{{ $required }}"
            >

            <label for="{{ $name }}_nao">Não</label>
        </div>

        <div>
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $showValue }}"
                id="{{ $name }}_sim"
                data-target="{{ $target }}"
                data-show-value="{{ $showValue }}"
                data-required="{{ $required }}"
            >

            <label for="{{ $name }}_sim">Sim</label>
        </div>
    </div>

    {{ $slot }}
</div>