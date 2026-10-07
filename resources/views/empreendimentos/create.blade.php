@extends('layouts.app')

@section('title', 'Cadastrar Empreendimento - SECAMB')
@section('tag', 'Empreendimentos')

@section('content')
<link rel="stylesheet" href="{{ asset('css/empreendimentos.css') }}?v={{ filemtime(public_path('css/empreendimentos.css')) }}">

<div class="emp-container">

    {{-- Botão Voltar --}}
    <div style="margin-bottom: 20px;">
        @if(request('retorno') === 'requerimento')
            <a href="{{ route('requerimentos.aluno.novo') }}" class="emp-btn-cancel" style="display: inline-flex; align-items: center; gap: 6px;">
                ← Voltar ao Requerimento
            </a>
        @else
            <a href="{{ route('empreendimentos.index') }}" class="emp-btn-cancel" style="display: inline-flex; align-items: center; gap: 6px;">
                ← Voltar para Empreendimentos
            </a>
        @endif
    </div>

    {{-- Notificação de Erros --}}
    @if ($errors->any())
        <div class="emp-alert emp-alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Corrija os erros abaixo:</strong>
                <ul style="margin: 6px 0 0 18px; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="emp-form-card">
        <div class="emp-form-header">
            <h2>Cadastrar Novo Empreendimento</h2>
            <p>Preencha os dados da empresa/empreendimento e do responsável legal perante a Secretaria Municipal de Meio Ambiente de Seabra.</p>
        </div>

        <form action="{{ route('empreendimentos.store') }}" method="POST">
            @csrf

            @if(request('retorno') || old('retorno'))
                <input type="hidden" name="retorno" value="{{ old('retorno', request('retorno')) }}">
            @endif

            {{-- 1. Dados do Empreendimento --}}
            <div class="emp-section">
                <h3 class="emp-section-title">
                    <svg width="18" height="18" fill="none" stroke="#0284c7" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    1. Identificação do Empreendimento
                </h3>

                <div class="emp-form-grid">
                    <div class="emp-form-group emp-col-8">
                        <label for="nome">Nome do Empreendimento / Razão Social <span class="required">*</span></label>
                        <input type="text" name="nome" id="nome" class="emp-input" value="{{ old('nome') }}" placeholder="Ex: Pousada Chapada Diamantina LTDA" required>
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" name="cnpj" id="cnpj" class="emp-input" value="{{ old('cnpj') }}" placeholder="00.000.000/0000-00">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="tipo_atividade">Atividade Econômica Principal</label>
                        <input type="text" name="tipo_atividade" id="tipo_atividade" class="emp-input" value="{{ old('tipo_atividade') }}" placeholder="Ex: Hotelaria, Mineração, Agropecuária, Comércio">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="fase_operacao">Fase de Operação</label>
                        <select name="fase_operacao" id="fase_operacao" class="emp-select">
                            <option value="">Selecione uma opção...</option>
                            <option value="Localização" {{ old('fase_operacao') === 'Localização' ? 'selected' : '' }}>Localização</option>
                            <option value="Instalação" {{ old('fase_operacao') === 'Instalação' ? 'selected' : '' }}>Instalação</option>
                            <option value="Operação" {{ old('fase_operacao') === 'Operação' ? 'selected' : '' }}>Operação</option>
                            <option value="Não se Aplica" {{ old('fase_operacao') === 'Não se Aplica' ? 'selected' : '' }}>Não se Aplica</option>
                        </select>
                    </div>

                    <div class="emp-form-group emp-col-8">
                        <label for="endereco">Endereço / Logradouro</label>
                        <input type="text" name="endereco" id="endereco" class="emp-input" value="{{ old('endereco') }}" placeholder="Rua, Avenida, Rodovia ou Estrada, Nº">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="bairro">Bairro / Povoado</label>
                        <input type="text" name="bairro" id="bairro" class="emp-input" value="{{ old('bairro') }}" placeholder="Ex: Centro, Povoado Velame">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="cep">CEP</label>
                        <input type="text" name="cep" id="cep" class="emp-input" value="{{ old('cep') }}" placeholder="46900-000">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="cidade">Município</label>
                        <input type="text" name="cidade" id="cidade" class="emp-input" value="{{ old('cidade', 'Seabra') }}" placeholder="Seabra">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="estado">UF</label>
                        <input type="text" name="estado" id="estado" class="emp-input" value="{{ old('estado', 'BA') }}" maxlength="2" placeholder="BA" style="text-transform: uppercase;">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="bacia_hidrografica">Bacia Hidrográfica</label>
                        <input type="text" name="bacia_hidrografica" id="bacia_hidrografica" class="emp-input" value="{{ old('bacia_hidrografica') }}" placeholder="Ex: Bacia do Rio Paraguaçu">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="recurso_hidrico">Recurso Hídrico Utilizado / Impactado</label>
                        <input type="text" name="recurso_hidrico" id="recurso_hidrico" class="emp-input" value="{{ old('recurso_hidrico') }}" placeholder="Ex: Rio Campestre, Poço Artesiano">
                    </div>
                </div>
            </div>

            {{-- 2. Dados de Contato do Responsável --}}
            <div class="emp-section">
                <h3 class="emp-section-title">
                    <svg width="18" height="18" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    2. Dados de Contato do Responsável
                </h3>

                <div class="emp-form-grid">
                    <div class="emp-form-group emp-col-6">
                        <label for="contato_nome">Nome do Contato / Representante</label>
                        <input type="text" name="contato_nome" id="contato_nome" class="emp-input" value="{{ old('contato_nome', auth()->user()->nome) }}" placeholder="Nome do responsável">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_email">E-mail de Contato</label>
                        <input type="email" name="contato_email" id="contato_email" class="emp-input" value="{{ old('contato_email', auth()->user()->email) }}" placeholder="contato@empresa.com">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_telefone">Telefone Fixo (opcional)</label>
                        <input type="text" name="contato_telefone" id="contato_telefone" class="emp-input" value="{{ old('contato_telefone', auth()->user()->telefone) }}" placeholder="(75) 3331-0000">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_celular">Celular / WhatsApp (opcional)</label>
                        <input type="text" name="contato_celular" id="contato_celular" class="emp-input" value="{{ old('contato_celular', auth()->user()->celular) }}" placeholder="(75) 99999-9999">
                    </div>
                </div>
            </div>

            {{-- 3. Declaração Legal --}}
            <div class="emp-termo-box">
                <div class="emp-termo-text">
                    <strong>Declaração do Representante Legal:</strong><br>
                    Declaro que são verdadeiras as informações prestadas pelo(a) ora requerente neste cadastro e nos processos de licenciamento ambiental decorrentes, respondendo administrativa, civil e penalmente por qualquer inconsistência ou falsidade, conforme a Lei Municipal nº 498/2013, o Decreto Estadual 14.024/2012 e a Lei Federal nº 9.605/98 (Lei de Crimes Ambientais).
                </div>
                <label class="emp-termo-check">
                    <input type="checkbox" name="termo_aceito" value="1" {{ old('termo_aceito', '1') ? 'checked' : '' }} required>
                    <span>Confirmo a veracidade das informações e assumo a responsabilidade técnica e legal.</span>
                </label>
            </div>

            {{-- Ações --}}
            <div class="emp-form-actions">
                <a href="{{ request('retorno') === 'requerimento' ? route('requerimentos.aluno.novo') : route('empreendimentos.index') }}" class="emp-btn-cancel">
                    Cancelar
                </a>
                <button type="submit" class="emp-btn-submit">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Cadastrar <span>Empreendimento</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    // Máscaras amigáveis para CNPJ, CEP e Telefones
    document.addEventListener('DOMContentLoaded', function () {
        const cnpjInput = document.getElementById('cnpj');
        if (cnpjInput) {
            cnpjInput.addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 14) v = v.substring(0, 14);
                v = v.replace(/^(\d{2})(\d)/, '$1.$2');
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
                v = v.replace(/(\d{4})(\d)/, '$1-$2');
                e.target.value = v;
            });
        }

        const cepInput = document.getElementById('cep');
        if (cepInput) {
            cepInput.addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 8) v = v.substring(0, 8);
                v = v.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = v;
            });
        }
    });
</script>
@endsection
