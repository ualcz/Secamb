<!-- Etapa 1: Dados do Requerimento (IdentificaÃ§Ã£o, Objeto e Justificativa) -->
<div class="form-step" data-step="1">
    <!-- 1. IdentificaÃ§Ã£o do Aluno -->
    <fieldset>
        <legend>IdentificaÃ§Ã£o do Aluno</legend>
        <div class="form-linha">
            <div class="campo">
                <label>Nome:</label>
                <input type="text" value="{{ auth()->user()->nome }}" readonly>
            </div>
            <div class="campo">
                <label>MatrÃ­cula:</label>
                <input type="text" value="{{ auth()->user()->matricula ?? '' }}" readonly>
            </div>
            <div class="campo">
                <label>Turma / Curso:</label>
                <input type="text" value="{{ auth()->user()->tipo_processo_formatado ?? '' }}" readonly>
            </div>
        </div>

        <div class="info-aluno">
            <div class="form-linha">
                <div class="campo">
                    <label>E-mail Pessoal (editÃ¡vel):</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" placeholder="seu.email@exemplo.com">
                </div>
                <div class="campo">
                    <label>Telefone / WhatsApp (editÃ¡vel):</label>
                    <input type="text" name="telefone" value="{{ old('telefone', auth()->user()->telefone ?? '') }}" placeholder="(XX) XXXXX-XXXX">
                </div>
            </div>

            <div class="form-linha">
                <div class="campo" style="flex: 2; min-width: 240px;">
                    <label>Rua e NÃºmero (editÃ¡vel):</label>
                    <input type="text" name="rua" value="{{ old('rua', auth()->user()->endereco?->rua ?? '') }}" placeholder="Ex: Rua Antonio Francisco, 60">
                </div>
                <div class="campo" style="flex: 1.5; min-width: 150px;">
                    <label>Bairro:</label>
                    <input type="text" name="bairro" value="{{ old('bairro', auth()->user()->endereco?->bairro ?? '') }}" placeholder="Bairro">
                </div>
            </div>

            <div class="form-linha">
                <div class="campo" style="flex: 2; min-width: 180px;">
                    <label>Cidade:</label>
                    <input type="text" name="cidade" value="{{ old('cidade', auth()->user()->endereco?->cidade ?? '') }}" placeholder="Cidade">
                </div>
                <div class="campo" style="flex: 0.8; min-width: 80px;">
                    <label>Estado (UF):</label>
                    <input type="text" name="estado" maxlength="2" value="{{ old('estado', auth()->user()->endereco?->estado ?? '') }}" placeholder="BA" style="text-transform: uppercase;">
                </div>
                <div class="campo" style="flex: 1.2; min-width: 130px;">
                    <label>CEP:</label>
                    <input type="text" name="cep" value="{{ old('cep', auth()->user()->endereco?->cep ?? '') }}" placeholder="00000-000">
                </div>
            </div>
        </div>
        <button type="button" id="ver-info-aluno" onclick="verInfoAluno()" class="inline-flex justify-center items-center w-10 h-10">
            <img src="{{ asset('img/icons/chevron-down.svg') }}" class="icon">
        </button>
    </fieldset>

    <!-- 2. Objeto do Requerimento -->
    <fieldset>
        <legend>Objeto do Requerimento</legend>
        <div class="opcoes-objeto">
            @php
                $assuntosMap = collect($modeloAtivo['assuntos_detalhes'] ?? [])->keyBy('descricao');
            @endphp
            @foreach($modeloAtivo['objetos'] ?? [] as $codigo => $descricao)
                @php
                    $assuntoItem = $assuntosMap[$descricao] ?? null;
                    $obs = $assuntoItem['observacao'] ?? null;
                    $indexAssunto = $loop->index;
                    $isChecked = old('objetoDoRequerimento') ? (old('objetoDoRequerimento') === $descricao) : $loop->first;
                @endphp
                <label style="display: flex; flex-direction: column; align-items: flex-start; margin-bottom: 8px; cursor: pointer;">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <input type="radio" 
                               name="objetoDoRequerimento" 
                               value="{{ $descricao }}" 
                               onchange="alternarObjetoOutro(false); mostrarDocumentosAssunto({{ $indexAssunto }})" 
                               {{ $isChecked ? 'checked' : '' }}>
                        {{ $descricao }}
                        @if(!empty($assuntoItem['link_norma']))
                            <a href="{{ $assuntoItem['link_norma'] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               aria-label="Consultar norma de {{ $descricao }}"
                               title="Consultar norma"
                               style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border: 1px solid #e6904f; border-radius: 50%; color: #e6904f; font-size: 12px; font-weight: 700; text-decoration: none;">
                                ?
                            </a>
                        @endif
                    </span>
                    @if(!empty($obs))
                        <small style="color: #e6904f; margin-left: 22px; font-size: 0.78rem;">{{ $obs }}</small>
                    @endif
                </label>
            @endforeach
            <label style="display: flex; flex-direction: column; align-items: flex-start; margin-bottom: 8px; cursor: pointer;">
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <input type="radio"
                           name="objetoDoRequerimento"
                           value="outro"
                           onchange="alternarObjetoOutro(true)"
                           {{ old('objetoDoRequerimento') === 'outro' ? 'checked' : '' }}>
                    Outro
                </span>
            </label>
        </div>

        <div class="campo" style="margin-top: 10px;">
            <label>Outro / Detalhe adicional (opcional):</label>
                 <small id="objeto-outro-ajuda" class="ajuda-campo-bloqueado"
                     style="display: {{ old('objetoDoRequerimento') === 'outro' ? 'none' : 'flex' }}; align-items: center; gap: 5px; margin-bottom: 6px; color: #64748b; font-size: 12px; line-height: 1.4;">
                <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Selecione â€œOutroâ€ para liberar este campo.
            </small>
            <input type="text" 
                   name="objeto_outro" 
                   value="{{ old('objeto_outro') }}"
                   @disabled(old('objetoDoRequerimento') !== 'outro')
                   style="background-color: {{ old('objetoDoRequerimento') === 'outro' ? '#fff' : '#f1f5f9' }}; border-color: {{ old('objetoDoRequerimento') === 'outro' ? '#ccc' : '#cbd5e1' }}; color: {{ old('objetoDoRequerimento') === 'outro' ? '#111827' : '#64748b' }}; cursor: {{ old('objetoDoRequerimento') === 'outro' ? 'text' : 'not-allowed' }};"
                   placeholder="Especifique caso necessÃ¡rio"
                   oninput="const el = document.getElementById('nome-assunto-outro-preview'); if(el) el.textContent = 'Outro: ' + this.value;">
        </div>
    </fieldset>

    <!-- 3. Justificativa / Motivo -->
    <fieldset>
        <legend>Justificativa / Motivo</legend>
        <div class="campo">
            <textarea name="motivo" rows="4" placeholder="Descreva os motivos da sua solicitaÃ§Ã£o...">{{ old('motivo') }}</textarea>
        </div>
    </fieldset>

    <button type="button" class="btn-enviar" onclick="mudarPasso(2)" style="margin-top: 10px;">PrÃ³ximo</button>
</div>

