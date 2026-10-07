@extends('layouts.app')

@section('title', 'Modelos de Requerimentos - SDP')
@section('tag', 'Administração')

@section('content')
<style>
    .admin-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 24px;
        margin-bottom: 24px;
    }
    .tabela-modelos {
        border-collapse: collapse;
        width: 100%;
        margin-top: 15px;
    }
    .tabela-modelos th, .tabela-modelos td {
        border: 1px solid #e5e7eb;
        padding: 10px 14px;
        text-align: left;
    }
    .tabela-modelos th {
        background: #f9fafb;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }
    .tabela-modelos tr:hover {
        background-color: #f9fafb;
    }
    .badge-ativo {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .badge-inativo {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .badge-interno {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #d8b4fe;
    }
    .badge-publico {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .btn-acao {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        background-color: #2563eb;
        color: #fff;
        transition: background-color 0.2s;
    }
    .btn-acao:hover {
        background-color: #1d4ed8;
    }
    .btn-voltar {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.875rem;
        border: 1px solid #d1d5db;
        color: #374151;
        text-decoration: none;
        background: #fff;
    }
    .btn-voltar:hover {
        background: #f3f4f6;
    }
</style>

<div class="admin-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: #111827;">Setores</h2>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.setores.create') }}" class="btn-acao">
                    ＋ Novo Setor
                </a>
            @endif
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('servidor.dashboard') }}" class="btn-voltar">
                &larr; Voltar ao Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #e6f4ea; color: #137333; padding: 12px 16px; margin: 16px 0; border: 1px solid #ceead6; border-radius: 6px;">
            {{ session('success') }}
        </div>
    @endif

    <table class="tabela-modelos">
        <thead>
            <tr>
                <th style="width: 100px;">Sigla</th>
                <th>Setor</th>
                <th>Título Formulário</th>
                <th>E-mail Setor</th>
                <th style="width: 110px; text-align: center;">Tipo</th>
                <th style="width: 150px; text-align: center;">Requerimentos</th>
                <th style="width: 100px; text-align: center;">Status</th>
                <th style="width: 140px; text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($modelos as $modelo)
                <tr>
                    <td><strong>{{ $modelo->setor_sigla }}</strong></td>
                    <td>{{ $modelo->setor_nome }}</td>
                    <td>{{ $modelo->titulo }}</td>
                    <td>{{ $modelo->email ?: '—' }}</td>
                    <td style="text-align: center;">
                        @if($modelo->is_interno)
                            <span class="badge-interno" title="Setor interno para despachos e tramitação (não visível ao cidadão)">Interno</span>
                        @else
                            <span class="badge-publico" title="Visível para o cidadão no portal">Público</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span style="font-weight: 600; color: #2563eb;">{{ $modelo->assuntos_ativos_count }}</span>
                        <span style="color: #9ca3af;">/</span>
                        <span>{{ $modelo->assuntos_count }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if($modelo->ativo)
                            <span class="badge-ativo">Ativo</span>
                        @else
                            <span class="badge-inativo">Inativo</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.setores.edit', $modelo->id) }}" class="btn-acao">
                            Editar
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 24px; color: #6b7280;">Nenhum setor cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
