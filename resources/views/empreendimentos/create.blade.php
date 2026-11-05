@extends('layouts.app')

@section('title', 'Cadastrar Empreendimento - SECAMB')
@section('tag', 'Empreendimentos')

@section('content')
<link rel="stylesheet" href="{{ asset('css/empreendimentos.css') }}?v={{ filemtime(public_path('css/empreendimentos.css')) }}">

<div class="emp-container">

    {{-- Botão Voltar --}}
    <div style="margin-bottom: 20px;">
        @if(request('retorno') === 'requerimento')
            <a href="{{ route('requerimentos.cidadao.novo') }}" class="emp-btn-cancel" style="display: inline-flex; align-items: center; gap: 6px;">
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
                        <x-floating-input
                            name="nome"
                            label="Nome Fantasia / Razão Social"
                            value="{{ old('nome_fantasia') }}"
                            placeholder="Ex: Empresa LTDA"/>
                    </div>



                    <div class="emp-form-group emp-col-4">
                        <x-floating-input
                            id="cnpj"
                            name="cnpj"
                            label="CNPJ"
                            value="{{ old('cnpj') }}"
                            placeholder="00.000.000/0000-00"
                        />
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <x-floating-input
                            name="tipo_atividade"
                            label="Atividade Econômica Principal"
                            value="{{ old('tipo_atividade') }}"
                            placeholder="Ex: Hotelaria, Mineração, Agropecuária, Comércio"/>
                    </div>

                    <div class="floating-group emp-form-group emp-col-6">
                        <select name="fase_operacao" id="fase_operacao" class="floating-select emp-select" required>
                            <option value="" disabled {{ old('fase_operacao') ? '' : 'selected' }} hidden>Selecione uma opção...</option>
                            <option value="Localização" {{ old('fase_operacao') === 'Localização' ? 'selected' : '' }}>Localização</option>
                            <option value="Instalação" {{ old('fase_operacao') === 'Instalação' ? 'selected' : '' }}>Instalação</option>
                            <option value="Operação" {{ old('fase_operacao') === 'Operação' ? 'selected' : '' }}>Operação</option>
                            <option value="Não se Aplica" {{ old('fase_operacao') === 'Não se Aplica' ? 'selected' : '' }}>Não se Aplica</option>
                        </select>
                        <label for="fase_operacao" class="floating-label">Fase de Operação</label>
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <x-floating-input
                            name="cep"
                            id="cep"
                            label="CEP"
                            value="{{ old('cep') }}"
                            placeholder="46900-000"/>
                    </div>

                    <div class="emp-form-group emp-col-8">
                        <x-floating-input
                            name="endereco"
                            id="logradouro"
                            label="Endereço / Logradouro"
                            value="{{ old('endereco') }}"
                            placeholder="Rua, Avenida, Rodovia ou Estrada, Nº"/>
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <x-floating-input
                        name="bairro"
                        id="bairro"
                        label="Bairro / Povoado"
                        value="{{ old('bairro') }}"
                        placeholder="Ex: Centro, Povoado Velame"/>
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <x-floating-input
                            name="cidade"
                            id="cidade"
                            label="Município"
                            value="{{ old('cidade', 'Seabra') }}"
                            placeholder="Seabra"/>
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <x-floating-input
                            name="estado"
                            id="uf"
                            label="UF"
                            value="{{ old('estado', 'BA') }}"
                            placeholder="BA"
                            maxlength="2"
                            style="text-transform: uppercase;"/>
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <x-floating-input
                            name="bacia_hidrografica"
                            label="Bacia Hidrográfica"
                            value="{{ old('bacia_hidrografica') }}"
                            placeholder="Ex: Bacia do Rio Paraguaçu"/>
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <x-floating-input
                            name="recurso_hidrico"
                            label="Recurso Hídrico Utilizado / Impactado"
                            value="{{ old('recurso_hidrico') }}"
                            placeholder="Ex: Rio Campestre, Poço Artesiano"/>
                    </div>
                </div>

                <div class="mb-5">
                    <x-radio-options
                        name="unidade_conservacao"
                        label="Atividade situada em Unidade de Conservação ou em sua zona de amortecimento?"
                        target="container_unidade_conservacao"
                        required="nome_unidade_conservacao"
                    >
                        <div
                            id="container_unidade_conservacao"
                            style="display:none;"
                            class="mt-3"
                        >
                            <x-floating-input
                                name="unidade_conservacao"
                                id="nome_unidade_conservacao"
                                label="Nome da Unidade de Conservação"
                            />
                        </div>
                    </x-radio-options>
                </div>

                <div class="mb-5">
                    <x-radio-options
                        name="licenca_anterior"
                        label="O empreendimento possui licença ou autorização anterior?"
                        target="container_licenca_anterior"
                    >
                        <div
                            id="container_licenca_anterior"
                            style="display:none;"
                            class="mt-3"
                        >

                            <label>Especifique a localização:</label>

                            <div class="flex gap-10" style="justify-content: center;">

                                <div>
                                    <input
                                        type="radio"
                                        name="localizacao_licenca"
                                        value="municipio"
                                        id="localizacao_licenca_municipio"
                                    >

                                    <label for="localizacao_licenca_municipio">
                                        No município
                                    </label>
                                </div>

                                <div>
                                    <input
                                    type="radio"
                                    name="localizacao_licenca"
                                    value="estado"
                                    id="localizacao_licenca_estado"
                                >

                                    <label for="localizacao_licenca_estado">
                                        No estado
                                    </label>
                                </div>
                            </div>
                        </div>

                        
                    <div
                        id="container_licenca_anterior_municipio"
                        style="display:none;" class="grid grid-cols-3 gap-4 mt-5"
                    >
                       <div class="col-span-2">
                            <x-floating-input
                                name="licenca_anterior_tipo"
                                id="licenca_anterior_tipo"
                                label="Tipo"
                            />
                        </div>

                        <div class="col-span-1">
                            <x-floating-input
                                name="licenca_anterior_processo"
                                id="licenca_anterior_processo"
                                label="Número do processo"
                            />
                        </div>
                    </div>

                        <div
                            id="container_licenca_anterior_estado"
                            style="display:none;" class="grid grid-cols-2 gap-4 mt-5"
                        >
                            <div>
                                <x-floating-input
                                    name="resolucao_cepram"
                                    id="resolucao_cepram"
                                    label="Número da resolução CEPRAM"
                                />

                                <x-floating-input
                                    name="portaria_inema"
                                    id="portaria_inema"
                                    label="Portaria INEMA"
                                />
                            </div>

                            <div>
                                <x-floating-input
                                    name="resolucao_cepram_validade"
                                    id="resolucao_cepram_validade"
                                    type="date"
                                    label="Validade CEPRAM"
                                />

                                <x-floating-input
                                    name="portaria_inema_validade"
                                    id="portaria_inema_validade"
                                    type="date"
                                    label="Validade INEMA"
                                />
                            </div>
                        </div>
                    </x-radio-options>
                </div>

                <div class="mb-5">
                    <x-radio-options
                        name="processo_semma"
                        label="Existe outro processo referente a este empreendimento em tramitação na SEMMA?"
                        target="container_processo_semma"
                    >
                        <div
                            id="container_processo_semma"
                            style="display:none;"
                            class="mt-3 grid grid-cols-3 gap-4"
                        >
                            <div class="col-span-1">
                                <x-floating-input
                                    name="processo_semma_numero"
                                    id="processo_semma_numero"
                                    label="Número do processo"
                                />
                            </div>

                            <div class="col-span-2">
                                <x-floating-input
                                    name="processo_semma_tipo"
                                    id="processo_semma_tipo"
                                    label="Tipo do processo"
                                />
                            </div>
                        </div>
                    </x-radio-options>
                </div>

                <div>
                    <x-radio-options
                        name="pendencia_ambiental"
                        label="O requerente tem pendência ambiental junto ao INEMA, IBAMA ou na SEMMA, relativa ao empreendimento objeto deste requerimento?"
                        target="container_pendencia_ambiental"
                    >
                        <div
                            id="container_pendencia_ambiental"
                            style="display:none;"
                            class="mt-3 grid grid-cols-2 gap-4"
                        >
                            <div>
                                <x-floating-input
                                    name="notificacao_pendencia"
                                    id="notificacao_pendencia"
                                    label="Número da notificação"
                                />
                            </div>

                            <div>
                                <x-floating-input
                                    name="embargo_pendencia"
                                    id="embargo_pendencia"
                                    label="Número do embargo"
                                />
                            </div>

                            <div>
                                <x-floating-input
                                    name="advertencia_pendencia"
                                    id="advertencia_pendencia"
                                    label="Número da advertência"
                                />
                            </div>

                            <div>
                                <x-floating-input
                                    name="apreensao_pendencia"
                                    id="apreensao_pendencia"
                                    label="Número da apreensão"
                                />
                            </div>

                            <div>
                                <x-floating-input
                                    name="multa_pendencia"
                                    id="multa_pendencia"
                                    label="Número da multa"
                                />
                            </div>

                            <div>
                                <x-floating-input
                                    name="interdicao_pendencia"
                                    id="interdicao_pendencia"
                                    label="Número da interdição"
                                />
                            </div>
                        </div>
                    </x-radio-options>
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
                        <x-floating-input
                            name="contato_nome"
                            label="Nome do Contato / Representante"
                            value="{{ old('contato_nome', auth()->user()->nome) }}"
                            placeholder="Nome do responsável"/>
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <x-floating-input
                            name="contato_email"
                            label="E-mail de Contato"
                            value="{{ old('contato_email', auth()->user()->email) }}"
                            placeholder="contato@empresa.com"/>
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <x-floating-input
                            name="contato_celular"
                            label="Celular / WhatsApp"
                            value="{{ old('contato_celular', auth()->user()->celular) }}"
                            placeholder="(75) 99999-9999"/>
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
                <a href="{{ request('retorno') === 'requerimento' ? route('requerimentos.cidadao.novo') : route('empreendimentos.index') }}" class="emp-btn-cancel">
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

    // Pertencimento à unidade de conservação ou zona de amortecimento;
    const radios = document.querySelectorAll('input[name="unidade_conservacao"]');
    const container = document.getElementById('container_unidade_conservacao');
    const campo = document.getElementById('nome_unidade_conservacao');

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'sim') {
                container.style.display = 'block';
                campo.setAttribute('required', 'required');
            } else {
                container.style.display = 'none';
                campo.removeAttribute('required');
            }
        });
    });

    // Licença ou autorização anterior;
    const radioLicencaAnterior = document.querySelectorAll('input[name="licenca_anterior"]');
    const containerLicencaAnterior = document.getElementById('container_licenca_anterior');
    const campoLicencaAnterior = document.getElementById('licenca_autorizacao_anterior');

    radioLicencaAnterior.forEach(opcao => {
        opcao.addEventListener('change', function () {
            if (this.value === 'sim') {
                containerLicencaAnterior.style.display = 'block';
                campoLicencaAnterior.setAttribute('required', 'required');
            } else {
                containerLicencaAnterior.style.display = 'none';
                campoLicencaAnterior.removeAttribute('required');
            }
        });
    });

    // Licença ou autorização anterior: município;
    const radioLicenca = document.querySelectorAll('input[name="localizacao_licenca"]');
    const containerLicencaMunicipio = document.getElementById('container_licenca_anterior_municipio');
    const campoLicencaTipo = document.getElementById('licenca_anterior_tipo');
    const campoLicencaProcesso = document.getElementById('licenca_anterior_processo');

    radioLicenca.forEach(opcaoLocal => {
        opcaoLocal.addEventListener('change', function () {
            if (this.value === 'municipio') {
                containerLicencaMunicipio.style.display = 'grid';
                campoLicencaTipo.setAttribute('required', 'required');
                campoLicencaProcesso.setAttribute('required', 'required');
            } else {
                containerLicencaMunicipio.style.display = 'none';
                campoLicencaTipo.removeAttribute('required');
                campoLicencaProcesso.removeAttribute('required');
            }
        });
    });

    // Licença ou autorização anterior: estado;
    const containerLicencaEstado = document.getElementById('container_licenca_anterior_estado');
    const campoResolucaoCepram = document.getElementById('resolucao_cepram');
    const campoValidadeResolucaoCepram = document.getElementById('resolucao_cepram_validade');
    const campoPortariaInema = document.getElementById('portaria_inema');
    const campoValidadePortariaInema = document.getElementById('portaria_inema_validade');

    radioLicenca.forEach(opcaoLocal => {
        opcaoLocal.addEventListener('change', function () {
            if (this.value === 'estado') {
                containerLicencaEstado.style.display = 'grid';
                campoResolucaoCepram.setAttribute('required', 'required');
                campoValidadeResolucaoCepram.setAttribute('required', 'required');
                campoPortariaInema.setAttribute('required', 'required');
                campoValidadePortariaInema.setAttribute('required', 'required');
            } else {
                containerLicencaEstado.style.display = 'none';
                campoResolucaoCepram.removeAttribute('required');
                campoValidadeResolucaoCepram.removeAttribute('required');
                campoPortariaInema.removeAttribute('required');
                campoValidadePortariaInema.removeAttribute('required');
            }
        });
    });

    // Processo em tramitação na SEMMA;
    const containerProcessoSemma = document.getElementById('container_processo_semma');
    const campoNumeroProcessoSemma = document.getElementById('processo_semma_numero');
    const campoTipoProcessoSemma = document.getElementById('processo_semma_tipo');

    const radioProcessoSemma = document.querySelectorAll(
        'input[name="processo_semma"]'
    );

    radioProcessoSemma.forEach(opcaoLocal => {
        opcaoLocal.addEventListener('change', function () {
            if (this.value === 'sim') {
                containerProcessoSemma.style.display = 'grid';
                campoNumeroProcessoSemma.setAttribute('required', 'required');
                campoTipoProcessoSemma.setAttribute('required', 'required');
            } else {
                containerProcessoSemma.style.display = 'none';
                campoNumeroProcessoSemma.removeAttribute('required');
                campoTipoProcessoSemma.removeAttribute('required');
            }
        });
    });

    // Pendência ambiental;
    const containerPendenciaAmbiental = document.getElementById('container_pendencia_ambiental');
    const campoNotificacaoPendencia = document.getElementById('notificacao_pendencia');
    const campoAdvertenciaPendencia = document.getElementById('advertencia_pendencia');

    const campoMultaPendencia = document.getElementById('multa_pendencia');
    const campoEmbargoPendencia = document.getElementById('embargo_pendencia');
    const campoApreensaoPendencia = document.getElementById('apreensao_pendencia');
    const campoInterdicaoPendencia = document.getElementById('interdicao_pendencia');

    const radioPendenciaAmbiental = document.querySelectorAll(
        'input[name="pendencia_ambiental"]'
    );

    radioPendenciaAmbiental.forEach(opcaoLocal => {
        opcaoLocal.addEventListener('change', function () {
            if (this.value === 'sim') {
                containerPendenciaAmbiental.style.display = 'grid';
                campoNotificacaoPendencia.setAttribute('required', 'required');
                campoAdvertenciaPendencia.setAttribute('required', 'required');
                campoMultaPendencia.setAttribute('required', 'required');
                campoEmbargoPendencia.setAttribute('required', 'required');
                campoApreensaoPendencia.setAttribute('required', 'required');
                campoInterdicaoPendencia.setAttribute('required', 'required');
            } else {
                containerPendenciaAmbiental.style.display = 'none';
                campoNotificacaoPendencia.removeAttribute('required');
                campoAdvertenciaPendencia.removeAttribute('required');
                campoMultaPendencia.removeAttribute('required');
                campoEmbargoPendencia.removeAttribute('required');
                campoApreensaoPendencia.removeAttribute('required');
                campoInterdicaoPendencia.removeAttribute('required');

            }
        });
    });
</script>
<script src="{{ asset('js/endereco.js') }}"></script>
@endsection
