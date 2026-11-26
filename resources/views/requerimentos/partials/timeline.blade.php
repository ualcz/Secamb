<div class="historico">
    <h3>Histórico da Tramitação</h3>

    <ul class="timeline">
        @php
            $listaHistoricos = isset($historicos) ? $historicos : ($requerimento->historicos ?? collect());
            $historicosTimeline = collect();

            foreach ($listaHistoricos as $itemHistorico) {
                $observacaoItem = (string) $itemHistorico->observacao;
                $atividadeDocumento = $itemHistorico->tipo_evento === \App\Models\HistoricoRequerimento::TIPO_EVENTO_DOCUMENTO_ANEXADO
                    || (
                        $itemHistorico->setor_id
                        && str_starts_with($observacaoItem, 'Documento anexado pelo setor.')
                    );

                if (!$atividadeDocumento) {
                    $itemHistorico->setAttribute('eventos_documentos', collect());
                    $historicosTimeline->push($itemHistorico);
                    continue;
                }

                $historicoStatus = $historicosTimeline->reverse()->first(
                    fn ($item) => $item->status === $itemHistorico->status
                );

                if (!$historicoStatus) {
                    $historicosTimeline->push($itemHistorico);
                    continue;
                }

                $eventosDocumento = $historicoStatus->getAttribute('eventos_documentos') ?? collect();
                $eventosDocumento->push($itemHistorico);
                $historicoStatus->setAttribute('eventos_documentos', $eventosDocumento);
            }
        @endphp

        @forelse($historicosTimeline as $historico)
            @php
                $rotuloObservacao = 'Observação';
                $textoObservacao = $historico->observacao;
                $servicoSolicitado = null;
                $rotaSetores = null;

                if (
                    trim((string) $textoObservacao) === 'Requerimento cadastrado no sistema.'
                    || preg_match('/^Status alterado para .+ pelo setor\.$/u', trim((string) $textoObservacao))
                ) {
                    $textoObservacao = null;
                }

                if ($textoObservacao && preg_match('/\[Serviço Solicitado:\s*(.+?)\]\R?/i', $textoObservacao, $matchServico)) {
                    $servicoSolicitado = $matchServico[1];
                    $textoObservacao = str_replace($matchServico[0], '', $textoObservacao);
                }

                // Identifica tramitação entre setores: "Encaminhado de A para B", "Despacho de A para B", etc.
                if ($textoObservacao && preg_match('/^(?:Encaminhado|Despacho|Orientação|Resposta) de\s+([^\n\.\:\,]+?)\s+para\s+([^\n\.\:\,]+?)(?:\.|\:)?(?:\R+|$)(.*)$/si', $textoObservacao, $mRota)) {
                    $rotaSetores = [
                        'origem' => trim($mRota[1]),
                        'destino' => trim($mRota[2]),
                    ];
                    $textoObservacao = $mRota[3];
                    $rotuloObservacao = 'Despacho / Orientação';
                } elseif ($textoObservacao && preg_match('/^(?:Orientação|Resposta) para:\s*([^\n\.\:\,]+?)(?:\.|\:)?(?:\R+|$)(.*)$/si', $textoObservacao, $mRota)) {
                    $origemDetectada = null;
                    if (isset($requerimento) && $requerimento->setorRetorno) {
                        $origemDetectada = $requerimento->setorRetorno->setor_sigla;
                    } elseif ($historico->usuario && $historico->usuario->relationLoaded('setoresSobResponsabilidade') && $historico->usuario->setoresSobResponsabilidade->isNotEmpty()) {
                        $origemDetectada = $historico->usuario->setoresSobResponsabilidade->first()->setor_sigla;
                    } elseif ($historico->usuario?->setoresSobResponsabilidade()?->exists()) {
                        $origemDetectada = $historico->usuario->setoresSobResponsabilidade()->first()?->setor_sigla;
                    } elseif (isset($requerimento) && $requerimento->setor) {
                        $origemDetectada = $requerimento->setor->setor_sigla;
                    }

                    $rotaSetores = [
                        'origem' => $origemDetectada,
                        'destino' => trim($mRota[1]),
                    ];
                    $textoObservacao = $mRota[2];
                    $rotuloObservacao = 'Orientação';
                }

                $eventosDocumento = $historico->getAttribute('eventos_documentos') ?? collect();
                $documentosProtocoloNoGrupo = $historico->status === \App\Models\Requerimento::STATUS_PROTOCOLADO
                    && (int) $historico->user_id === (int) ($requerimento->usuario_id ?? 0)
                    && $historico->documentos
                    && $historico->documentos->isNotEmpty();

                if ($documentosProtocoloNoGrupo) {
                    $eventosDocumento->push($historico);
                    $historico->setAttribute('eventos_documentos', $eventosDocumento);
                }

                $temDetalhes = !empty(trim($textoObservacao ?? ''))
                    || !empty($historico->nome_documento_solicitado)
                    || (!$documentosProtocoloNoGrupo && $historico->documentos && $historico->documentos->isNotEmpty());
                $totalDocumentosJuntados = $eventosDocumento->sum(fn ($evento) => $evento->documentos->count());
            @endphp
            <li class="timeline-item status-{{ Str::slug($historico->status) }}">
                <div class="timeline-badge"></div>
                <div class="timeline-panel">
                    {{-- Linha principal: Status · Data · Responsável · Detalhes --}}
                    <div class="timeline-row">
                        <span class="badge-status">{{ $historico->status }}</span>
                        <span class="timeline-date">{{ $historico->created_at->format('d/m/Y H:i') }}</span>
                        <span class="timeline-sep">·</span>
                        <div class="timeline-responsavel-inline" title="Responsável: {{ $historico->usuario?->nome ?? 'Sistema' }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span class="timeline-responsavel-nome">{{ $historico->usuario?->nome ?? 'Sistema' }}</span>
                        </div>

                        @if($temDetalhes)
                            <button type="button" class="timeline-toggle-btn" onclick="toggleTimelineDetalhes(this)" aria-expanded="false">
                                <span>detalhes</span>
                                <svg class="timeline-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if($eventosDocumento->isNotEmpty())
                        <details class="timeline-anexos-status">
                            <summary class="timeline-anexos-resumo">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.82-2.83l8.48-8.48"/>
                                </svg>
                                <strong>Documentos anexados</strong>
                                <span>{{ $totalDocumentosJuntados }} {{ $totalDocumentosJuntados === 1 ? 'arquivo' : 'arquivos' }}</span>
                                <svg class="timeline-anexos-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </summary>
                            <div class="timeline-anexos-lista">
                                @foreach($eventosDocumento as $eventoDocumento)
                                    @php
                                        $contextoEvento = $documentosProtocoloNoGrupo && $eventoDocumento->is($historico)
                                            ? ''
                                            : trim((string) $eventoDocumento->observacao);
                                        $prefixoLegadoAnexo = 'Documento anexado pelo setor.';
                                        if (str_starts_with($contextoEvento, $prefixoLegadoAnexo)) {
                                            $contextoEvento = trim(substr($contextoEvento, strlen($prefixoLegadoAnexo)));
                                        }
                                    @endphp
                                    <div class="timeline-anexo-item">
                                        <div class="timeline-anexo-meta">
                                            <time>{{ $eventoDocumento->created_at->format('d/m/Y H:i') }}</time>
                                            <span>por {{ $eventoDocumento->usuario?->nome ?? 'Servidor do setor' }}</span>
                                        </div>
                                        @if($contextoEvento !== '')
                                            <p class="timeline-anexo-contexto"><strong>Contexto:</strong> {{ $contextoEvento }}</p>
                                        @endif
                                        @foreach($eventoDocumento->documentos as $documento)
                                            <div class="timeline-anexo-arquivo">
                                                <div>
                                                    <strong>{{ $documento->titulo }}</strong>
                                                    <small>{{ $documento->nome_original }} · {{ $documento->tamanho_formatado }}</small>
                                                </div>
                                                <div class="timeline-anexo-links">
                                                    @if($documento->isVisualizavel())
                                                        <a href="{{ route('documentos.preview', $documento->id) }}" target="_blank" rel="noopener noreferrer">Visualizar</a>
                                                    @endif
                                                    <a href="{{ route('documentos.download', $documento->id) }}">Baixar</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    {{-- Informação de tramitação entre setores: dentro do card, visível antes de expandir --}}
                    @if($rotaSetores)
                        <div class="timeline-tramitacao-row">
                            <span class="timeline-rota-badge" title="Tramitação: {{ $rotaSetores['origem'] ? $rotaSetores['origem'] . ' → ' . $rotaSetores['destino'] : 'Para ' . $rotaSetores['destino'] }}">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="timeline-rota-icone" aria-hidden="true">
                                    <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                                <span class="timeline-rota-label">Tramitação:</span>
                                @if($rotaSetores['origem'])
                                    <span class="timeline-rota-setor">{{ $rotaSetores['origem'] }}</span>
                                    <span class="timeline-rota-seta">→</span>
                                @endif
                                <span class="timeline-rota-setor">{{ $rotaSetores['destino'] }}</span>
                            </span>
                        </div>
                    @endif

                    {{-- Conteúdo adicional expandido ao clicar em "detalhes" --}}
                    @if($temDetalhes)
                        <div class="timeline-detalhes-conteudo" style="display: none;">
                            @if($servicoSolicitado)
                                <div class="timeline-servico-tag">
                                    {{ $servicoSolicitado }}
                                </div>
                            @endif

                            @if(!empty(trim($textoObservacao ?? '')))
                                <div class="timeline-observacao">
                                    <strong>{{ $rotuloObservacao }}:</strong> <span>{!! nl2br(e(trim($textoObservacao))) !!}</span>
                                </div>
                            @endif

                            @if($historico->nome_documento_solicitado)
                                <div class="timeline-observacao">
                                    <strong>Documento Solicitado:</strong> <span>{{ $historico->nome_documento_solicitado }}</span>
                                </div>
                            @endif

                            @include('requerimentos.partials.historico-documentos', [
                                'documentos' => $documentosProtocoloNoGrupo ? collect() : $historico->documentos,
                                'historico' => $historico,
                                'requerimento' => $requerimento,
                            ])
                        </div>
                    @endif
                </div>
            </li>
        @empty
            <p>Nenhum registro no histórico até o momento.</p>
        @endforelse
    </ul>
</div>
