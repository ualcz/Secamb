@extends('layouts.app')

@section('title', 'Editar Empreendimento - SECAMB')
@section('tag', 'Empreendimentos')

@section('content')
<link rel="stylesheet" href="{{ asset('css/empreendimentos.css') }}?v={{ filemtime(public_path('css/empreendimentos.css')) }}">

<div class="emp-container">

    {{-- Botão Voltar --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('empreendimentos.index') }}" class="emp-btn-cancel" style="display: inline-flex; align-items: center; gap: 6px;">
            ← Voltar para Empreendimentos
        </a>
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
            <h2>Editar Empreendimento: {{ $empreendimento->nome }}</h2>
            <p>Atualize os dados cadastrais, de localização e de contato do empreendimento.</p>
        </div>

        <form action="{{ route('empreendimentos.update', $empreendimento->id) }}" method="POST">
            @csrf
            @method('PUT')

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
                        <input type="text" name="nome" id="nome" class="emp-input" value="{{ old('nome', $empreendimento->nome) }}" required>
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" name="cnpj" id="cnpj" class="emp-input" value="{{ old('cnpj', $empreendimento->cnpj) }}" placeholder="AA.AAA.AAA/AAAA-00" maxlength="18">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="tipo_atividade">Atividade Econômica Principal</label>
                        <input type="text" name="tipo_atividade" id="tipo_atividade" class="emp-input" value="{{ old('tipo_atividade', $empreendimento->tipo_atividade) }}">
                    </div>

                    <div class="emp-form-group emp-col-3">
                        <label for="cnae">Cód. Atividade / CNAE</label>
                        <input type="text" name="cnae" id="cnae" class="emp-input" value="{{ old('cnae', $empreendimento->cnae) }}" placeholder="Ex: B4.1 ou 0810-0/99">
                    </div>

                    <div class="emp-form-group emp-col-3">
                        <label for="porte">Porte do Empreendimento</label>
                        <select name="porte" id="porte" class="emp-select">
                            <option value="">Selecione o porte...</option>
                            <option value="Micro" {{ old('porte', $empreendimento->porte) === 'Micro' ? 'selected' : '' }}>Micro</option>
                            <option value="Pequeno" {{ old('porte', $empreendimento->porte) === 'Pequeno' ? 'selected' : '' }}>Pequeno</option>
                            <option value="Médio" {{ old('porte', $empreendimento->porte) === 'Médio' ? 'selected' : '' }}>Médio</option>
                            <option value="Grande" {{ old('porte', $empreendimento->porte) === 'Grande' ? 'selected' : '' }}>Grande</option>
                        </select>
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="fase_operacao">Fase de Operação</label>
                        <select name="fase_operacao" id="fase_operacao" class="emp-select">
                            <option value="">Selecione uma opção...</option>
                            <option value="Localização" {{ old('fase_operacao', $empreendimento->fase_operacao) === 'Localização' ? 'selected' : '' }}>Localização</option>
                            <option value="Instalação" {{ old('fase_operacao', $empreendimento->fase_operacao) === 'Instalação' ? 'selected' : '' }}>Instalação</option>
                            <option value="Operação" {{ old('fase_operacao', $empreendimento->fase_operacao) === 'Operação' ? 'selected' : '' }}>Operação</option>
                            <option value="Não se Aplica" {{ old('fase_operacao', $empreendimento->fase_operacao) === 'Não se Aplica' ? 'selected' : '' }}>Não se Aplica</option>
                        </select>
                    </div>

                    <div class="emp-form-group emp-col-8">
                        <label for="endereco">Endereço / Logradouro</label>
                        <input type="text" name="endereco" id="endereco" class="emp-input" value="{{ old('endereco', $empreendimento->endereco) }}">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="bairro">Bairro / Povoado</label>
                        <input type="text" name="bairro" id="bairro" class="emp-input" value="{{ old('bairro', $empreendimento->bairro) }}">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="cep">CEP</label>
                        <input type="text" name="cep" id="cep" class="emp-input" value="{{ old('cep', $empreendimento->cep) }}">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="cidade">Município</label>
                        <input type="text" name="cidade" id="cidade" class="emp-input" value="{{ old('cidade', $empreendimento->cidade ?? 'Seabra') }}">
                    </div>

                    <div class="emp-form-group emp-col-4">
                        <label for="estado">UF</label>
                        <input type="text" name="estado" id="estado" class="emp-input" value="{{ old('estado', $empreendimento->estado ?? 'BA') }}" maxlength="2" style="text-transform: uppercase;">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="bacia_hidrografica">Bacia Hidrográfica</label>
                        <input type="text" name="bacia_hidrografica" id="bacia_hidrografica" class="emp-input" value="{{ old('bacia_hidrografica', $empreendimento->bacia_hidrografica) }}">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="recurso_hidrico">Recurso Hídrico Utilizado / Impactado</label>
                        <input type="text" name="recurso_hidrico" id="recurso_hidrico" class="emp-input" value="{{ old('recurso_hidrico', $empreendimento->recurso_hidrico) }}">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="latitude">Coordenadas: Latitude (SIRGAS 2000)</label>
                        <input type="text" name="latitude" id="latitude" class="emp-input" value="{{ old('latitude', $empreendimento->latitude) }}" placeholder="Ex: -12.418290 ou 12°25'05'' S">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="longitude">Coordenadas: Longitude (SIRGAS 2000)</label>
                        <input type="text" name="longitude" id="longitude" class="emp-input" value="{{ old('longitude', $empreendimento->longitude) }}" placeholder="Ex: -41.769850 ou 41°46'11'' W">
                    </div>
                </div>
                <div class="emp-form-group emp-col-6 mt-2">
                        <label for="unidade_conservacao">Unidade de Conservação ou Zona de Amortecimento</label>
                        <input type="text" name="unidade_conservacao" id="unidade_conservacao" class="emp-input" value="{{ old('unidade_conservacao', $empreendimento->unidade_conservacao) ?? 'Não possui.'}}">
                </div>
                @if ($empreendimento->localizacao_licenca != null)
                    @if ($empreendimento->localizacao_licenca === 'municipio')
                        <div class="emp-form-group emp-col-6 mt-2">
                            <label>Licença localizada no {{$empreendimento->localizacao_licenca}}</label class="text-2x1">
                                <div class="flex gap-5">
                                    <div>
                                        <label for="licenca_anterior_tipo">Tipo da licença</label>
                                        <input type="text" name="licenca_anterior_tipo" id="licenca_anterior_tipo" class="emp-input" value="{{ old('licenca_anterior_tipo', $empreendimento->licenca_anterior_tipo)}}"> 
                                    </div>
                                                                        
                                    <div>
                                        <label for="licenca_anterior_processo">Número do processo da licença</label>
                                        <input type="text" name="licenca_anterior_processo" id="licenca_anterior_processo" class="emp-input" value="{{ old('licenca_anterior_processo', $empreendimento->licenca_anterior_processo)}}">
                                    </div>
                                              
                                    @if ($empreendimento->processo_semma_tipo != null)
                                        <div>
                                            <label for="processo_semma_tipo">Tipo do processo SEMMA</label>
                                            <input type="text" name="processo_semma_tipo" id="processo_semma_tipo" class="emp-input" value="{{ old('processo_semma_tipo', $empreendimento->processo_semma_tipo)}}">
                                        </div>

                                        <div>
                                            <label for="processo_semma_numero">Número do processo SEMMA</label>
                                            <input type="text" name="processo_semma_numero" id="processo_semma_numero" class="emp-input" value="{{ old('processo_semma_numero', $empreendimento->processo_semma_numero)}}">
                                        </div>
                                    @endif
                                </div>
                        </div>
                    @endif

                    @if ($empreendimento->localizacao_licenca === 'estado')
                        <div class="emp-form-group emp-col-6 mt-2">
                            <label>Licença localizada no {{$empreendimento->localizacao_licenca}}</label class="text-2x1">
                                <div class="flex gap-5">
                                    <div>
                                        <label for="resolucao_cepram">Resolução CEPRAM</label>
                                        <input type="text" name="resolucao_cepram" id="resolucao_cepram" class="emp-input" value="{{ old('resolucao_cepram', $empreendimento->resolucao_cepram)}}">
                                    </div>
                                        
                                    <div>
                                        <label for="resolucao_cepram_validade">Resolução CEPRAM (validade)</label>
                                        <input type="text" name="resolucao_cepram_validade" id="resolucao_cepram_validade" class="emp-input" value="{{ old('resolucao_cepram_validade', \Carbon\Carbon::parse($empreendimento->resolucao_cepram_validade)->format('d/m/Y')) }}">
                                    </div>
                                        
                                    <div>
                                        <label for="portaria_inema">Portaria INEMA</label>
                                        <input type="text" name="portaria_inema" id="portaria_inema" class="emp-input" value="{{ old('portaria_inema', $empreendimento->portaria_inema)}}">
                                    </div>
                                        
                                    <div>
                                        <label for="portaria_inema_validade">Portaria INEMA (validade)</label>
                                        <input type="text" name="portaria_inema_validade" id="portaria_inema_validade" class="emp-input" value="{{ old('portaria_inema_validade', \Carbon\Carbon::parse($empreendimento->portaria_inema_validade)->format('d/m/Y')) }}">
                                    </div>

                                    @if ($empreendimento->processo_semma_tipo != null)
                                        <div>
                                            <label for="processo_semma_tipo">Tipo do processo SEMMA</label>
                                            <input type="text" name="processo_semma_tipo" id="processo_semma_tipo" class="emp-input" value="{{ old('processo_semma_tipo', $empreendimento->processo_semma_tipo)}}">
                                        </div>

                                        <div>
                                            <label for="processo_semma_numero">Processo SEMMA</label>
                                            <input type="text" name="processo_semma_numero" id="processo_semma_numero" class="emp-input" value="{{ old('processo_semma_numero', $empreendimento->processo_semma_numero)}}">
                                        </div>
                                    @endif
                                </div>
                            </div>
                    @endif
                @endif

                @if ($empreendimento->notificacao_pendencia != null)
                    <div class="emp-form-group emp-col-6 mt-5">
                        <label>Pendência ambiental</label>
                            <div class="flex gap-5">
                                <div>
                                    <label for="notificacao_pendencia">Notificação</label>
                                    <input type="text" name="notificacao_pendencia" id="notificacao_pendencia" class="emp-input" value="{{ old('notificacao_pendencia', $empreendimento->notificacao_pendencia)}}">
                                </div>
                                    
                                <div>
                                    <label for="embargo_pendencia">Embargo</label>
                                    <input type="text" name="embargo_pendencia" id="embargo_pendencia" class="emp-input" value="{{ old('embargo_pendencia', $empreendimento->embargo_pendencia)}}">
                                </div>
                                    
                                <div>
                                    <label for="advertencia_pendencia">Advertência</label>
                                    <input type="text" name="advertencia_pendencia" id="advertencia_pendencia" class="emp-input" value="{{ old('advertencia_pendencia', $empreendimento->advertencia_pendencia)}}">
                                </div>
                                    
                                <div>
                                    <label for="apreensao_pendencia">Apreensão</label>
                                    <input type="text" name="apreensao_pendencia" id="apreensao_pendencia" class="emp-input" value="{{ old('apreensao_pendencia', $empreendimento->apreensao_pendencia)}}">
                                </div>
                                    
                                <div>
                                    <label for="multa_pendencia">Multa</label>
                                    <input type="text" name="multa_pendencia" id="multa_pendencia" class="emp-input" value="{{ old('multa_pendencia', $empreendimento->multa_pendencia)}}">
                                </div>
                                    
                                <div>
                                    <label for="interdicao_pendencia">Interdição</label>
                                    <input type="text" name="interdicao_pendencia" id="interdicao_pendencia" class="emp-input" value="{{ old('interdicao_pendencia', $empreendimento->interdicao_pendencia)}}">
                                </div>
                        </div>
                    @endif
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
                        <input type="text" name="contato_nome" id="contato_nome" class="emp-input" value="{{ old('contato_nome', $empreendimento->contato_nome) }}">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_email">E-mail de Contato</label>
                        <input type="email" name="contato_email" id="contato_email" class="emp-input" value="{{ old('contato_email', $empreendimento->contato_email) }}">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_telefone">Telefone Fixo (opcional)</label>
                        <input type="text" name="contato_telefone" id="contato_telefone" class="emp-input" value="{{ old('contato_telefone', $empreendimento->contato_telefone) }}">
                    </div>

                    <div class="emp-form-group emp-col-6">
                        <label for="contato_celular">Celular / WhatsApp (opcional)</label>
                        <input type="text" name="contato_celular" id="contato_celular" class="emp-input" value="{{ old('contato_celular', $empreendimento->contato_celular) }}">
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="emp-form-actions">
                <a href="{{ route('empreendimentos.index') }}" class="emp-btn-cancel">
                    Cancelar
                </a>
                <button type="submit" class="emp-btn-submit">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cnpjInput = document.getElementById('cnpj');
        if (cnpjInput) {
            cnpjInput.addEventListener('input', function (e) {
                let v = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 14);
                v = v.slice(0, 12) + v.slice(12).replace(/\D/g, '').slice(0, 2);
                e.target.value = v.slice(0, 2)
                    + (v.length > 2 ? '.' + v.slice(2, 5) : '')
                    + (v.length > 5 ? '.' + v.slice(5, 8) : '')
                    + (v.length > 8 ? '/' + v.slice(8, 12) : '')
                    + (v.length > 12 ? '-' + v.slice(12, 14) : '');
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
