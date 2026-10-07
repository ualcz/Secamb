<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SECAMB Seabra</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <div class="login-wrapper">

        <!-- Logo / Marca -->
        <a href="{{ route('home') }}" class="login-brand">
            <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="Logo Prefeitura de Seabra">
            <div class="login-brand-text">
                <span class="login-brand-title">SECAMB</span>
                <span class="login-brand-subtitle">Licenciamento Ambiental — Seabra</span>
            </div>
        </a>

        <!-- Card de Login -->
        <div class="login-card">

            <h1>Acessar o sistema</h1>
            <p class="login-subtitulo">
                Informe seu e-mail e senha cadastrados.
            </p>

            <!-- Erros -->
            @if($errors->any())
                <div class="login-erro">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="login-sucesso">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Formulário -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- E-mail -->
                <div class="campo">
                    <label for="email">E-mail</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-envelope icon-prefix"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="seu@email.com.br"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Senha -->
                <div class="campo">
                    <label for="senha">Senha</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock icon-prefix"></i>
                        <input
                            type="password"
                            id="senha"
                            name="password"
                            placeholder="Digite sua senha"
                            required
                        >
                        <i class="fa-solid fa-eye mostrar-senha" id="toggleSenha" title="Mostrar/ocultar senha"></i>
                    </div>
                </div>

                <!-- Lembrar-me -->
                <div class="campo campo-checkbox">
                    <label class="label-checkbox">
                        <input type="checkbox" name="lembrar" value="1"> Manter-me conectado
                    </label>
                </div>

                <!-- Botão de Entrar -->
                <button type="submit" class="btn-entrar">
                    <span>Entrar</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            {{-- Divisor --}}
            <div class="login-divisor">
                <span>ou continue com</span>
            </div>

            {{-- Login com Google --}}
            <a href="{{ route('auth.google') }}" class="btn-google" id="btn-google-login">
                <svg class="google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span>Entrar com Google</span>
            </a>

            <div class="login-footer-links">
                <p>Não possui conta? <a href="{{ route('register') }}">Cadastre-se</a></p>
            </div>
        </div>

    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>

</html>