@extends('layouts.app')

@section('title', 'Empreendimentos - SECAMB')
@section('tag', 'Empreendimentos')

@section('content')
<link rel="stylesheet" href="{{ asset('css/empreendimentos.css') }}?v={{ filemtime(public_path('css/empreendimentos.css')) }}">

<div class="emp-container">

    {{-- Cabeçalho --}}
    <div class="emp-header">
        <div class="emp-title-area">
            <h1>Empreendimentos</h1>
        </div>
    </div>

    {{-- Notificações de Sucesso / Erro --}}
    @if(session('sucesso'))
        <div class="emp-alert emp-alert-success">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('sucesso') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="emp-alert emp-alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Busca e ação principal --}}
    <div class="emp-toolbar">
        <form method="GET" action="{{ route('empreendimentos.index') }}" class="emp-toolbar-form {{ !$empreendimentos->isEmpty() || request()->filled('busca') ? 'has-search' : '' }}">
            @if(!$empreendimentos->isEmpty() || request()->filled('busca'))
                <div class="emp-search-field">
                    <label for="emp-search">Buscar empreendimentos</label>
                    <input
                        type="text"
                        id="emp-search"
                        name="busca"
                        value="{{ request('busca') }}"
                        placeholder="Buscar empreendimento, CNPJ ou bairro..."
                        class="emp-input"
                        aria-label="Buscar empreendimentos"
                    />
                </div>
            @endif
            <div class="emp-toolbar-actions">
                @if(!$empreendimentos->isEmpty() || request()->filled('busca'))
                    <button type="submit" class="emp-btn-submit">Buscar</button>
                @endif
                <a href="{{ route('empreendimentos.create') }}" class="emp-btn-create">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Novo Empreendimento</span>
                </a>
                @if(request()->filled('busca'))
                    <a href="{{ route('empreendimentos.index') }}" class="emp-btn-cancel">Limpar</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Lista de Empreendimentos --}}
    @if($empreendimentos->isEmpty())
        <div class="emp-table-card emp-empty-state">
            <svg width="40" height="40" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <h2>
                {{ request()->filled('busca') ? 'Nenhum resultado encontrado' : 'Nenhum empreendimento cadastrado' }}
            </h2>
            <p>
                {{ request()->filled('busca') ? 'Tente outro termo ou limpe a busca.' : 'Use “Novo empreendimento” para começar.' }}
            </p>
        </div>
    @else
        <div class="emp-table-card emp-table-list">
            <table class="emp-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>CNPJ</th>
                        <th>Localização / Bairro</th>
                        <th>Fase de Operação</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($empreendimentos as $emp)
                        <tr>
                            <td data-label="Empresa">
                                <strong class="emp-name">{{ $emp->nome }}</strong>
                                @if($emp->tipo_atividade)
                                    <div class="emp-activity">{{ $emp->tipo_atividade }}</div>
                                @endif
                            </td>
                            <td data-label="CNPJ">
                                @if($emp->cnpj)
                                    <span class="emp-badge-cnpj">{{ $emp->cnpj }}</span>
                                @else
                                    <span style="color: #94a3b8; font-size: 0.85rem;">Não informado</span>
                                @endif
                            </td>
                            <td data-label="Localização / Bairro">
                                <div class="emp-location">{{ $emp->endereco ? $emp->endereco . ', ' : '' }}{{ $emp->bairro ?? 'Seabra-BA' }}</div>
                                @if($emp->bacia_hidrografica)
                                    <small class="emp-basin">Bacia: {{ $emp->bacia_hidrografica }}</small>
                                @endif
                            </td>
                            <td data-label="Fase de Operação">
                                <span class="emp-badge-fase">
                                    {{ $emp->fase_operacao ?? 'Não especificada' }}
                                </span>
                            </td>
                            <td data-label="Ações">
                                <div class="emp-actions">
                                    {{-- Botão Nova Licença / Requisição --}}
                                    <a href="{{ route('requerimentos.aluno.novo', ['empreendimento_id' => $emp->id]) }}"
                                       class="emp-btn-req"
                                       title="Abrir novo processo de licenciamento para este empreendimento">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Nova Requisição
                                    </a>

                                    {{-- Botão Editar --}}
                                    <a href="{{ route('empreendimentos.edit', $emp->id) }}"
                                       class="emp-btn-edit"
                                       title="Editar dados cadastrais do empreendimento">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
