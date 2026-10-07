<div class="historico">
    <h3>Histórico da Tramitação</h3>

    <ul class="timeline">
        @forelse($requerimento->historicos as $historico)
            <li class="timeline-item status-{{ Str::slug($historico->status) }}">
                <div class="timeline-badge"></div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <span class="badge-status">{{ $historico->status }}</span>
                        <span class="timeline-date">
                            {{ $historico->created_at->format('d/m/Y às H:i') }}
                        </span>
                    </div>
                    <div class="timeline-body">
                        <p><strong>Responsável:</strong> {{ $historico->usuario->nome ?? 'Sistema' }}</p>
                        @if($historico->observacao)
                            @php
                                $rotuloObservacao = 'Observação';
                                $textoObservacao = $historico->observacao;
                                $servicoSolicitado = null;

                                if (preg_match('/\[Serviço Solicitado:\s*(.+?)\]\R?/i', $textoObservacao, $matchServico)) {
                                    $servicoSolicitado = $matchServico[1];
                                    $textoObservacao = str_replace($matchServico[0], '', $textoObservacao);
                                }

                                if (preg_match('/^(Orientação para|Resposta para):\s*(.+?)\R(.*)$/s', $textoObservacao, $partes)) {
                                    $rotuloObservacao = $partes[1] . ': ' . $partes[2];
                                    $textoObservacao = $partes[3];
                                } elseif (preg_match('/^Encaminhado de .+ para (.+?)\.\s*\R+\s*(.*)$/s', $textoObservacao, $partes)) {
                                    $rotuloObservacao = 'Orientação para: ' . $partes[1];
                                    $textoObservacao = $partes[2];
                                } elseif (preg_match('/^Resposta de .+ para (.+?)\.\s*\R+\s*(.*)$/s', $textoObservacao, $partes)) {
                                    $rotuloObservacao = 'Resposta para: ' . $partes[1];
                                    $textoObservacao = $partes[2];
                                }
                            @endphp
                            <div class="timeline-observacao">
                                <strong>{{ $rotuloObservacao }}</strong>
                                @if($servicoSolicitado)
                                    <div style="margin: 6px 0; padding: 4px 10px; background: #ede9fe; font-size: 0.8125rem; font-weight: 600;">
                                        Serviço / Demanda: {{ $servicoSolicitado }}
                                    </div>
                                @endif
                                <div>{!! nl2br(e(trim($textoObservacao))) !!}</div>
                            </div>
                        @endif
                        @if($historico->nome_documento_solicitado)
                            <div class="timeline-observacao">
                                <strong>Documento Solicitado:</strong> {{ $historico->nome_documento_solicitado }}
                            </div>
                        @endif

                        {{-- DOCUMENTOS ANEXADOS NESTA TRAMITAÇÃO --}}
                        @include('requerimentos.partials.historico-documentos', [
                            'documentos' => $historico->documentos,
                            'historico' => $historico,
                            'requerimento' => $requerimento,
                        ])
                    </div>
                </div>
            </li>
        @empty
            <p>Nenhum registro no histórico até o momento.</p>
        @endforelse
    </ul>
</div>
