<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete seu perfil - SECAMB</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .perfil-wrapper {
            width: 100%;
            max-width: 520px;
            margin: auto;
            padding: 40px 16px 48px;
        }

        .perfil-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 28px 26px;
        }

        .perfil-card h1 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .perfil-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 22px;
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 18px 0 10px;
        }

        .tipo-grupo {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        .tipo-opcao {
            flex: 1;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
        }

        .tipo-opcao:has(input:checked) {
            border-color: #059669;
            background: #f0fdf4;
        }

        .tipo-opcao input[type="radio"] { display: none; }

        .tipo-opcao label {
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .tipo-opcao:has(input:checked) label { color: #059669; }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 480px) {
            .grid-2 { grid-template-columns: 1fr; }
        }

        .btn-salvar {
            width: 100%;
            height: 44px;
            background-color: #059669;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            margin-top: 22px;
            transition: background-color 0.15s;
        }

        .btn-salvar:hover { background-color: #047857; }

        .nota-contato {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: -6px;
            margin-bottom: 2px;
        }
    </style>
</head>

<body>

<div class="perfil-wrapper">

    <a href="{{ route('home') }}" class="login-brand">
        <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="Logo Prefeitura de Seabra">
        <div class="login-brand-text">
            <span class="login-brand-title">SECAMB</span>
            <span class="login-brand-subtitle">Licenciamento Ambiental — Seabra</span>
        </div>
    </a>

    <div class="perfil-card">

        <h1>Complete seu cadastro</h1>
        <p class="perfil-subtitle">Preencha os campos abaixo para continuar usando o sistema.</p>

        @if($errors->any())
            <div class="login-erro">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('perfil.completar.salvar') }}" novalidate>
            @csrf

            {{-- Tipo de registro --}}
            <p class="section-label">Tipo de cadastro</p>
            <div class="tipo-grupo">
                <div class="tipo-opcao">
                    <input type="radio" name="tipo_registro" id="tipo_fisica" value="fisica"
                        {{ old('tipo_registro', $usuario->tipo_registro ?? 'fisica') === 'fisica' ? 'checked' : '' }}>
                    <label for="tipo_fisica">
                        <i class="fa-regular fa-user"></i> Pessoa Física
                    </label>
                </div>
                <div class="tipo-opcao">
                    <input type="radio" name="tipo_registro" id="tipo_juridica" value="juridica"
                        {{ old('tipo_registro', $usuario->tipo_registro ?? 'fisica') === 'juridica' ? 'checked' : '' }}>
                    <label for="tipo_juridica">
                        <i class="fa-regular fa-building"></i> Pessoa Jurídica
                    </label>
                </div>
            </div>

            {{-- Campos Pessoa Física --}}
            <div id="campos-pf">
                <div class="campo">
                    <label for="nome">Nome completo <span style="color:#ef4444">*</span></label>
                    <div class="input-icon">
                        <i class="fa-solid fa-user icon-prefix"></i>
                        <input type="text" id="nome" name="nome"
                            value="{{ old('nome', $usuario->nome) }}" placeholder="Seu nome completo">
                    </div>
                </div>
                <div class="campo">
                    <label for="cpf">CPF <span style="color:#ef4444">*</span></label>
                    <div class="input-icon">
                        <i class="fa-solid fa-id-card icon-prefix"></i>
                        <input type="text" id="cpf" name="cpf"
                            value="{{ old('cpf', $usuario->cpf) }}" placeholder="000.000.000-00" maxlength="14">
                    </div>
                </div>
            </div>

            {{-- Campos Pessoa Jurídica --}}
            <div id="campos-pj" style="display:none">
                <div class="campo">
                    <label for="razao_social">Razão social <span style="color:#ef4444">*</span></label>
                    <div class="input-icon">
                        <i class="fa-regular fa-building icon-prefix"></i>
                        <input type="text" id="razao_social" name="razao_social"
                            value="{{ old('razao_social', $usuario->razao_social) }}" placeholder="Razão social">
                    </div>
                </div>
                <div class="campo">
                    <label for="cnpj">CNPJ <span style="color:#ef4444">*</span></label>
                    <div class="input-icon">
                        <i class="fa-solid fa-file-contract icon-prefix"></i>
                        <input type="text" id="cnpj" name="cnpj"
                            value="{{ old('cnpj', $usuario->cnpj) }}" placeholder="00.000.000/0000-00" maxlength="18">
                    </div>
                </div>
            </div>

            {{-- Contato --}}
            <p class="section-label">Contato <span style="color:#ef4444">*</span></p>
            <div class="campo">
                <label for="celular">Telefone ou celular</label>
                <div class="input-icon">
                    <i class="fa-solid fa-phone icon-prefix"></i>
                    <input type="text" id="celular" name="celular"
                        value="{{ old('celular', $usuario->celular) }}"
                        placeholder="(00) 00000-0000" maxlength="20">
                </div>
            </div>

            {{-- Endereço --}}
            <p class="section-label">Endereço</p>
            <div class="campo">
                <label for="cep">CEP <span style="color:#ef4444">*</span></label>
                <div class="input-icon">
                    <i class="fa-solid fa-location-dot icon-prefix"></i>
                    <input type="text" id="cep" name="cep"
                        value="{{ old('cep', $usuario->endereco?->cep) }}" placeholder="00000-000" maxlength="9">
                </div>
            </div>
            <div class="campo">
                <label for="rua">Rua / Logradouro <span style="color:#ef4444">*</span></label>
                <div class="input-icon">
                    <i class="fa-solid fa-road icon-prefix"></i>
                    <input type="text" id="rua" name="rua"
                        value="{{ old('rua', $usuario->endereco?->rua) }}" placeholder="Ex: Rua das Flores, 123">
                </div>
            </div>
            <div class="campo">
                <label for="bairro">Bairro <span style="color:#ef4444">*</span></label>
                <div class="input-icon">
                    <i class="fa-solid fa-map-pin icon-prefix"></i>
                    <input type="text" id="bairro" name="bairro"
                        value="{{ old('bairro', $usuario->endereco?->bairro) }}" placeholder="Bairro">
                </div>
            </div>
            <input type="hidden" name="cidade" value="Seabra">
            <input type="hidden" name="estado" value="BA">

            <button type="submit" class="btn-salvar">
                <i class="fa-solid fa-check"></i> Salvar e continuar
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const radioFisica   = document.getElementById('tipo_fisica');
    const radioJuridica = document.getElementById('tipo_juridica');
    const camposPf      = document.getElementById('campos-pf');
    const camposPj      = document.getElementById('campos-pj');

    function alternar() {
        const isPj = radioJuridica.checked;
        camposPf.style.display = isPj ? 'none' : '';
        camposPj.style.display = isPj ? '' : 'none';
        camposPf.querySelectorAll('input').forEach(el => el.disabled = isPj);
        camposPj.querySelectorAll('input').forEach(el => el.disabled = !isPj);
    }

    radioFisica.addEventListener('change', alternar);
    radioJuridica.addEventListener('change', alternar);
    alternar();

    // Máscaras
    document.getElementById('cpf').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = v;
    });

    document.getElementById('cnpj').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 14);
        v = v.replace(/^(\d{2})(\d)/, '$1.$2')
             .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
             .replace(/\.(\d{3})(\d)/, '.$1/$2')
             .replace(/(\d{4})(\d)/, '$1-$2');
        this.value = v;
    });

    document.getElementById('celular').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 11);
        v = v.length <= 10
            ? v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2')
            : v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
        this.value = v;
    });

    // CEP via ViaCEP
    document.getElementById('cep').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 8);
        this.value = v.replace(/(\d{5})(\d)/, '$1-$2');
    });

    document.getElementById('cep').addEventListener('blur', function () {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length !== 8) return;
        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(r => r.json())
            .then(function (d) {
                if (d.erro) return;
                document.getElementById('rua').value    = d.logradouro || '';
                document.getElementById('bairro').value = d.bairro     || '';
            })
            .catch(function () {});
    });
})();
</script>
</body>
</html>
