@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/show-requerimento.css') }}?v={{ filemtime(public_path('css/show-requerimento.css')) }}">

@section('content')
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
                        'Aberto' => 'badge-Aberto',
                        'Em Análise' => 'badge-analise',
                        'Concluído' => 'badge-concluido',
                        'Indeferido' => 'badge-indeferido',
                        'Despacho' => 'badge-despacho',
                        default => 'badge-analise'
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
                        <div class="info-grupo">
                            <span class="info-label">Bacia Hidrográfica</span>
                            <span class="info-valor">{{ $requerimento->empreendimento->bacia_hidrografica ?? 'Não informada' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('js/show-requerimento.js') }}?v={{ filemtime(public_path('js/show-requerimento.js')) }}"></script>
@endsection

