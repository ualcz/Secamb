@extends('layouts.app')

@section('title', 'Novo Requerimento - SECAMB')
@section('tag', 'Cidadão')

@section('content')
@vite([
    'resources/css/requerimentos/form.css',
    'resources/css/requerimentos/form-etapa-1.css',
    'resources/css/requerimentos/form-etapa-2.css',
    'resources/css/requerimentos/form-etapa-3.css',
])

<div class="form-box">
    <!-- Overlay de Carregamento -->
    <x-loading-overlay form-id="formRequerimento" />

    <!-- Cabeçalho Hierárquico do Requerimento e Setor Responsável -->
    <div class="req-header">
        <h1 class="req-header-titulo">{{ $modeloAtivo['titulo'] ?? 'Novo Requerimento' }}</h1>

        <div class="req-setor-wrap">
            <span class="req-setor-label">Setor:</span>

            <div class="req-setores-nav">
                @foreach($modelos as $mod)
                    @php 
                        $isActive = (($modeloChave ?? '') == $mod['id']);
                        $nomeDiferente = !empty($mod['setor_nome']) && trim(strtolower($mod['setor_nome'])) !== trim(strtolower($mod['setor_sigla']));
                    @endphp
                    <a href="{{ route('requerimentos.cidadao.novo', array_merge(['setor' => $mod['id']], request()->filled('empreendimento_id') ? ['empreendimento_id' => request('empreendimento_id')] : [])) }}"
                       class="req-setor-badge {{ $isActive ? 'active' : '' }}"
                       title="{{ $mod['setor_nome'] ?? $mod['setor_sigla'] }}">
                        @if($isActive)
                            <span class="req-setor-dot"></span>
                        @endif
                        <span class="req-setor-sigla">{{ $mod['setor_sigla'] }}</span>
                        @if($isActive && $nomeDiferente)
                            <span class="req-setor-nome-extenso">{{ $mod['setor_nome'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if (session('sucesso'))
        <div class="sucesso">✓ {{ session('sucesso') }}</div>
    @endif

    @if ($errors->any())
        <div class="erros">
            <strong>Erros:</strong>
            <ul style="margin: 5px 0 0 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p id="status-rascunho-requerimento" class="rascunho-status" role="status" aria-live="polite"></p>

    @php
        $camposEtapaCaracterizacao = [
            'bacia_hidrografica', 'recurso_hidrico', 'latitude', 'longitude',
            'unidade_conservacao_resposta', 'unidade_conservacao_nome',
            'licenca_anterior', 'localizacao_licenca', 'licenca_anterior_tipo',
            'licenca_anterior_processo', 'resolucao_cepram', 'resolucao_cepram_validade',
            'portaria_inema', 'portaria_inema_validade', 'processo_semma',
            'processo_semma_numero', 'processo_semma_tipo', 'pendencia_ambiental',
            'notificacao_pendencia', 'embargo_pendencia', 'advertencia_pendencia',
            'apreensao_pendencia', 'multa_pendencia', 'interdicao_pendencia',
        ];
        $erroEtapaCaracterizacao = $errors->has('motivo')
            || $errors->has('empreendimento_id')
            || $errors->hasAny($camposEtapaCaracterizacao);
    @endphp

        <form id="formRequerimento" action="{{ route('cidadao.enviar-email') }}" method="POST" enctype="multipart/form-data"
            data-draft-key="requerimento-{{ auth()->id() }}-{{ $modeloAtivo['id'] ?? 'geral' }}"
            data-draft-url="{{ route('requerimentos.rascunhos.show', ['setor' => $modeloAtivo['id'] ?? 0]) }}"
            data-draft-files-url="{{ route('requerimentos.rascunhos.arquivos.salvar', ['setor' => $modeloAtivo['id'] ?? 0]) }}"
            data-form-url="{{ route('requerimentos.cidadao.novo', ['setor' => $modeloAtivo['id'] ?? 0]) }}"
            data-restore-draft="true"
            data-step-on-error="{{ $errors->has('objetoDoRequerimento') ? 1 : ($erroEtapaCaracterizacao ? 2 : ($errors->any() ? 3 : '')) }}"
            data-motivo-invalido="{{ $errors->has('motivo') ? 'true' : 'false' }}"
            data-clear-draft="{{ session()->has('sucesso') ? 'true' : 'false' }}">
        @csrf
        <input type="hidden" name="setor_id" value="{{ $modeloAtivo['id'] ?? '' }}">
        <input type="hidden" name="setor" value="{{ $modeloAtivo['id'] ?? '' }}">

        {{-- Etapa 1: Identificação e Objeto --}}
        @include('requerimentos.partials.dados-requerimento')

        {{-- Etapa 2: Descrição da Solicitação --}}
        @include('requerimentos.partials.descricao-requerimento')

        {{-- Etapa 3: Documentos e Anexos --}}
        @include('requerimentos.partials.documentos-anexos')
    </form>

    @vite('resources/js/entries/requerimento-form.js')

</div>
@endsection
