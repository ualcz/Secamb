@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/show-requerimento.css') }}?v={{ filemtime(public_path('css/show-requerimento.css')) }}">

@section('content')
<div class="detalhes-container">
    <x-btn-voltar/>
    <a href="{{ route('requerimentos.gerar-comprovante', ['id' => $requerimento->id]) }}"
    target="_blank"
    class="req-btn-imprimir"
    >
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimir comprovante
    </a>
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
                                {{ $historico->created_at->format('d/m/Y \à\s H:i') }}
                            </span>
                        </div>
                        <div class="timeline-body">
                            <p><strong>Responsável:</strong> {{ $historico->usuario->nome ?? 'Sistema' }}</p>
                            @if($historico->observacao)
                                @php
                                    $textoObservacao = $historico->observacao;
                                    $servicoSolicitado = null;
                                    if (preg_match('/\[Serviço Solicitado:\s*(.+?)\]\R?/i', $textoObservacao, $matchServico)) {
                                        $servicoSolicitado = $matchServico[1];
                                        $textoObservacao = str_replace($matchServico[0], '', $textoObservacao);
                                    }
                                @endphp
                                <div class="timeline-observacao">
                                    <strong>Observação:</strong>
                                    @if($servicoSolicitado)
                                        <div style="margin: 6px 0; padding: 4px 10px; background: #ede9fe; color: #5b21b6; border-left: 3px solid #7c3aed; border-radius: 4px; font-size: 0.8125rem; font-weight: 600;">
                                            📋 Serviço / Demanda: {{ $servicoSolicitado }}
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

    <div class="protocolo-info">
        @if(session('sucesso'))
            <div class="alert-sucesso">
                {{ session('sucesso') }}
            </div>
        @endif
        <x-loading-overlay
            form-id="formCorrecao"
            :mensagens="[
                'Atualizando status...',
                'Enviando email para o setor...',
                'Atualizando sistema...',
                'Só mais um instante...',
            ]"
        />

            @php
                $ultimoHistorico = $requerimento->historicos->sortByDesc('created_at')->first();
            @endphp

            @if($requerimento->status === 'Indeferido' || ($ultimoHistorico && $ultimoHistorico->solicita_novo_documento))
                <div class="alert-acao-necessaria card-painel">
                    <div class="alert-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <h4 class="alert-titulo">Ação Necessária: Documento Solicitado</h4>
                    </div>

                    <div class="alert-detalhes">
                        <div class="info-grupo">
                            <span class="info-label">OBSERVAÇÃO DO SETOR</span>
                            <div class="box-motivo">
                                <span class="info-valor">{{ $ultimoHistorico->observacao }}</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('requerimentos.reenviar', $requerimento->id) }}" method="POST" enctype="multipart/form-data" id="formCorrecao" class="form-correcao">
                        @csrf

                        <div class="mb-3">
                            <label for="motivo_correcao" class="info-label">DESCRIÇÃO DAS CORREÇÕES / JUSTIFICATIVA</label>
                            <textarea name="motivo_correcao" id="motivo_correcao" class="form-control" rows="4" placeholder="Informe aqui os detalhes da correção..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="arquivos" class="info-label">{{ $ultimoHistorico->nome_documento_solicitado ?? 'Documento indeferido' }} / DOCUMENTOS CORRIGIDOS</label>
                            <input type="file" name="arquivos[]" id="arquivos" class="form-control file-input" multiple required>
                        </div>

                        <button type="submit" class="btn-atualizar btn-enviar-correcao">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 7-7 7 7"/>
                                <path d="M12 19V5"/>
                            </svg>
                            Enviar Correção
                        </button>
                    </form>
                </div>
            @endif


        {{-- CABEÇALHO DO REQUERIMENTO --}}
        <div class="card-painel">
            <div class="card-header-flex">
                <div>
                    <span class="info-label">Nº Protocolo</span>
                    <h1 class="card-titulo" style="font-size: 1.5rem; color: #2563eb;">
                        {{ $requerimento->id }}
                    </h1>
                </div>
                <div>
                    @php
                        $statusClass = match($requerimento->status_aluno) {
                            'Aberto' => 'badge-Aberto',
                            'Em Análise' => 'badge-analise',
                            'Concluído' => 'badge-concluido',
                            'Indeferido' => 'badge-indeferido',
                            default => 'badge-analise'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                        {{ $requerimento->status_aluno }}
                    </span>
                </div>
            </div>

            <div class="grid-2">
                <div class="info-grupo">
                    <span class="info-label">Objeto do Requerimento</span>
                    <span class="info-valor">{{ $requerimento->objetoDoRequerimento }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Data e Hora de Envio</span>
                    <span class="info-valor">{{ $requerimento->created_at->format('d/m/Y \à\s H:i') }}</span>
                </div>
            </div>

            <div class="info-grupo" style="margin-top: 0.75rem;">
                <span class="info-label">Motivo / Solicitação</span>
                <div class="box-motivo">
                    <span class="info-valor">{{ $requerimento->motivo }}</span>
                </div>
            </div>

            @if($requerimento->empreendimento)
                <div style="margin-top: 1rem; padding: 14px 18px; border-radius: 8px; border: 1px solid #bbf7d0; background-color: #f0fdf4;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <svg width="18" height="18" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <strong style="color: #065f46; font-size: 0.95rem;">Empreendimento Vinculado</strong>
                    </div>
                    <div class="grid-2">
                        <div class="info-grupo">
                            <span class="info-label">Nome / Razão Social</span>
                            <span class="info-valor" style="font-weight: 600; color: #0f172a;">{{ $requerimento->empreendimento->nome }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">CNPJ</span>
                            <span class="info-valor">{{ $requerimento->empreendimento->cnpj ?? 'Não informado' }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">Localização</span>
                            <span class="info-valor">{{ $requerimento->empreendimento->endereco_completo ?: 'Seabra - BA' }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">Bacia Hidrográfica</span>
                            <span class="info-valor">{{ $requerimento->empreendimento->bacia_hidrografica ?? 'Não informada' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div style="background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-size: 0.875rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 0.875rem;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


        </div>

    </div>
</div>

<script src="{{ asset('js/show-requerimento.js') }}"></script>
@endsection
