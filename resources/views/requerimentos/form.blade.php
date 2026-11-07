@extends('layouts.app')

@section('title', 'Novo Requerimento - SECAMB')
@section('tag', 'Cidadão')

@section('content')
<link rel="stylesheet" href="{{ asset('css/form.css') }}?v={{ filemtime(public_path('css/form.css')) }}">

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

        <form id="formRequerimento" action="{{ route('cidadao.enviar-email') }}" method="POST" enctype="multipart/form-data"
            data-draft-key="requerimento-{{ auth()->id() }}-{{ $modeloAtivo['id'] ?? 'geral' }}"
            data-restore-draft="{{ $errors->any() ? 'false' : 'true' }}"
            data-clear-draft="{{ session()->has('sucesso') ? 'true' : 'false' }}">
        @csrf
        <input type="hidden" name="setor_id" value="{{ $modeloAtivo['id'] ?? '' }}">
        <input type="hidden" name="setor" value="{{ $modeloAtivo['id'] ?? '' }}">

        {{-- Etapa 1: Identificação, Objeto e Justificativa --}}
        @include('requerimentos.partials.dados-requerimento')

        {{-- Etapa 2: Documentos e Anexos --}}
        @include('requerimentos.partials.documentos-anexos')
    </form>

    <script src="{{ asset('js/form.js') }}?v={{ filemtime(public_path('js/form.js')) }}"></script>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                mudarPasso(2);
            });
        </script>
    @endif
</div>
@endsection
