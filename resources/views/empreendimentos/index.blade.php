@extends('layouts.app')

@section('title', 'Empreendimentos - SECAMB')
@section('tag', 'Empreendimentos')

@section('content')
<link rel="stylesheet" href="{{ asset('css/empreendimentos.css') }}?v={{ filemtime(public_path('css/empreendimentos.css')) }}">

<div class="emp-container">

    {{-- Cabeçalho & Barra de Ações --}}
    <div class="emp-header">
        <div class="emp-title-area">
            <h1>Empreendimentos</h1>
            <p>Gerencie seus empreendimentos cadastrados e representações legais no município de Seabra</p>
        </div>

        {{-- <div class="emp-nav-buttons"> --}}
            <a href="{{ route('empreendimentos.create') }}" class="emp-nav-btn emp-nav-btn-new">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Novo Empreendimento</span>
            </a>
        {{-- </div> --}}
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

    {{-- Filtro de Pesquisa Rápida --}}
    @if(!$empreendimentos->isEmpty() || request()->filled('busca'))
        <div  style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
            <form method="GET" action="{{ route('empreendimentos.index') }}" class="busca" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input
                    type="text"
                    name="busca"
                    value="{{ request('busca') }}"
                    placeholder="Filtrar por nome do empreendimento, CNPJ ou bairro..."
                    class="emp-input"
                    style="flex: 1; min-width: 250px;"
                />
                <button type="submit" class="emp-btn-submit" style="padding: 10px 20px;">
                    Filtrar
                </button>
                @if(request()->filled('busca'))
                    <a href="{{ route('empreendimentos.index') }}" class="emp-btn-cancel">
                        Limpar
                    </a>
                @endif
            </form>
        </div>
    @endif

    {{-- Lista de Empreendimentos --}}
    <section class="desktop-empreendimentos">
        @if($empreendimentos->isEmpty())
            <div class="emp-table-card" style="padding: 40px 20px; text-align: center;">
                <svg width="48" height="48" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 12px auto; display: block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 style="font-size: 1.15rem; color: #1e293b; margin: 0 0 6px 0;">
                    {{ request()->filled('busca') ? 'Nenhum empreendimento localizado para esta busca' : 'Nenhum empreendimento vinculado à sua conta' }}
                </h3>
                <p style="color: #64748b; font-size: 0.9rem; max-width: 500px; margin: 0 auto 20px auto;">
                    {{ request()->filled('busca') ? 'Tente buscar por outro termo ou limpe o filtro.' : 'Cadastre um novo empreendimento ou busque por CNPJ para solicitar sua representação legal e abrir processos de licenciamento.' }}
                </p>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('empreendimentos.create') }}" class="emp-btn-req" style="padding: 9px 20px; font-size: 0.9rem;">
                        + Cadastrar Empreendimento
                    </a>
                    <a href="
                    {{-- {{ route('empreendimentos.buscar') }} --}}
                    " class="emp-btn-edit" style="padding: 9px 20px; font-size: 0.9rem;">
                        Buscar por CNPJ
                    </a>
                </div>
            </div>
        @else
            <div class="emp-table-card">
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
                                <td>
                                    <strong style="color: #0f172a; font-size: 0.95rem;">{{ $emp->nome }}</strong>
                                    @if($emp->tipo_atividade)
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">{{ $emp->tipo_atividade }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($emp->cnpj)
                                        <span class="emp-badge-cnpj">{{ $emp->cnpj }}</span>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.85rem;">Não informado</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $emp->endereco ? $emp->endereco . ', ' : '' }}{{ $emp->bairro ?? 'Seabra-BA' }}</div>
                                    @if($emp->bacia_hidrografica)
                                        <small style="color: #64748b;">Bacia: {{ $emp->bacia_hidrografica }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="emp-badge-fase">
                                        {{ $emp->fase_operacao ?? 'Não especificada' }}
                                    </span>
                                </td>
                                <td>
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
    </section>
    <section class="mobile-empreendimentos">
        @if($empreendimentos->isEmpty())
            <div class="emp-table-card" style="padding: 40px 20px; text-align: center;">
                <svg width="48" height="48" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 12px auto; display: block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 style="font-size: 1.15rem; color: #1e293b; margin: 0 0 6px 0;">
                    {{ request()->filled('busca') ? 'Nenhum empreendimento localizado para esta busca' : 'Nenhum empreendimento vinculado à sua conta' }}
                </h3>
                <p style="color: #64748b; font-size: 0.9rem; max-width: 500px; margin: 0 auto 20px auto;">
                    {{ request()->filled('busca') ? 'Tente buscar por outro termo ou limpe o filtro.' : 'Cadastre um novo empreendimento ou busque por CNPJ para solicitar sua representação legal e abrir processos de licenciamento.' }}
                </p>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('empreendimentos.create') }}" class="emp-btn-req" style="padding: 9px 20px; font-size: 0.9rem;">
                        + Cadastrar Empreendimento
                    </a>
                    <a href="
                    {{-- {{ route('empreendimentos.buscar') }} --}}
                    " class="emp-btn-edit" style="padding: 9px 20px; font-size: 0.9rem;">
                        Buscar por CNPJ
                    </a>
                </div>
            </div>
        @else
                        @foreach($empreendimentos as $emp)
                            <div class="empreendimento-card">

                                <div class="grid grid-cols-2 mb-4">
                                    <div class="mb-4">
                                        <strong style="color: #0f172a; font-size: 1.35rem;">{{ $emp->nome }}</strong>
                                        @if($emp->tipo_atividade)
                                            <div style="font-size: 0.75rem; color: #64748b;">{{ $emp->tipo_atividade }}</div>
                                        @endif
                                    </div>
                                    @if($emp->cnpj)
                                        <span class="text-sm p-2 bg-blue-100 h-9 text-center rounded-2xl text-blue-950">{{ $emp->cnpj }}</span>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.85rem;">Não informado</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2">
                                    <div>{{ $emp->endereco ? $emp->endereco . ', ' : '' }}{{ $emp->bairro ?? 'Seabra-BA' }}</div>
                                    @if($emp->bacia_hidrografica)
                                        <small style="color: #64748b;">Bacia: {{ $emp->bacia_hidrografica }}</small>
                                    @endif
                                </div>

                                <span class="text-sm text-center rounded-2xl text-gray-600">
                                   Operação: {{ $emp->fase_operacao ?? 'Não especificada' }}
                                </span>
                                <div class="grid grid-cols-2 gap-2 mt-8">
                                    {{-- Botão Editar --}}
                                    <a href="{{ route('empreendimentos.edit', $emp->id) }}"
                                    class="emp-btn-edit"
                                    title="Editar dados cadastrais do empreendimento">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>

                                    {{-- Botão Nova Licença / Requisição --}}
                                    <a href="{{ route('requerimentos.aluno.novo', ['empreendimento_id' => $emp->id]) }}"
                                    class="emp-btn-req"
                                    title="Abrir novo processo de licenciamento para este empreendimento">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Nova Requisição
                                    </a>
                                </div>
                            </div>
                        @endforeach

        @endif
    </section>


</div>
@endsection
