<!-- Etapa 2: Documentos Obrigatórios e Anexos Complementares -->
<div class="form-step" data-step="2" style="display: none;">
    <!-- Documentos e Anexos -->
    <fieldset class="secao-anexos">
        <legend>Documentos e Anexos</legend>

        @php
            $assuntosList = array_values($modeloAtivo['assuntos_detalhes'] ?? []);
            $assuntoSelecionadoOld = old('objetoDoRequerimento');
        @endphp

        @foreach($assuntosList as $idx => $assuntoItem)
            @php
                $isAssuntoAtivo = $assuntoSelecionadoOld
                    ? ($assuntoSelecionadoOld === $assuntoItem['descricao'])
                    : ($idx === 0);
                $docs = $assuntoItem['documentos_obrigatorios'] ?? [];
            @endphp

            <div class="bloco-documentos-assunto" id="bloco-doc-{{ $idx }}" style="{{ $isAssuntoAtivo ? '' : 'display: none;' }}">
                <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px;">
                    <span style="font-size: 12px; color: #15803d; text-transform: uppercase; font-weight: 600; display: block;">Assunto Selecionado:</span>
                    <strong style="font-size: 16px; color: #047857;">{{ $assuntoItem['descricao'] }}</strong>
                </div>
                @if(count($docs) > 0)
                    <h3 style="font-size: 1.25rem; text-align: center; margin-bottom: 12px;">Documentos Obrigatórios</h3>
                    @foreach($docs as $doc)
                        @php
                            $tiposAceitos = !empty($doc['tipos_aceitos'])
                                ? '.' . str_replace(',', ',.', str_replace([' ', '.'], ['', ''], strtolower($doc['tipos_aceitos'])))
                                : '.pdf,.doc,.docx,.png,.jpg,.jpeg';
                        @endphp
                        <div class="campo" style="background: rgb(249, 235, 235); padding: 20px; border-radius: 6px; border: 1px solid rgb(255, 203, 203); margin-bottom: 16px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px;">
                                {{ $doc['nome'] }}
                                @if($doc['obrigatorio'])
                                    <span style="color: #dc2626; font-weight: normal;">(Obrigatório)</span>
                                @else
                                    <span style="color: #6b7280; font-weight: normal;">(Opcional)</span>
                                @endif
                            </label>
                            @if(!empty(trim($doc['descricao'] ?? '')))
                                <div style="display: block; background: #fffbeb; color: #78350f; padding: 10px 14px; margin: 8px 0 12px; font-size: 15px; font-weight: 600; line-height: 1.5; border: 1px solid #fcd34d;">{{ $doc['descricao'] }}</div>
                            @endif
                            @if(!empty($doc['link_modelo']))
                                <a href="{{ $doc['link_modelo'] }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-flex; align-items: center; gap: 5px; margin: 4px 0 12px; color: #1d4ed8; font-size: 13px; line-height: 1.2; font-weight: 600;">
                                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    Baixar modelo
                                </a>
                            @endif

                            <x-file-input
                                name="documentos[{{ $doc['id'] ?? $loop->index }}]"
                                required="{{ $doc['obrigatorio'] ? 'true' : 'false' }}"
                                label="{{ $doc['nome'] }}"
                                helpText=""
                                accept="{{ $tiposAceitos }}"
                            />
                        </div>
                    @endforeach
                @else
                    <p style="color: #666; font-size: 14px; margin-bottom: 12px;">
                        Este assunto não possui documentos obrigatórios.
                    </p>
                @endif
            </div>
        @endforeach

        <!-- Bloco para a opção "Outro" -->
        <div class="bloco-documentos-assunto" id="bloco-doc-outro" style="display: none;">
            <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px;">
                <span style="font-size: 12px; color: #15803d; text-transform: uppercase; font-weight: 600; display: block;">Assunto Selecionado:</span>
                <strong id="nome-assunto-outro-preview" style="font-size: 16px; color: #047857;">Outro Requerimento</strong>
            </div>
            <p style="color: #666; font-size: 14px; margin-bottom: 12px;">
                Requerimento personalizado. Caso possua documentos comprobatórios, anexe no campo complementar abaixo.
            </p>
        </div>

        <!-- Anexos complementares para qualquer requerimento -->
        <x-file-input />
    </fieldset>

    <div class="step-nav">
        <button type="button" class="btn-voltar" onclick="mudarPasso(1)">Anterior</button>
        <button type="submit" class="btn-enviar" id="btn-enviar-requerimento">
            Enviar Requerimento
        </button>
    </div>
</div>
