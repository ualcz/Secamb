@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/show-requerimento.css') }}?v={{ filemtime(public_path('css/show-requerimento.css')) }}">

@section('content')
<div class="detalhes-container">
    <x-btn-voltar style="grid-column: span 2;"/>

    {{-- 1. Histórico e Linha do Tempo da Tramitação --}}
    @include('setor.requerimentos.partials.historico')

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
                'Enviando email de notificação...',
                'Atualizando sistema...',
                'Só mais um instante...'
            ]"
        />

        {{-- 2. Informações Cadastrais do Solicitante (Accordion) --}}
        @include('setor.requerimentos.partials.info-usuario')

        {{-- 3. Dados do Requerimento e Empreendimento Vinculado --}}
        @include('setor.requerimentos.partials.dados-requerimento')

        {{-- 4. Painel de Ações do Setor (Status, Despacho, Encaminhamento) --}}
        @include('setor.requerimentos.partials.acoes')
    </div>
</div>

<script>
    window.catalogoSetoresDestino = @json($catalogoSetoresDestino ?? []);
    window.oldAssuntoDestinoId = @json(old('assunto_destino_id'));
</script>
<script src="{{ asset('js/show-requerimento.js') }}?v={{ filemtime(public_path('js/show-requerimento.js')) }}"></script>
@endsection
