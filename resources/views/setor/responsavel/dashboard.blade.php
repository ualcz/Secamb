@extends('layouts.app')

@section('title', $setor->setor_sigla.' - SECAMB')
@section('tag', 'Administração')

@section('content')
<link rel="stylesheet" href="{{ asset('css/consultaRequerimento.css') }}?v={{ filemtime(public_path('css/consultaRequerimento.css')) }}">

<x-btn-voltar />

<div class="dash-filter-card">
    <form method="GET" action="{{ route('setor.responsavel.dashboard', $setor->id) }}" class="dash-filter-form">
        <div class="filter-grid" style="display:grid; grid-template-columns: 1.5fr 1.2fr 1.2fr 1.2fr 1fr 1fr auto; gap:1rem; align-items:end;">
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
                <label class="filter-label">Status</label>
                <select name="status" class="input-filtro">
                    <option value="">Todos</option>
                    <option value="Aberto" {{ request('status') == 'Aberto' ? 'selected' : '' }}>Aberto</option>
                    <option value="Em Análise" {{ request('status') == 'Em Análise' ? 'selected' : '' }}>Em Análise</option>
                    <option value="Despacho" {{ request('status') == 'Despacho' ? 'selected' : '' }}>Despacho</option>
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

                @if(request()->anyFilled(['aluno', 'turma', 'status', 'data_inicio', 'data_fim']))
                    <a href="{{ route('setor.responsavel.dashboard', $setor->id) }}" class="btn-limpar">Limpar</a>
                @endif
            </div>
        </div>
    </form>
</div>

<section class="dash-section">
    <h3 class="dash-section-title">Requerimentos do setor</h3>

    @if($requerimentos->isEmpty())
        <p style="color: #64748b; font-size: 0.875rem; margin: 0; padding: 12px 0;">
            Nenhum requerimento encontrado para este setor.
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
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requerimentos as $requerimento)
                        <tr>
                            <td>{{ $requerimento->id }}</td>
                            <td style="color: #64748b; font-size: 0.8125rem;">
                                {{ $requerimento->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td>{{ $requerimento->usuario?->nome ?? 'Usuário removido' }}</td>
                            <td>{{ $requerimento->usuario?->tipo_pessoa_formatado ?? '—' }}</td>
                            <td>{{ $requerimento->objetoDoRequerimento }}</td>
                            <td class="{{ $requerimento->status ?? 'Aberto' }}" style="text-align: center;">
                                <span>{{ $requerimento->status ?? 'Aberto' }}</span>
                            </td>
                            <td style="text-align: center; width: 120px;">
                                <span class="badge badge-setor border" style="background:#dbeafe; border-color:#60a5fa;">
                                    <a href="{{ route('setor.requerimentos.show', ['setor' => $setor->id, 'requerimento' => $requerimento->id]) }}" style="color:#1d4ed8; text-decoration:none; font-weight:600;">Atender</a>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1rem;">
            {{ $requerimentos->links() }}
        </div>
    @endif
</section>
@endsection
