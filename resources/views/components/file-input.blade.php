@props([
    'name' => 'arquivos[]',
    'label' => 'Anexos complementares',
    'helpText' => null,
    'multiple' => true,
    'accept' => '.pdf,.doc,.docx,.png,.jpg,.jpeg',
    'required' => false,
    'compact' => false
])

@php
    $id = 'file_input_' . Str::random(8);
    $defaultHelpText = $required ? 'Campo obrigatório.' : 'Opcional. Adicione outros arquivos se desejar.';
    $mostrarHeader = !$compact && !empty($label);
@endphp

@if($mostrarHeader)
<div class="campo file-input-group" style="margin-top: 15px; border-top: 1px dashed #ddd; padding-top: 12px;">
    <label style="display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px;">
        {{ $label }}
        @if($required)
            <span style="color: #e53e3e;" title="Campo obrigatório">*</span>
        @endif
    </label>

    <small style="display: block; color: #666; margin-bottom: 8px; font-size: 12px;">
        {{ $helpText ?? $defaultHelpText }}
    </small>
@endif

    <div class="file-input-wrapper">
        <label class="custom-file-upload">
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
                <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span class="file-upload-label">
                    <strong>Clique aqui</strong> para selecionar {{ $multiple ? 'os arquivos' : 'o arquivo' }}
                </span>
                @if(!empty($accept))
                    <span class="file-upload-formats">({{ str_replace(',', ', ', str_replace('.', '', strtoupper($accept))) }})</span>
                @endif
            </div>
        </label>

        <div id="feedback_{{ $id }}" class="file-feedback-box">
            <div style="font-weight: 600; display: flex; align-items: center; gap: 5px; margin-bottom: 4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ $multiple ? 'Arquivos selecionados:' : 'Arquivo selecionado:' }}</span>
            </div>
            <ul id="lista_{{ $id }}" style="margin: 0; padding-left: 18px; word-break: break-all;"></ul>
        </div>
    </div>

@if($mostrarHeader)
</div>
@endif

<script>
    function atualizarFeedbackInline_{{ $id }}(input) {
        const feedbackBox = document.getElementById('feedback_{{ $id }}');
        const lista = document.getElementById('lista_{{ $id }}');
        const card = input.closest('.doc-card') || input.closest('.campo');

        lista.innerHTML = '';

        if (input.files && input.files.length > 0) {
            feedbackBox.style.display = 'block';
            Array.from(input.files).forEach(file => {
                const li = document.createElement('li');
                li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                lista.appendChild(li);
            });
            if (card) {
                card.classList.add('doc-card-preenchido');
                card.classList.remove('doc-card-obrigatorio');
            }
        } else {
            feedbackBox.style.display = 'none';
            if (card) {
                card.classList.remove('doc-card-preenchido');
                if (input.dataset.obrigatorio === 'true') {
                    card.classList.add('doc-card-obrigatorio');
                }
            }
        }
    }
</script>
