@extends('layouts.app')

@section('title', 'Meu Perfil - SECAMB Seabra')
@section('tag', 'Perfil')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ filemtime(public_path('css/profile.css')) }}">

<div class="profile-container">

    {{-- Feedback de Mensagens --}}
    @if (session('success') || session('sucesso') || session('status'))
        <div class="alert-sucesso">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Sucesso!</strong> {{ session('success') ?? session('sucesso') ?? session('status') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-erro">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Atenção:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Hero Card do Usuário --}}
    <div class="profile-hero">
        <div class="profile-hero-left">
            <div class="profile-avatar-lg">
                @if($usuario->avatar)
                    <img src="{{ $usuario->avatar }}" alt="{{ $usuario->nome }}">
                @else
                    @php
                        $palavras = explode(' ', trim($usuario->nome));
                        $iniciais = strtoupper(substr($palavras[0], 0, 1));
                        if (count($palavras) > 1) {
                            $iniciais .= strtoupper(substr(end($palavras), 0, 1));
                        }
                    @endphp
                    <span>{{ $iniciais }}</span>
                @endif
            </div>

            <div class="profile-hero-info">
                <h1>{{ $usuario->nome }}</h1>
                <p class="profile-hero-email">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $usuario->email }}</span>
                </p>

                <div class="profile-badges">
                    @if($usuario->isAdmin())
                        <span class="profile-badge badge-role-admin">Administrador Geral</span>
                    @elseif($usuario->isServidor())
                        <span class="profile-badge badge-role-servidor">Servidor Municipal</span>
                    @else
                        <span class="profile-badge badge-role-cidadao">
                            Cidadão &bull; {{ $usuario->isPessoaJuridica() ? 'Pessoa Jurídica' : 'Pessoa Física' }}
                        </span>
                    @endif

                    @if($usuario->hasLoginSocial())
                        <span class="profile-badge" style="background:#eef2ff; color:#4f46e5;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z"/>
                            </svg>
                            Google OAuth
                        </span>
                    @endif

                    <span class="profile-badge badge-active">Ativo</span>
                </div>
            </div>
        </div>

        <div class="profile-hero-actions">
            @if($usuario->isAdmin())
                <a href="{{ route('perfil.senha') }}" class="btn-perfil-edit">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Alterar Senha</span>
                </a>
            @else
                <a href="{{ route('perfil.edit') }}" class="btn-perfil-edit">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Editar Meus Dados</span>
                </a>

                <a href="{{ route('perfil.senha') }}" class="btn-perfil-secundario">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Alterar Senha</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Grid com Informações Cadastrais --}}
    <div class="profile-grid">

        {{-- Card 1: Identificação --}}
        <div class="profile-card">
            <div class="profile-card-header">
                <div class="profile-card-icon icon-blue">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3>Identificação do Usuário</h3>
            </div>

            <div class="profile-card-body">
                <div class="profile-field">
                    <span class="profile-field-label">Nome Completo</span>
                    <span class="profile-field-value">{{ $usuario->nome }}</span>
                </div>

                <div class="profile-field">
                    <span class="profile-field-label">E-mail</span>
                    <span class="profile-field-value">{{ $usuario->email }}</span>
                </div>

                @if(!$usuario->isAdmin())
                    @if($usuario->isPessoaJuridica() && !empty($usuario->razao_social))
                        <div class="profile-field">
                            <span class="profile-field-label">Razão Social Registrada</span>
                            <span class="profile-field-value">{{ $usuario->razao_social }}</span>
                        </div>
                    @endif

                    <div class="profile-field">
                        <span class="profile-field-label">{{ $usuario->isPessoaJuridica() ? 'CNPJ' : 'CPF' }}</span>
                        <span class="profile-field-value">
                            {{ $usuario->documento_identificacao ?: 'Não informado' }}
                        </span>
                    </div>

                    <div class="profile-field">
                        <span class="profile-field-label">Tipo de Cadastro</span>
                        <span class="profile-field-value">
                            {{ $usuario->tipo_pessoa_formatado }}
                        </span>
                    </div>
                @else
                    <div class="profile-field">
                        <span class="profile-field-label">Perfil de Acesso</span>
                        <span class="profile-field-value" style="color: #be185d; font-weight: 700;">
                            Administrador Geral do Sistema
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Informações de Contato e Endereço: SOMENTE PARA NÃO-ADMIN --}}
        @if(!$usuario->isAdmin())
            {{-- Card 2: Contato --}}
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3>Informações de Contato</h3>
                </div>

                <div class="profile-card-body">
                    <div class="profile-field">
                        <span class="profile-field-label">E-mail Principal</span>
                        <span class="profile-field-value">{{ $usuario->email }}</span>
                    </div>

                    <div class="profile-field">
                        <span class="profile-field-label">Telefone / WhatsApp</span>
                        <span class="profile-field-value">
                            @if($usuario->celular)
                                {{ $usuario->celular }}
                            @else
                                <span class="profile-field-muted">Não cadastrado</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Card 3: Endereço Residencial / Sede --}}
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-card-icon icon-amber">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3>Endereço do Usuário</h3>
                </div>

                <div class="profile-card-body">
                    @if($usuario->endereco && !empty($usuario->endereco->rua))
                        <div class="profile-field">
                            <span class="profile-field-label">Logradouro / Rua</span>
                            <span class="profile-field-value">
                                {{ $usuario->endereco->rua }}
                                @if($usuario->endereco->numero)
                                    , Nº {{ $usuario->endereco->numero }}
                                @endif
                                @if($usuario->endereco->complemento)
                                    ({{ $usuario->endereco->complemento }})
                                @endif
                            </span>
                        </div>

                        <div class="profile-field">
                            <span class="profile-field-label">Bairro</span>
                            <span class="profile-field-value">{{ $usuario->endereco->bairro ?: 'Não informado' }}</span>
                        </div>

                        <div class="profile-field">
                            <span class="profile-field-label">Cidade / Estado</span>
                            <span class="profile-field-value">
                                {{ $usuario->endereco->cidade ?? 'Seabra' }} &mdash; {{ $usuario->endereco->estado ?? 'BA' }}
                            </span>
                        </div>

                        <div class="profile-field">
                            <span class="profile-field-label">CEP</span>
                            <span class="profile-field-value">{{ $usuario->endereco->cep ?: 'Não informado' }}</span>
                        </div>
                    @else
                        <div class="profile-field">
                            <span class="profile-field-muted">Nenhum endereço cadastrado.</span>
                            <p style="margin: 6px 0 0; font-size: 0.85rem; color: #64748b;">
                                Adicione o seu endereço residencial ou sede para facilitar o preenchimento de requerimentos.
                            </p>
                            <a href="{{ route('perfil.edit') }}" style="margin-top: 10px; display: inline-block; color: #0284c7; font-weight: 600; font-size: 0.88rem;">
                                Cadastrar endereço agora &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Card: Dados da Conta e Segurança (Sempre visível) --}}
        <div class="profile-card">
            <div class="profile-card-header">
                <div class="profile-card-icon icon-purple">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3>Conta &bull; Acesso e Segurança</h3>
            </div>

            <div class="profile-card-body">
                <div class="profile-field">
                    <span class="profile-field-label">Método de Autenticação</span>
                    <span class="profile-field-value">
                        @if($usuario->hasLoginSocial())
                            Conta vinculada via Google
                        @else
                            E-mail e Senha protegida
                        @endif
                    </span>
                </div>

                <div class="profile-field">
                    <span class="profile-field-label">Data de Cadastro</span>
                    <span class="profile-field-value">
                        {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y \à\s H:i') : 'Data não registrada' }}
                    </span>
                </div>

                <div class="profile-field">
                    <span class="profile-field-label">Última Atualização</span>
                    <span class="profile-field-value">
                        {{ $usuario->updated_at ? $usuario->updated_at->format('d/m/Y \à\s H:i') : '—' }}
                    </span>
                </div>

                <div style="margin-top: 6px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                    <a href="{{ route('perfil.senha') }}" style="color: #0284c7; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Redefinir ou trocar minha senha de acesso
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
