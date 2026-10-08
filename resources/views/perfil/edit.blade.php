@extends('layouts.app')

@section('title', $usuario->isAdmin() ? 'Alterar Minha Senha - SECAMB' : 'Editar Meus Dados - SECAMB Seabra')
@section('tag', 'Perfil')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ filemtime(public_path('css/profile.css')) }}">

<div class="edit-perfil-wrapper">

    {{-- Botão Voltar --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('perfil.index') }}" class="btn-cancelar" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 0.88rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Voltar ao Perfil</span>
        </a>
    </div>

    {{-- Feedback de Mensagens --}}
    @if (session('success') || session('sucesso'))
        <div class="alert-sucesso">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Sucesso!</strong> {{ session('success') ?? session('sucesso') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-erro">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Revise os campos com erro abaixo:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($usuario->isAdmin())
        {{-- VISUALIZAÇÃO PARA ADMINISTRADOR: Apenas dados institucionais fixos + Alteração de Senha --}}
        <div class="edit-perfil-card">
            <div class="edit-perfil-card-header">
                <h2>Dados do Administrador</h2>
                <p>Informações institucionais da sua conta administrativa.</p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 8px;">
                <div class="form-grid-2">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Nome</span>
                        <strong style="font-size: 1rem; color: #0f172a;">{{ $usuario->nome }}</strong>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">E-mail Institucional</span>
                        <strong style="font-size: 1rem; color: #0f172a;">{{ $usuario->email }}</strong>
                    </div>
                </div>

                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 0.85rem; color: #64748b; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Dados cadastrais de administradores são gerenciados institucionalmente e não podem ser alterados pelo painel.</span>
                </div>
            </div>
        </div>

    @else
        {{-- FORMULÁRIO DE DADOS CADASTRAIS (CIDADÃO E SERVIDOR) --}}
        <div class="edit-perfil-card">
            <div class="edit-perfil-card-header">
                <h2>Editar Dados Cadastrais</h2>
                <p>Mantenha seus dados atualizados para correta identificação em processos, certidões e notificações da SECAMB.</p>
            </div>

            <form action="{{ route('perfil.update') }}" method="POST" id="formEditarPerfil">
                @csrf
                @method('PUT')

                {{-- 1. IDENTIFICAÇÃO --}}
                <h3 class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    1. Identificação
                </h3>

                @if($usuario->isCidadao())
                    {{-- Alternador de Pessoa Física / Jurídica para Cidadão --}}
                    <div class="tipo-selector-grid">
                        <label class="tipo-selector-card {{ old('tipo_registro', $usuario->tipo_registro ?? 'fisica') === 'fisica' ? 'active' : '' }}" id="cardTipoFisica">
                            <input type="radio" name="tipo_registro" value="fisica" id="radioFisica"
                                {{ old('tipo_registro', $usuario->tipo_registro ?? 'fisica') === 'fisica' ? 'checked' : '' }}
                                onchange="alternarTipoRegistro()">
                            <div>
                                <p class="tipo-title">Pessoa Física</p>
                                <p class="tipo-sub">Cidadão com CPF próprio</p>
                            </div>
                        </label>

                        <label class="tipo-selector-card {{ old('tipo_registro', $usuario->tipo_registro ?? 'fisica') === 'juridica' ? 'active' : '' }}" id="cardTipoJuridica">
                            <input type="radio" name="tipo_registro" value="juridica" id="radioJuridica"
                                {{ old('tipo_registro', $usuario->tipo_registro ?? 'fisica') === 'juridica' ? 'checked' : '' }}
                                onchange="alternarTipoRegistro()">
                            <div>
                                <p class="tipo-title">Pessoa Jurídica</p>
                                <p class="tipo-sub">Empresa ou organização com CNPJ</p>
                            </div>
                        </label>
                    </div>

                    {{-- Campos de Pessoa Física --}}
                    <div id="secaoPf">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="nome" class="form-label">
                                    Nome Completo <span class="required">*</span>
                                </label>
                                <div class="form-input-wrap">
                                    <span class="form-input-icon">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </span>
                                    <input type="text" name="nome" id="nome"
                                        class="form-control has-icon {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                                        value="{{ old('nome', $usuario->nome) }}"
                                        placeholder="Ex: João da Silva">
                                </div>
                                @error('nome')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="cpf" class="form-label">
                                    CPF <span class="required">*</span>
                                </label>
                                <div class="form-input-wrap">
                                    <span class="form-input-icon">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                    </span>
                                    <input type="text" name="cpf" id="cpf"
                                        class="form-control has-icon {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                                        value="{{ old('cpf', $usuario->cpf) }}"
                                        placeholder="000.000.000-00" maxlength="14"
                                        oninput="mascaraCPF(this)">
                                </div>
                                @error('cpf')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Campos de Pessoa Jurídica --}}
                    <div id="secaoPj" style="display: none;">
                        <div class="form-group">
                            <label for="razao_social" class="form-label">
                                Razão Social <span class="required">*</span>
                            </label>
                            <div class="form-input-wrap">
                                <span class="form-input-icon">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </span>
                                <input type="text" name="razao_social" id="razao_social"
                                    class="form-control has-icon {{ $errors->has('razao_social') ? 'is-invalid' : '' }}"
                                    value="{{ old('razao_social', $usuario->razao_social) }}"
                                    placeholder="Ex: Empresa de Mineração e Serviços LTDA">
                            </div>
                            @error('razao_social')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="nome_fantasia" class="form-label">Nome Fantasia (Opcional)</label>
                                <div class="form-input-wrap">
                                    <span class="form-input-icon">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                    </span>
                                    <input type="text" name="nome" id="nome_fantasia"
                                        class="form-control has-icon"
                                        value="{{ old('nome', $usuario->isPessoaJuridica() ? $usuario->nome : '') }}"
                                        placeholder="Ex: Mineração Seabra">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="cnpj" class="form-label">
                                    CNPJ <span class="required">*</span>
                                </label>
                                <div class="form-input-wrap">
                                    <span class="form-input-icon">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </span>
                                    <input type="text" name="cnpj" id="cnpj"
                                        class="form-control has-icon {{ $errors->has('cnpj') ? 'is-invalid' : '' }}"
                                        value="{{ old('cnpj', $usuario->cnpj) }}"
                                        placeholder="00.000.000/0000-00" maxlength="18"
                                        oninput="mascaraCNPJ(this)">
                                </div>
                                @error('cnpj')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                @else
                    {{-- Servidor --}}
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="nome" class="form-label">
                                Nome Completo <span class="required">*</span>
                            </label>
                            <div class="form-input-wrap">
                                <span class="form-input-icon">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input type="text" name="nome" id="nome"
                                    class="form-control has-icon {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                                    value="{{ old('nome', $usuario->nome) }}"
                                    placeholder="Nome completo" required>
                            </div>
                            @error('nome')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="cpf" class="form-label">CPF (Opcional)</label>
                            <div class="form-input-wrap">
                                <span class="form-input-icon">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                </span>
                                <input type="text" name="cpf" id="cpf"
                                    class="form-control has-icon {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                                    value="{{ old('cpf', $usuario->cpf) }}"
                                    placeholder="000.000.000-00" maxlength="14"
                                    oninput="mascaraCPF(this)">
                            </div>
                            @error('cpf')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- 2. CONTATO --}}
                <h3 class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    2. Contato
                </h3>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            E-mail de Acesso <span class="required">*</span>
                        </label>
                        <div class="form-input-wrap">
                            <span class="form-input-icon">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <input type="email" name="email" id="email"
                                class="form-control has-icon {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                value="{{ old('email', $usuario->email) }}"
                                placeholder="seuemail@exemplo.com" required>
                        </div>
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <span class="form-help">Utilizado para login e recebimento de notificações oficiais do processo.</span>
                    </div>

                    <div class="form-group">
                        <label for="celular" class="form-label">
                            Telefone / Celular (WhatsApp)
                            @if($usuario->isCidadao()) <span class="required">*</span> @endif
                        </label>
                        <div class="form-input-wrap">
                            <span class="form-input-icon">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                            <input type="text" name="celular" id="celular"
                                class="form-control has-icon {{ $errors->has('celular') ? 'is-invalid' : '' }}"
                                value="{{ old('celular', $usuario->celular) }}"
                                placeholder="(00) 00000-0000" maxlength="15"
                                oninput="mascaraCelular(this)"
                                {{ $usuario->isCidadao() ? 'required' : '' }}>
                        </div>
                        @error('celular')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <span class="form-help">Para contato rápido e esclarecimento de pendências documentais.</span>
                    </div>
                </div>

                {{-- 3. ENDEREÇO --}}
                <h3 class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    3. Endereço Residencial ou Sede
                </h3>

                {{-- CEP com busca automática via ViaCEP --}}
                <div class="form-grid-cep">
                    <div class="form-group">
                        <label for="cep" class="form-label">
                            CEP
                            @if($usuario->isCidadao()) <span class="required">*</span> @endif
                        </label>
                        <div class="form-input-wrap">
                            <span class="form-input-icon">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </span>
                            <input type="text" name="cep" id="cep"
                                class="form-control has-icon {{ $errors->has('cep') ? 'is-invalid' : '' }}"
                                value="{{ old('cep', $usuario->endereco?->cep) }}"
                                placeholder="00000-000" maxlength="9"
                                oninput="mascaraCEP(this)"
                                onblur="buscarCEP(this.value)">
                        </div>
                        <div id="cepFeedback" class="cep-status"></div>
                        @error('cep')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="rua" class="form-label">
                            Logradouro / Rua
                            @if($usuario->isCidadao()) <span class="required">*</span> @endif
                        </label>
                        <div class="form-input-wrap">
                            <input type="text" name="rua" id="rua"
                                class="form-control {{ $errors->has('rua') ? 'is-invalid' : '' }}"
                                value="{{ old('rua', $usuario->endereco?->rua) }}"
                                placeholder="Ex: Rua Horácio de Matos"
                                {{ $usuario->isCidadao() ? 'required' : '' }}>
                        </div>
                        @error('rua')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="bairro" class="form-label">
                            Bairro
                            @if($usuario->isCidadao()) <span class="required">*</span> @endif
                        </label>
                        <input type="text" name="bairro" id="bairro"
                            class="form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
                            value="{{ old('bairro', $usuario->endereco?->bairro) }}"
                            placeholder="Ex: Centro"
                            {{ $usuario->isCidadao() ? 'required' : '' }}>
                        @error('bairro')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="numero" class="form-label">Número</label>
                        <input type="text" name="numero" id="numero"
                            class="form-control {{ $errors->has('numero') ? 'is-invalid' : '' }}"
                            value="{{ old('numero', $usuario->endereco?->numero) }}"
                            placeholder="Ex: 120 ou S/N">
                        @error('numero')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="complemento" class="form-label">Complemento</label>
                        <input type="text" name="complemento" id="complemento"
                            class="form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
                            value="{{ old('complemento', $usuario->endereco?->complemento) }}"
                            placeholder="Ex: Apto 102, Bloco B">
                        @error('complemento')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="cidade" class="form-label">Cidade</label>
                        <input type="text" name="cidade" id="cidade"
                            class="form-control {{ $errors->has('cidade') ? 'is-invalid' : '' }}"
                            value="{{ old('cidade', $usuario->endereco?->cidade ?? 'Seabra') }}"
                            placeholder="Seabra">
                        @error('cidade')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="estado" class="form-label">Estado (UF)</label>
                        <input type="text" name="estado" id="estado"
                            class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}"
                            value="{{ old('estado', $usuario->endereco?->estado ?? 'BA') }}"
                            placeholder="BA" maxlength="2" style="text-transform: uppercase;">
                        @error('estado')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('perfil.index') }}" class="btn-cancelar">Cancelar</a>
                    <button type="submit" class="btn-salvar-perfil">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Salvar Alterações</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Card: Alteração de Senha (Disponível para todos os perfis) --}}
    <div class="edit-perfil-card" id="secao-senha">
        <div class="edit-perfil-card-header">
            <h2>Segurança &bull; Alterar Senha de Acesso</h2>
            <p>
                @if($usuario->hasLoginSocial() && empty($usuario->password))
                    Sua conta foi criada via Google OAuth. Você pode cadastrar uma senha para também poder fazer login com seu e-mail e senha diretamente.
                @else
                    Altere sua senha de acesso ao sistema SECAMB. Escolha uma senha segura com pelo menos 6 caracteres.
                @endif
            </p>
        </div>

        <form action="{{ route('perfil.senha.update') }}" method="POST" id="formAlterarSenha">
            @csrf
            @method('PUT')

            {{-- Senha Atual (exigida apenas se o usuário já possuir uma senha definida) --}}
            @if(!empty($usuario->password))
                <div class="form-group">
                    <label for="senha_atual" class="form-label">
                        Senha Atual <span class="required">*</span>
                    </label>
                    <div class="form-input-wrap">
                        <span class="form-input-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" name="senha_atual" id="senha_atual"
                            class="form-control has-icon {{ $errors->has('senha_atual') ? 'is-invalid' : '' }}"
                            placeholder="Digite sua senha atual" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibilidadeSenha('senha_atual', this)" aria-label="Mostrar ou ocultar senha">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('senha_atual')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="password" class="form-label">
                        Nova Senha <span class="required">*</span>
                    </label>
                    <div class="form-input-wrap">
                        <span class="form-input-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" name="password" id="password"
                            class="form-control has-icon {{ $errors->has('password') ? 'is-invalid' : '' }}"
                            placeholder="Mínimo de 6 caracteres" minlength="6" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibilidadeSenha('password', this)" aria-label="Mostrar ou ocultar senha">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        Confirmar Nova Senha <span class="required">*</span>
                    </label>
                    <div class="form-input-wrap">
                        <span class="form-input-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control has-icon"
                            placeholder="Digite a nova senha novamente" minlength="6" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibilidadeSenha('password_confirmation', this)" aria-label="Mostrar ou ocultar senha">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-salvar-perfil btn-salvar-senha">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Atualizar Senha</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    // Alternância Pessoa Física vs Pessoa Jurídica
    function alternarTipoRegistro() {
        const radioFisica = document.getElementById('radioFisica');
        const radioJuridica = document.getElementById('radioJuridica');
        const secaoPf = document.getElementById('secaoPf');
        const secaoPj = document.getElementById('secaoPj');
        const cardPf = document.getElementById('cardTipoFisica');
        const cardPj = document.getElementById('cardTipoJuridica');
        const inputNome = document.getElementById('nome');
        const inputCpf = document.getElementById('cpf');
        const inputRazao = document.getElementById('razao_social');
        const inputCnpj = document.getElementById('cnpj');

        if (!radioFisica || !radioJuridica) return;

        const isFisica = radioFisica.checked;

        if (cardPf && cardPj) {
            cardPf.classList.toggle('active', isFisica);
            cardPj.classList.toggle('active', !isFisica);
        }

        if (secaoPf && secaoPj) {
            secaoPf.style.display = isFisica ? 'block' : 'none';
            secaoPj.style.display = isFisica ? 'none' : 'block';
        }

        if (inputNome && inputCpf && inputRazao && inputCnpj) {
            if (isFisica) {
                inputNome.setAttribute('required', 'required');
                inputCpf.setAttribute('required', 'required');
                inputRazao.removeAttribute('required');
                inputCnpj.removeAttribute('required');
            } else {
                inputNome.removeAttribute('required');
                inputCpf.removeAttribute('required');
                inputRazao.setAttribute('required', 'required');
                inputCnpj.setAttribute('required', 'required');
            }
        }
    }

    // Máscara CPF
    function mascaraCPF(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.value = v;
    }

    // Máscara CNPJ
    function mascaraCNPJ(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 14);
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
        input.value = v;
    }

    // Máscara Celular / WhatsApp
    function mascaraCelular(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 11);
        if (v.length <= 10) {
            v = v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            v = v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
        }
        input.value = v;
    }

    // Máscara CEP
    function mascaraCEP(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 8);
        v = v.replace(/(\d{5})(\d)/, '$1-$2');
        input.value = v;
    }

    // Busca automática no ViaCEP
    function buscarCEP(cepValor) {
        const cepLimpo = cepValor.replace(/\D/g, '');
        const feedback = document.getElementById('cepFeedback');

        if (cepLimpo.length !== 8) {
            return;
        }

        if (feedback) {
            feedback.innerHTML = '<span class="cep-loading">Buscando endereço...</span>';
        }

        fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
            .then(res => res.json())
            .then(data => {
                if (data.erro) {
                    if (feedback) {
                        feedback.innerHTML = '<span class="cep-error">CEP não encontrado na base dos Correios.</span>';
                    }
                    return;
                }

                if (data.logradouro) {
                    document.getElementById('rua').value = data.logradouro;
                }
                if (data.bairro) {
                    document.getElementById('bairro').value = data.bairro;
                }
                if (data.localidade) {
                    document.getElementById('cidade').value = data.localidade;
                }
                if (data.uf) {
                    document.getElementById('estado').value = data.uf;
                }

                if (feedback) {
                    feedback.innerHTML = '<span class="cep-success">Endereço preenchido com sucesso!</span>';
                    setTimeout(() => { feedback.innerHTML = ''; }, 4000);
                }

                // Coloca foco no campo de número
                const inputNumero = document.getElementById('numero');
                if (inputNumero) {
                    inputNumero.focus();
                }
            })
            .catch(() => {
                if (feedback) {
                    feedback.innerHTML = '<span class="cep-error">Não foi possível consultar o CEP automaticamente.</span>';
                }
            });
    }

    // Alternar visibilidade de senhas (olho)
    function toggleVisibilidadeSenha(inputId, botao) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        if (isPassword) {
            botao.innerHTML = `
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                </svg>
            `;
        } else {
            botao.innerHTML = `
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            `;
        }
    }

    // Inicialização ao carregar o DOM
    document.addEventListener('DOMContentLoaded', function () {
        alternarTipoRegistro();
    });
</script>
@endsection
