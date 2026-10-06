@extends('layouts.app')

@section('title', 'Consulta de Requerimentos - SECAMB')
@section('tag', 'Administração')

@section('content')
<link rel="stylesheet" href="{{ asset('css/consultaRequerimento.css') }}?v={{ filemtime(public_path('css/consultaRequerimento.css')) }}">

{{-- Filtro Avançado de Pesquisa --}}
<div class="dash-filter-card">
    <form method="GET" action="" class="dash-filter-form">
        <div class="filter-grid">

            <div class="filter-group">
                <label class="filter-label">Solicitante</label>
                <input type="text" name="aluno" value="{{ request('aluno') }}" placeholder="Nome do solicitante..." class="input-filtro">
            </div>

            <div class="filter-group">
                <label class="filter-label">Tipo de Pessoa</label>
                <select name="turma" class="input-filtro">
                    <option value="">Todos</option>
                    <option value="fisica" {{ request('turma') == 'fisica' ? 'selected' : '' }}>Pessoa Física</option>
                    <option value="juridica" {{ request('turma') == 'juridica' ? 'selected' : '' }}>Pessoa Jurídica</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Setor</label>
                <select name="setor" class="input-filtro">
                    <option value="">Todos</option>
                    @foreach($setores as $s)
                        <option value="{{ $s->id }}" {{ request('setor') == $s->id ? 'selected' : '' }}>
                            {{ $s->setor_sigla }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="input-filtro">
                    <option value="">Todos</option>
                    <option value="Aberto" {{ request('status') == 'Aberto' ? 'selected' : '' }}>Aberto</option>
                    <option value="Em Análise" {{ request('status') == 'Em Análise' ? 'selected' : '' }}>Em Análise</option>
                    <option value="Indeferido" {{ request('status') == 'Indeferido' ? 'selected' : '' }}>Indeferido</option>
                    <option value="Concluído" {{ request('status') == 'Concluído' ? 'selected' : '' }}>Concluído</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Data Inicial</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="input-filtro">
            </div>

            <div class="filter-group">
                <label class="filter-label">Data Final</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="input-filtro">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filtrar">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                @if(request()->anyFilled(['aluno', 'turma', 'setor', 'status', 'data_inicio', 'data_fim']))
                    <a href="{{ route('admin.consultar-requerimentos') }}" class="btn-limpar">
                       <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>

    </form>
</div>

<section class="dash-section">
    <h3 class="dash-section-title">Requerimentos recentes</h3>

    @if($requerimentos->isEmpty())
        <p style="color: #64748b; font-size: 0.875rem; margin: 0; padding: 12px 0;">
            @if(request()->filled('aluno') || request()->filled('setor'))
                Nenhum requerimento encontrado para esta busca.
            @else
                Nenhum requerimento cadastrado.
            @endif
        </p>
    @else
        <div style="overflow-x: auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th style="width: 140px;">Data e Hora</th>
                        <th>Solicitante</th>
                        <th>Tipo de Pessoa</th>
                        <th>Requerimento</th>
                        <th style="width: 120px; text-align: center;">Setor</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requerimentos as $requerimento)
                        <tr>
                            <td>{{ $requerimento->id ?? '—' }}</td>
                            <td style="color: #64748b; font-size: 0.8125rem;">
                                {{ $requerimento->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td>{{ $requerimento->usuario?->nome ?? 'Usuário removido' }}</td>
                            <td>{{ $requerimento->usuario?->tipo_pessoa_formatado ?? '—' }}</td>
                            <td>{{ $requerimento->objetoDoRequerimento }}</td>
                            <td style="text-align: center;">
                                <span class="badge badge-setor" title="{{ $requerimento->setor_nome }}">
                                    {{ $requerimento->setor?->setor_sigla ?? $requerimento->setor_sigla }}
                                </span>
                            </td>
                            <td class="req-col-objeto {{ $requerimento->status ?? 'Aberto' }}" style="text-align: center;">
                                <span>
                                    {{ $requerimento->status ?? '-'}}
                                </span>
                            </td>
                            <td style="text-align: center; width: 120px;">
                                <span class="badge badge-setor border">
                                    <a href="{{ route('admin.historico', $requerimento->id) }}">Visualizar</a>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
