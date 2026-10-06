<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastre-se - SECAMB Seabra</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .register-wrapper {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 32px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .tipo-registro-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 8px 12px;
            background: #f1f5f9;
            border-radius: 10px;
            justify-content: center;
        }

        .tipo-radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
        }

        .tipo-radio-label input[type="radio"] {
            accent-color: #0284c7;
            width: 17px;
            height: 17px;
            cursor: pointer;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 600px) {
            .grid-2 {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        .btn-cadastrar {
            width: 100%;
            padding: 13px 20px;
            background-color: #0284c7;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            transition: all 0.2s ease;
        }

        .btn-cadastrar:hover {
            background-color: #0369a1;
        }

        .toggle-senha-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="register-wrapper">

        <!-- Logo / Marca -->
        <a href="{{ route('home') }}" class="login-brand">
            <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="Logo Prefeitura de Seabra">
            <div class="login-brand-text">
                <span class="login-brand-title">SECAMB</span>
                <span class="login-brand-subtitle">Licenciamento Ambiental — Seabra</span>
            </div>
        </a>

        <!-- Card de Cadastro -->
        <div class="login-card">

            <h1>Cadastre-se</h1>
            <p class="login-subtitulo">
                Preencha seus dados para solicitar e acompanhar processos ambientais.
            </p>

            <!-- Erros -->
            @if($errors->any())
                <div class="login-erro" style="margin-bottom: 18px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Formulário -->
            <form method="POST" action="{{ route('register.submit') }}" id="formCadastro">
                @csrf

                <!-- Tipo de Registro -->
                <div class="campo">
                    <label style="font-weight: 700; margin-bottom: 8px; display: block; color: #475569;">Tipo de Registro:</label>
                    <div class="tipo-registro-group">
                        <label class="tipo-radio-label">
                            <input type="radio" name="tipo_registro" value="fisica" id="radioFisica" {{ old('tipo_registro', 'fisica') === 'fisica' ? 'checked' : '' }} onchange="alternarTipoRegistro()">
                            Pessoa Física
                        </label>
                        <label class="tipo-radio-label">
                            <input type="radio" name="tipo_registro" value="juridica" id="radioJuridica" {{ old('tipo_registro') === 'juridica' ? 'checked' : '' }} onchange="alternarTipoRegistro()">
                            Pessoa Jurídica
                        </label>
                    </div>
                </div>

                <!-- Campos de Pessoa Física -->
                <div id="secaoPessoaFisica">
                    <div class="campo">
                        <label for="nome">Nome completo:*</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-user icon-prefix"></i>
                            <input type="text" id="nome" name="nome" value="{{ old('nome') }}" placeholder="Nome Completo">
                        </div>
                    </div>

                    <div class="campo">
                        <label for="cpf">CPF:*</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-id-card icon-prefix"></i>
                            <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}" placeholder="000.000.000-00" maxlength="14" oninput="mascaraCPF(this)">
                        </div>
                    </div>
                </div>

                <!-- Campos de Pessoa Jurídica -->
                <div id="secaoPessoaJuridica" style="display: none;">
                    <div class="campo">
                        <label for="razao_social">Razão Social:*</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-building icon-prefix"></i>
                            <input type="text" id="razao_social" name="razao_social" value="{{ old('razao_social') }}" placeholder="Nome da Empresa / Razão Social">
                        </div>
                    </div>

                    <div class="campo">
                        <label for="cnpj">CNPJ:*</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-briefcase icon-prefix"></i>
                            <input type="text" id="cnpj" name="cnpj" value="{{ old('cnpj') }}" placeholder="00.000.000/0001-00" maxlength="18" oninput="mascaraCNPJ(this)">
                        </div>
                    </div>
                </div>

                <!-- E-mail -->
                <div class="campo">
                    <label for="email">E-mail:*</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-envelope icon-prefix"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="jose@email.com" required>
                    </div>
                </div>

                <!-- Endereço -->
                <div class="campo">
                    <label for="endereco">Endereço:*</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-location-dot icon-prefix"></i>
                        <input type="text" id="endereco" name="endereco" value="{{ old('endereco') }}" placeholder="Rua Horácio de Matos, Nº" required>
                    </div>
                </div>

                <!-- Bairro e CEP -->
                <div class="grid-2">
                    <div class="campo">
                        <label for="bairro">Bairro:*</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-map-pin icon-prefix"></i>
                            <input type="text" id="bairro" name="bairro" value="{{ old('bairro') }}" placeholder="Bairro" required>
                        </div>
                    </div>

                    <div class="campo">
                        <label for="cep">CEP:*</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-envelopes-bulk icon-prefix"></i>
                            <input type="text" id="cep" name="cep" value="{{ old('cep') }}" placeholder="46980-000" maxlength="9" oninput="mascaraCEP(this)" required>
                        </div>
                    </div>
                </div>

                <!-- Senha e Confirmação de Senha -->
                <div class="grid-2">
                    <div class="campo">
                        <label for="password">Senha:*</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock icon-prefix"></i>
                            <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                        </div>
                    </div>

                    <div class="campo">
                        <label for="password_confirmation">Confirmação da Senha:*</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock icon-prefix"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Digite sua senha novamente" required>
                        </div>
                    </div>
                </div>

                <!-- Botão Cadastrar -->
                <button type="submit" class="btn-cadastrar">
                    <span>Cadastrar</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div class="login-footer-links" style="margin-top: 20px; text-align: center;">
                <p>Já possui conta? <a href="{{ route('login') }}" style="color: #0284c7; font-weight: 600;">Faça login</a></p>
            </div>
        </div>

    </div>

    <script>
        function alternarTipoRegistro() {
            const isFisica = document.getElementById('radioFisica').checked;
            const secaoFisica = document.getElementById('secaoPessoaFisica');
            const secaoJuridica = document.getElementById('secaoPessoaJuridica');
            const inputNome = document.getElementById('nome');
            const inputCpf = document.getElementById('cpf');
            const inputRazao = document.getElementById('razao_social');
            const inputCnpj = document.getElementById('cnpj');

            if (isFisica) {
                secaoFisica.style.display = 'block';
                secaoJuridica.style.display = 'none';
                inputNome.setAttribute('required', 'required');
                inputCpf.setAttribute('required', 'required');
                inputRazao.removeAttribute('required');
                inputCnpj.removeAttribute('required');
            } else {
                secaoFisica.style.display = 'none';
                secaoJuridica.style.display = 'block';
                inputNome.removeAttribute('required');
                inputCpf.removeAttribute('required');
                inputRazao.setAttribute('required', 'required');
                inputCnpj.setAttribute('required', 'required');
            }
        }

        function mascaraCPF(i) {
            let v = i.value.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            i.value = v;
        }

        function mascaraCNPJ(i) {
            let v = i.value.replace(/\D/g, "");
            v = v.replace(/^(\d{2})(\d)/, "$1.$2");
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
            v = v.replace(/(\d{4})(\d)/, "$1-$2");
            i.value = v;
        }

        function mascaraCEP(i) {
            let v = i.value.replace(/\D/g, "");
            v = v.replace(/^(\d{5})(\d)/, "$1-$2");
            i.value = v;
        }

        // Inicializa o formulário na carga
        document.addEventListener('DOMContentLoaded', alternarTipoRegistro);
    </script>
</body>

</html>
