@php
    $historicoDoProprioUsuario = isset($historico, $requerimento)
        && (int) $historico->user_id === (int) $requerimento->usuario_id;

    $respostaDoAluno = $historicoDoProprioUsuario
        && $historico->status === 'Em Análise';

    // Exibe todos os documentos quando:
    // - status é Concluído, Indeferido, Despacho, ou o requerimento foi concluído
    // - histórico de análise com solicitação de novo documento
    // - histórico inicial do próprio cidadão (Aberto)
    $deveExibirTodosDocumentos = $documentos->isNotEmpty() && (
        ($historico?->status ?? null) === 'Concluído'
        || ($historico?->status ?? null) === 'Indeferido'
        || ($historico?->status ?? null) === 'Despacho'
        || ($requerimento?->status ?? null) === 'Concluído'
        || (($historico?->status ?? null) === 'Em Análise' && !empty($historico?->solicita_novo_documento))
        || (($historico?->status ?? null) === 'Aberto' && $historicoDoProprioUsuario)
        || $respostaDoAluno
    );

    $documentosExibidos = $documentos->filter(function ($doc) use ($respostaDoAluno, $deveExibirTodosDocumentos) {
        if ($deveExibirTodosDocumentos) {
            return true;
        }

        return in_array($doc->nome_documento, ['Modelo do Requerimento', 'Requerimento enviado'], true)
            || $respostaDoAluno;
    });
@endphp

@if($documentosExibidos->isNotEmpty())
    <div class="timeline-documentos">
        <details class="timeline-doc-dropdown">
            <summary class="timeline-doc-header">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <span>Documentos ({{ $documentosExibidos->count() }})</span>
                <svg class="timeline-doc-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                </svg>
            </summary>

            <div class="timeline-doc-lista">
                @foreach($documentosExibidos as $doc)
                    @php
                        $tituloDocumento = in_array(
                            $doc->nome_documento,
                            ['Modelo do Requerimento', 'Requerimento enviado'],
                            true
                        ) ? 'Requerimento enviado' : $doc->titulo;
                    @endphp
                    <div class="doc-card-item">
                    <div class="doc-card-icon">
                        @if($doc->isPdf())
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        @elseif($doc->isImagem())
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        @else
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                        @endif
                    </div>

                    <div class="doc-card-info">
                        <strong class="doc-titulo-principal" title="{{ $tituloDocumento }}">{{ $tituloDocumento }}</strong>
                        <div class="doc-detalhes-secundarios">
                            <span class="doc-nome-arq" title="{{ $doc->nome_original }}">{{ $doc->nome_original }}</span>
                            <span class="doc-divisor">&bull;</span>
                            <span class="doc-tam">{{ $doc->tamanho_formatado }}</span>
                        </div>
                    </div>

                    <div class="doc-card-acoes">
                        {{-- Visualizar: abre em nova aba diretamente --}}
                        @if($doc->isVisualizavel())
                            <a href="{{ route('documentos.preview', $doc->id) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn-acao-doc btn-visualizar-doc"
                               title="Abrir documento em nova aba">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span>Visualizar</span>
                            </a>
                        @endif

                        <a href="{{ route('documentos.download', $doc->id) }}"
                           class="btn-acao-doc btn-baixar-doc"
                           title="Baixar arquivo">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Baixar</span>
                        </a>
                    </div>
                    </div>
                @endforeach
            </div>
        </details>
    </div>
@endif
