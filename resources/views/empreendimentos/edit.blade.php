@extends('layouts.app')

@section('title', 'Editar Empreendimento - SECAMB')
@section('tag', 'Empreendimentos')

@section('content')
@vite('resources/css/empreendimentos/form.css')

<div class="emp-container">

    {{-- Botão Voltar --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('empreendimentos.index') }}" class="emp-btn-cancel" style="display: inline-flex; align-items: center; gap: 6px;">
            ← Voltar para Empreendimentos
        </a>
    </div>

    @if (session('sucesso'))
        <div class="emp-alert emp-alert-success">{{ session('sucesso') }}</div>
    @endif

    {{-- Notificação de Erros --}}
    @if ($errors->any())
        <div class="emp-alert emp-alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Corrija os erros abaixo:</strong>
                <ul style="margin: 6px 0 0 18px; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="emp-form-card">
        <div class="emp-form-header">
            <h2>Editar Empreendimento: {{ $empreendimento->nome }}</h2>
            <p>Atualize os dados cadastrais, de localização e de contato do empreendimento.</p>
        </div>

        <form action="{{ route('empreendimentos.update', $empreendimento->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- 1. Dados do Empreendimento --}}
            <div class="emp-section">
                <h3 class="emp-section-title">
                    <svg width="18" height="18" fill="none" stroke="#0284c7" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    1. Identificação do Empreendimento
                </h3>

                <div class="emp-form-grid">
                    <div class="emp-form-group emp-col-8">
                        <label for="nome">Nome do Empreendimento / Razão Social <span class="required">*</span></label>
                        <input type="text" name="nome" id="nome" class="emp-input" value="{{ old('nome', $empreendimento->nome) }}" required>
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" name="cnpj" id="cnpj" class="emp-input" value="{{ old('cnpj', $empreendimento->cnpj) }}" placeholder="AA.AAA.AAA/AAAA-00" maxlength="18">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="tipo_atividade">Atividade Econômica Principal</label>
                        <input type="text" name="tipo_atividade" id="tipo_atividade" class="emp-input" value="{{ old('tipo_atividade', $empreendimento->tipo_atividade) }}">
                    </div>

                    <div class="emp-form-group emp-col-3">
                        <label for="cnae">Cód. Atividade / CNAE</label>
                        <input type="text" name="cnae" id="cnae" class="emp-input" value="{{ old('cnae', $empreendimento->cnae) }}" placeholder="Ex: B4.1 ou 0810-0/99">
                    </div>

                    <div class="emp-form-group emp-col-3">
                        <label for="porte">Porte do Empreendimento</label>
                        <select name="porte" id="porte" class="emp-select">
                            <option value="">Selecione o porte...</option>
                            <option value="Micro" {{ old('porte', $empreendimento->porte) === 'Micro' ? 'selected' : '' }}>Micro</option>
                            <option value="Pequeno" {{ old('porte', $empreendimento->porte) === 'Pequeno' ? 'selected' : '' }}>Pequeno</option>
                            <option value="Médio" {{ old('porte', $empreendimento->porte) === 'Médio' ? 'selected' : '' }}>Médio</option>
                            <option value="Grande" {{ old('porte', $empreendimento->porte) === 'Grande' ? 'selected' : '' }}>Grande</option>
                        </select>
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="fase_operacao">Fase de Operação</label>
                        <select name="fase_operacao" id="fase_operacao" class="emp-select">
                            <option value="">Selecione uma opção...</option>
                            <option value="Localização" {{ old('fase_operacao', $empreendimento->fase_operacao) === 'Localização' ? 'selected' : '' }}>Localização</option>
                            <option value="Instalação" {{ old('fase_operacao', $empreendimento->fase_operacao) === 'Instalação' ? 'selected' : '' }}>Instalação</option>
                            <option value="Operação" {{ old('fase_operacao', $empreendimento->fase_operacao) === 'Operação' ? 'selected' : '' }}>Operação</option>
                            <option value="Não se Aplica" {{ old('fase_operacao', $empreendimento->fase_operacao) === 'Não se Aplica' ? 'selected' : '' }}>Não se Aplica</option>
                        </select>
                    </div>

                    <div class="emp-form-group emp-col-8">
                        <label for="endereco">Endereço / Logradouro</label>
                        <input type="text" name="endereco" id="endereco" class="emp-input" value="{{ old('endereco', $empreendimento->endereco) }}">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="bairro">Bairro / Povoado</label>
                        <input type="text" name="bairro" id="bairro" class="emp-input" value="{{ old('bairro', $empreendimento->bairro) }}">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="cep">CEP</label>
                        <input type="text" name="cep" id="cep" class="emp-input" value="{{ old('cep', $empreendimento->cep) }}" placeholder="46900-000" maxlength="9" inputmode="numeric">
                        <small id="cepFeedback" aria-live="polite"></small>
                    </div>

                                    </div>
            </div>

            {{-- 2. Dados de Contato do Responsável --}}
            <div class="emp-section">
                <h3 class="emp-section-title">
                    <svg width="18" height="18" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    2. Dados de Contato do Responsável
                </h3>

                <div class="emp-form-grid">
                    <div class="emp-form-group emp-col-6">
                        <label for="contato_nome">Nome do Contato / Representante</label>
                        <input type="text" name="contato_nome" id="contato_nome" class="emp-input" value="{{ old('contato_nome', $empreendimento->contato_nome) }}">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_email">E-mail de Contato</label>
                        <input type="email" name="contato_email" id="contato_email" class="emp-input" value="{{ old('contato_email', $empreendimento->contato_email) }}">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_telefone">Telefone Fixo (opcional)</label>
                        <input type="text" name="contato_telefone" id="contato_telefone" class="emp-input" value="{{ old('contato_telefone', $empreendimento->contato_telefone) }}">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_celular">Celular / WhatsApp (opcional)</label>
                        <input type="text" name="contato_celular" id="contato_celular" class="emp-input" value="{{ old('contato_celular', $empreendimento->contato_celular) }}">
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="emp-form-actions">
                <a href="{{ route('empreendimentos.index') }}" class="emp-btn-cancel">
                    Cancelar
                </a>
                <button type="submit" class="emp-btn-submit">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <div class="emp-form-card" style="margin-top: 24px;">
        <div class="emp-form-header">
            <h2>Técnicos autorizados</h2>
            <p>Vincule técnicos cadastrados como Pessoa Física pelo e-mail. Eles poderão abrir solicitações e acompanhar os processos deste empreendimento.</p>
        </div>

        <form action="{{ route('empreendimentos.tecnicos.adicionar', $empreendimento) }}" method="POST" class="emp-form-grid">
            @csrf
            <div class="emp-form-group emp-col-8">
                <label for="tecnico_email">E-mail do técnico</label>
                <input type="email" name="email" id="tecnico_email" class="emp-input" value="{{ old('email') }}" required>
            </div>
            <div class="emp-form-group emp-col-4" style="align-self: end;">
                <button type="submit" class="emp-btn-submit">Vincular técnico</button>
            </div>
        </form>

        @if($tecnicos->isEmpty())
            <p>Nenhum técnico vinculado a este empreendimento.</p>
        @else
            <ul style="list-style: none; padding: 0;">
                @foreach($tecnicos as $tecnico)
                    <li style="display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 0; border-top: 1px solid #e2e8f0;">
                        <span><strong>{{ $tecnico->nome }}</strong> — {{ $tecnico->email }}</span>
                        <form action="{{ route('empreendimentos.tecnicos.remover', [$empreendimento, $tecnico->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="emp-btn-cancel">Remover</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cnpjInput = document.getElementById('cnpj');
        if (cnpjInput) {
            cnpjInput.addEventListener('input', function (e) {
                let v = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 14);
                v = v.slice(0, 12) + v.slice(12).replace(/\D/g, '').slice(0, 2);
                e.target.value = v.slice(0, 2)
                    + (v.length > 2 ? '.' + v.slice(2, 5) : '')
                    + (v.length > 5 ? '.' + v.slice(5, 8) : '')
                    + (v.length > 8 ? '/' + v.slice(8, 12) : '')
                    + (v.length > 12 ? '-' + v.slice(12, 14) : '');
            });
        }

        const cepInput = document.getElementById('cep');
        if (cepInput) {
            cepInput.addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 8) v = v.substring(0, 8);
                v = v.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = v;
            });
        }
    });
</script>
<script src="{{ asset('js/endereco.js') }}"></script>
@endsection
