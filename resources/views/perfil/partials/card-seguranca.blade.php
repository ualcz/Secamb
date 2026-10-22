<div class="profile-card profile-card-full">
    <div class="profile-card-header">
        <div class="profile-card-icon icon-purple">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h3>Conta &bull; Acesso e Segurança</h3>
    </div>

    <div class="profile-card-body">
        <div class="profile-meta-grid">
            <div class="profile-meta-item">
                <span class="profile-field-label">Método de Autenticação</span>
                <div class="profile-field-value">
                    @if($usuario->hasLoginSocial())
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#4285F4">
                            <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z"/>
                        </svg>
                        <span>Google OAuth</span>
                    @else
                        <svg width="18" height="18" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>E-mail e Senha</span>
                    @endif
                </div>
            </div>

            <div class="profile-meta-item">
                <span class="profile-field-label">Data de Cadastro</span>
                <div class="profile-field-value">
                    <svg width="18" height="18" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y \à\s H:i') : 'Não registrada' }}</span>
                </div>
            </div>

            <div class="profile-meta-item">
                <span class="profile-field-label">Última Atualização</span>
                <div class="profile-field-value">
                    <svg width="18" height="18" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $usuario->updated_at ? $usuario->updated_at->format('d/m/Y \à\s H:i') : '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Banner de Ação de Segurança --}}
        <div class="security-action-box">
            <div class="security-action-left">
                <div class="security-action-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div class="security-action-text">
                    <h4>Proteção e Credenciais</h4>
                    <p>Mantenha sua conta segura alterando ou redefinindo sua senha de acesso periodicamente.</p>
                </div>
            </div>

            <a href="{{ route('perfil.senha') }}" class="btn-security-action">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Gerenciar Senha</span>
            </a>
        </div>
    </div>
</div>
