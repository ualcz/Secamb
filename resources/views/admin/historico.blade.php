@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/show-requerimento.css') }}?v={{ filemtime(public_path('css/show-requerimento.css')) }}">

@section('content')
@php
    $caracterizacaoEdicao = $requerimento->caracterizacao_empreendimento ?? [];
    if (!$caracterizacaoEdicao && $requerimento->empreendimento) {
        $empreendimentoLegado = $requerimento->empreendimento;
        $pendenciasLegadas = [
            'notificacao_pendencia' => $empreendimentoLegado->notificacao_pendencia,
            'embargo_pendencia' => $empreendimentoLegado->embargo_pendencia,
            'advertencia_pendencia' => $empreendimentoLegado->advertencia_pendencia,
            'apreensao_pendencia' => $empreendimentoLegado->apreensao_pendencia,
            'multa_pendencia' => $empreendimentoLegado->multa_pendencia,
            'interdicao_pendencia' => $empreendimentoLegado->interdicao_pendencia,
        ];
        $temLicencaLegada = $empreendimentoLegado->licenca_anterior_tipo
            || $empreendimentoLegado->licenca_anterior_processo
            || $empreendimentoLegado->resolucao_cepram
            || $empreendimentoLegado->portaria_inema;
        $unidadeConservacaoLegada = trim((string) $empreendimentoLegado->unidade_conservacao);
        $unidadeConservacaoRespostaLegada = $unidadeConservacaoLegada === ''
            ? ''
            : (stripos($unidadeConservacaoLegada, 'não possui') !== false
                || stripos($unidadeConservacaoLegada, 'nao possui') !== false
                ? 'nao'
                : 'sim');
        $caracterizacaoEdicao = [
            'bacia_hidrografica' => $empreendimentoLegado->bacia_hidrografica,
            'recurso_hidrico' => $empreendimentoLegado->recurso_hidrico,
            'latitude' => $empreendimentoLegado->latitude,
            'longitude' => $empreendimentoLegado->longitude,
            'unidade_conservacao_resposta' => $unidadeConservacaoRespostaLegada,
            'unidade_conservacao_nome' => $empreendimentoLegado->unidade_conservacao,
            'licenca_anterior' => $temLicencaLegada ? 'sim' : '',
            'localizacao_licenca' => $empreendimentoLegado->localizacao_licenca,
            'licenca_anterior_tipo' => $empreendimentoLegado->licenca_anterior_tipo,
            'licenca_anterior_processo' => $empreendimentoLegado->licenca_anterior_processo,
            'resolucao_cepram' => $empreendimentoLegado->resolucao_cepram,
            'resolucao_cepram_validade' => substr((string) $empreendimentoLegado->resolucao_cepram_validade, 0, 10),
            'portaria_inema' => $empreendimentoLegado->portaria_inema,
            'portaria_inema_validade' => substr((string) $empreendimentoLegado->portaria_inema_validade, 0, 10),
            'processo_semma' => $empreendimentoLegado->processo_semma_numero || $empreendimentoLegado->processo_semma_tipo ? 'sim' : '',
            'processo_semma_numero' => $empreendimentoLegado->processo_semma_numero,
            'processo_semma_tipo' => $empreendimentoLegado->processo_semma_tipo,
            'pendencia_ambiental' => count(array_filter($pendenciasLegadas)) ? 'sim' : '',
            ...$pendenciasLegadas,
        ];
    }
@endphp
<div class="detalhes-container">
    <x-btn-voltar style="grid-column: span 2;" />

    @include('requerimentos.partials.timeline')

    <div class="protocolo-info">
        <div class="card-painel">
            <div class="card-header-flex">
                <div>
                    <span class="info-label">Nº Protocolo</span>
                    <h1 class="card-titulo" style="font-size: 1.5rem; color: #2563eb;">
                        {{ $requerimento->id }}
                    </h1>
                </div>
                @php
                    $statusClass = match($requerimento->status) {
                        \App\Models\Requerimento::STATUS_PROTOCOLADO => 'badge-protocolado',
                        \App\Models\Requerimento::STATUS_EM_ANALISE => 'badge-em-analise',
                        \App\Models\Requerimento::STATUS_EM_ANALISE_TECNICA => 'badge-em-analise-tecnica',
                        \App\Models\Requerimento::STATUS_ANALISE_CONCLUIDA => 'badge-analise-concluida',
                        \App\Models\Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO => 'badge-aguardando-complementacao',
                        \App\Models\Requerimento::STATUS_AGUARDANDO_VISTORIA => 'badge-aguardando-vistoria',
                        \App\Models\Requerimento::STATUS_VISTORIA_CONCLUIDA => 'badge-vistoria-concluida',
                        \App\Models\Requerimento::STATUS_DEFERIDO => 'badge-deferido',
                        \App\Models\Requerimento::STATUS_LICENCA_EMITIDA => 'badge-licenca-emitida',
                        \App\Models\Requerimento::STATUS_INDEFERIDO => 'badge-indeferido',
                        \App\Models\Requerimento::STATUS_ENCERRADO,
                        \App\Models\Requerimento::STATUS_ARQUIVADO_COMPLEMENTACAO => 'badge-encerrado',
                        default => 'badge-protocolado'
                    };
                @endphp
                <span class="badge {{ $statusClass }}">
                    {{ $requerimento->status }}
                </span>
            </div>

            <div class="grid-2">
                <div class="info-grupo">
                    <span class="info-label">Objeto do Requerimento</span>
                    <span class="info-valor">{{ $requerimento->objetoDoRequerimento }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Data e Hora de Envio</span>
                    <span class="info-valor">{{ $requerimento->created_at->format('d/m/Y \\Ã \\s H:i') }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Cidadão</span>
                    <span class="info-valor">{{ $requerimento->usuario?->nome ?? 'NÃ£o informado' }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Setor</span>
                    <span class="info-valor">{{ $requerimento->setor?->setor_sigla ?? 'NÃ£o informado' }}</span>
                </div>
            </div>

            <div class="info-grupo" style="margin-top: 0.75rem;">
                <span class="info-label">Motivo / Solicitação</span>
                <div class="box-motivo">
                    <span class="info-valor">{{ $requerimento->motivo }}</span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-sucesso">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="erros">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($requerimento->empreendimento)
                <div style="margin-top: 1rem; padding: 14px 18px; border-radius: 8px; border: 1px solid #bbf7d0; border-left: 4px solid #059669; background-color: #f0fdf4;">
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
                    </div>
                    @include('requerimentos.partials.caracterizacao-resumo')
                </div>
            @endif

            <details style="margin-top: 1rem;">
                <summary style="cursor: pointer; font-weight: 600;">Editar caracterização desta solicitação</summary>
                <form action="{{ route('admin.historico.caracterizacao', $requerimento) }}" method="POST" data-caracterizacao-form>
                    @csrf
                    @method('PATCH')
                    @include('requerimentos.partials.caracterizacao-campos')
                    <button type="submit" class="btn-atualizar">Salvar caracterização</button>
                </form>
            </details>
        </div>
    </div>
</div>

<script src="{{ asset('js/show-requerimento.js') }}?v={{ filemtime(public_path('js/show-requerimento.js')) }}"></script>
<script src="{{ asset('js/caracterizacao-empreendimento.js') }}?v={{ filemtime(public_path('js/caracterizacao-empreendimento.js')) }}"></script>
@endsection
