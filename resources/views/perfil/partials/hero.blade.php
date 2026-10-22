<link rel="stylesheet" href="{{ asset('css/profile/hero.css') }}?v={{ filemtime(public_path('css/profile/hero.css')) }}">

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
