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
