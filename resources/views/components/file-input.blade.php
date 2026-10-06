@props([
    'name' => 'arquivos[]',
    'label' => 'Anexos complementares',
    'helpText' => null,
    'multiple' => true,
    'accept' => '.pdf,.doc,.docx,.png,.jpg,.jpeg',
    'required' => false
])

@php
    $id = 'file_input_' . Str::random(8);
    $defaultHelpText = $required ? 'Campo obrigatório.' : 'Opcional. Adicione outros arquivos se desejar.';
@endphp

<style>
    .custom-file-upload {
        display: block;
        padding: 16px;
        background: #f9fafb;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        position: relative;
        transition: border-color 0.2s, background-color 0.2s;
    }
    .custom-file-upload:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
    }
    .custom-file-upload input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
        opacity: 0;
    }
    .file-upload-text {
        font-size: 13px;
        color: #4b5563;
    }
    .file-feedback-box {
        margin-top: 8px;
        font-size: 12px;
        color: #374151;
        background: #f3f4f6;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        display: none;
    }
    .file-feedback-box ul {
        margin: 4px 0 0 0;
        padding-left: 18px;
    }
    .obrigatorio {
        background-color: #F9EBEB;
    }
</style>

<div class="campo" style="margin-top: 15px; border-top: 1px dashed #ddd; padding-top: 12px;">
    <label style="display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px;">
        {{ $label }}
        @if($required)
            <span style="color: #e53e3e;" title="Campo obrigatório">*</span>
        @endif
    </label>

    <small style="display: block; color: #666; margin-bottom: 8px; font-size: 12px;">
        {{ $helpText ?? $defaultHelpText }}
    </small>

    <label @class(['custom-file-upload', 'obrigatorio' => $required])>
        <input
            type="file"
            name="{{ $name }}"
            @if($multiple) multiple @endif
            data-obrigatorio="{{ $required ? 'true' : 'false' }}"
            accept="{{ $accept }}"
            onchange="atualizarFeedbackInline_{{ $id }}(this)"
            {{ $attributes }}
        >
        <div class="file-upload-text">
            <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 8px auto; display: block;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <strong>Clique aqui</strong> para selecionar os arquivos
        </div>
    </label>

    <div id="feedback_{{ $id }}" class="file-feedback-box">
        <strong>Arquivos selecionados:</strong>
        <ul id="lista_{{ $id }}"></ul>
    </div>
</div>

<script>
    function atualizarFeedbackInline_{{ $id }}(input) {
        const feedbackBox = document.getElementById('feedback_{{ $id }}');
        const lista = document.getElementById('lista_{{ $id }}');

        lista.innerHTML = '';

        if (input.files && input.files.length > 0) {
            feedbackBox.style.display = 'block';
            Array.from(input.files).forEach(file => {
                const li = document.createElement('li');
                li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                lista.appendChild(li);
            });
        } else {
            feedbackBox.style.display = 'none';
        }
    }
</script>
