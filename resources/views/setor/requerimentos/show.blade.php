@extends('layouts.app')

@vite('resources/css/requerimentos/show.css')

@section('content')
<div class="detalhes-container">
    <x-btn-voltar style="grid-column: span 2;"/>

    {{-- 1. Histórico e Linha do Tempo da Tramitação --}}
    @include('requerimentos.partials.timeline')

    <div class="protocolo-info">
        @if(session('success') || session('sucesso'))
            <div class="alert-sucesso">
                {{ session('success') ?? session('sucesso') }}
            </div>
        @endif

        {{-- Ações disponíveis para o setor --}}
        @include('setor.requerimentos.partials.acoes')

        {{-- Informações Cadastrais do Solicitante (Accordion) --}}
        @include('setor.requerimentos.partials.info-usuario')

        {{-- Dados do Requerimento e Empreendimento Vinculado --}}
        @include('setor.requerimentos.partials.dados-requerimento')
    </div>
</div>

<div
    id="acao-loading-overlay"
    class="acao-loading-overlay"
    role="status"
    aria-live="polite"
    aria-hidden="true"
    tabindex="-1"
    hidden
>
    <div class="acao-loading-card">
        <span class="acao-loading-spinner" aria-hidden="true"></span>
        <p class="acao-loading-titulo" data-loading-message>Processando solicitação...</p>
        <p class="acao-loading-ajuda">Aguarde enquanto o sistema registra a ação.</p>
    </div>
</div>

@vite('resources/js/entries/requerimento-show.js')
@endsection
