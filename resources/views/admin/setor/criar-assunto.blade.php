@extends('layouts.app')

@section('title', 'Novo Requerimento - ' . $setor->setor_sigla)
@section('tag', 'Administração')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-setor.css') }}">

<div class="card-criar-requerimento">
    {{-- Cabeçalho --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #e2e8f0;">
        <div>
            <span style="font-size: 0.8rem; font-weight: 700; color: #2563eb; text-transform: uppercase;">
                {{ $setor->setor_sigla }} &mdash; {{ $setor->setor_nome }}
            </span>
            <h2 style="font-size: 1.35rem; font-weight: 700; margin: 2px 0 0; color: #111827;">
                Novo Requerimento
            </h2>
        </div>
        <a href="{{ route('admin.setores.edit', $setor->id) }}" class="btn-voltar">
            &larr; Voltar
        </a>
    </div>

    {{-- Feedback de erros --}}
    @if($errors->any())
        <div style="background: #fce8e6; color: #c5221f; padding: 10px 14px; margin-bottom: 18px; border: 1px solid #fad2cf; border-radius: 6px; font-size: 0.875rem;">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulário --}}
    <form action="{{ route('admin.setores.assuntos.store', $setor->id) }}" method="POST">
        @csrf

        {{-- Campos Principais --}}
        <div style="display: grid; grid-template-columns: 1fr; gap: 14px; margin-bottom: 22px;">
            <div>
                <label class="label-campo">Descrição *</label>
                <input type="text"
                       name="descricao"
                       value="{{ old('descricao') }}"
                       placeholder="Ex: Declaração de Horário Individual"
                       required
                       class="input-tabela">
            </div>

            <div>
                <label class="label-campo">Link da norma (opcional)</label>
                <input type="url"
                       name="link_norma"
                       value="{{ old('link_norma') }}"
                       placeholder="https://..."
                       maxlength="2048"
                       class="input-tabela">
            </div>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div style="flex: 4; min-width: 280px;">
                    <label class="label-campo">Observação / Requisito</label>
                    <input type="text"
                           name="observacao"
                           value="{{ old('observacao') }}"
                           placeholder="Ex: Necessita assinatura da coordenação"
                           class="input-tabela">
                </div>
            </div>
        </div>

        {{-- Anexos --}}
        <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; margin-bottom: 22px; background: #fafafa;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <label class="label-campo" style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">
                    Anexos / Documentos Exigidos
                </label>
                <button type="button"
                        class="btn-destaque-anexo"
                        onclick="adicionarAnexoRow()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Adicionar Anexo
                </button>
            </div>

            <div id="container-tabela-anexos" style="display: none; overflow-x: auto;">
                <table class="tabela-docs-nested" style="min-width: 820px; margin-bottom: 0; background: #fff;">
                    <thead>
                        <tr>
                            <th style="min-width: 190px;">Documento *</th>
                            <th style="min-width: 220px;">Orientações</th>
                            <th style="min-width: 220px;">Link do modelo</th>
                            <th style="width: 80px; text-align: center;">Obrigatório</th>
                            <th style="width: 50px; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="lista-anexos-tbody"></tbody>
                </table>
            </div>
        </div>

        {{-- Botões --}}
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
            <a href="{{ route('admin.setores.edit', $setor->id) }}" class="btn-voltar">
                Cancelar
            </a>
            <button type="submit" class="btn-salvar" style="padding: 8px 20px;">
                Salvar
            </button>
        </div>
    </form>
</div>

<script>
let contadorAnexo = 0;

function adicionarAnexoRow() {
    const tbody = document.getElementById('lista-anexos-tbody');
    const containerTabela = document.getElementById('container-tabela-anexos');
    const msgVazio = document.getElementById('msg-sem-anexos');

    const index = contadorAnexo++;

    const tr = document.createElement('tr');
    tr.id = `anexo-row-${index}`;
    tr.innerHTML = `
        <td>
            <input type="text"
                   name="documentos[${index}][nome]"
                   placeholder="Ex: Histórico Escolar"
                   required
                   class="input-tabela"
                   style="font-size: 0.8125rem;">
        </td>
        <td>
            <input type="text"
                   name="documentos[${index}][descricao]"
                   placeholder="Instruções para o cidadão (opcional)"
                   class="input-tabela"
                   style="font-size: 0.8125rem;">
        </td>
        <td>
            <input type="url"
                   name="documentos[${index}][link_modelo]"
                   placeholder="https://..."
                   maxlength="2048"
                   class="input-tabela"
                   style="font-size: 0.8125rem;">
        </td>
        <td style="text-align: center;">
            <input type="checkbox"
                   name="documentos[${index}][obrigatorio]"
                   value="1"
                   checked
                   style="width: 15px; height: 15px; accent-color: #2563eb; cursor: pointer;">
        </td>
        <td style="text-align: center;">
            <button type="button"
                    class="btn-delete-doc-sm"
                    title="Remover"
                    onclick="removerAnexoRow(${index})">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);

    containerTabela.style.display = 'block';
    msgVazio.style.display = 'none';

    const inputNome = tr.querySelector('input[type="text"]');
    if (inputNome) {
        setTimeout(() => inputNome.focus(), 50);
    }
}

function removerAnexoRow(index) {
    const row = document.getElementById(`anexo-row-${index}`);
    if (row) {
        row.remove();
    }

    const tbody = document.getElementById('lista-anexos-tbody');
    const containerTabela = document.getElementById('container-tabela-anexos');
    const msgVazio = document.getElementById('msg-sem-anexos');

    if (!tbody.querySelector('tr')) {
        containerTabela.style.display = 'none';
        msgVazio.style.display = 'block';
    }
}
</script>
@endsection
