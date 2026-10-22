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
