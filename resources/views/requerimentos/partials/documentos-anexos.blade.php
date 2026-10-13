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
                    <h3 style="font-size: 1.25rem; text-align: center; margin-bottom: 16px; font-weight: 700; color: #1f2937;">Documentos Obrigatórios</h3>
                    <div class="documentos-grid">
                        @foreach($docs as $doc)
                            @php
                                $tiposAceitos = !empty($doc['tipos_aceitos'])
                                    ? '.' . str_replace(',', ',.', str_replace([' ', '.'], ['', ''], strtolower($doc['tipos_aceitos'])))
                                    : '.pdf,.doc,.docx,.png,.jpg,.jpeg';
                            @endphp
                            <div class="campo doc-card {{ $doc['obrigatorio'] ? 'doc-card-obrigatorio' : 'doc-card-opcional' }}">
                                <div class="doc-card-header">
                                    <div class="doc-card-title">
                                        <span class="doc-nome">{{ $doc['nome'] }}</span>
                                    </div>
                                    @if($doc['obrigatorio'])
                                        <span class="doc-badge badge-obrigatorio">Obrigatório</span>
                                    @else
                                        <span class="doc-badge badge-opcional">Opcional</span>
                                    @endif
                                </div>

                                @if(!empty(trim($doc['descricao'] ?? '')))
                                    <div class="doc-card-descricao">
                                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="min-width: 16px; margin-top: 2px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        <span>{{ $doc['descricao'] }}</span>
                                    </div>
                                @endif

                                @if(!empty($doc['link_modelo']))
                                    <a href="{{ $doc['link_modelo'] }}" target="_blank" rel="noopener noreferrer" class="doc-link-modelo">
                                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        Baixar modelo
                                    </a>
                                @endif

                                <div class="doc-card-upload">
                                    <x-file-input
                                        name="documentos[{{ $doc['id'] ?? $loop->index }}]"
                                        required="{{ $doc['obrigatorio'] ? 'true' : 'false' }}"
                                        label=""
                                        :compact="true"
                                        :multiple="false"
                                        accept="{{ $tiposAceitos }}"
                                    />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="doc-empty-state">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>Este assunto não possui documentos obrigatórios.</span>
                    </div>
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
                Requerimento personalizado. Descreva os detalhes na justificativa do requerimento.
            </p>
        </div>
    </fieldset>

    <div class="step-nav">
        <button type="button" class="btn-voltar" onclick="mudarPasso(1)">Anterior</button>
        <button type="submit" class="btn-enviar" id="btn-enviar-requerimento">
            Enviar Requerimento
        </button>
    </div>
</div>
