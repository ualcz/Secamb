<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SECAMB Seabra</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    @vite('resources/js/login.js')
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

            <div class="login-footer-links">
                <p>Ainda não tem cadastro? <a href="{{ route('home') }}">Saiba como solicitar acesso</a></p>
            </div>
        </div>

    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>

</html>