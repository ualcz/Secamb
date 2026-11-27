@php
    $valoresCaracterizacao = $caracterizacaoEdicao ?? [];
    $valor = fn ($campo) => old($campo, $valoresCaracterizacao[$campo] ?? '');
@endphp

<fieldset class="caracterizacao-fieldset">
    <legend>Caracterização do Empreendimento para esta Solicitação</legend>
    <p class="caracterizacao-ajuda">Registre a situação ambiental atual. Essas informações ficam vinculadas somente a esta solicitação.</p>

    <div class="form-linha caracterizacao-dados-linha">
        <div class="campo">
            <label for="bacia_hidrografica">Bacia hidrográfica</label>
            <input id="bacia_hidrografica" name="bacia_hidrografica" type="text" maxlength="255" value="{{ $valor('bacia_hidrografica') }}">
        </div>
        <div class="campo">
            <label for="recurso_hidrico">Recurso hídrico utilizado / impactado</label>
            <input id="recurso_hidrico" name="recurso_hidrico" type="text" maxlength="255" value="{{ $valor('recurso_hidrico') }}">
        </div>
    </div>

    <div class="form-linha caracterizacao-dados-linha">
        <div class="campo">
            <label for="latitude">Latitude (SIRGAS 2000)</label>
            <input id="latitude" name="latitude" type="text" maxlength="50" value="{{ $valor('latitude') }}">
        </div>
        <div class="campo">
            <label for="longitude">Longitude (SIRGAS 2000)</label>
            <input id="longitude" name="longitude" type="text" maxlength="50" value="{{ $valor('longitude') }}">
        </div>
    </div>

    <div class="campo campo-pergunta">
        <span>Está situado em Unidade de Conservação ou em sua zona de amortecimento?</span>
        @foreach(['sim' => 'Sim', 'nao' => 'Não'] as $opcao => $rotulo)
            <label>
                <input type="radio" name="unidade_conservacao_resposta" value="{{ $opcao }}" required
                    data-conditional-trigger data-step-required
                    {{ $valor('unidade_conservacao_resposta') === $opcao ? 'checked' : '' }}>
                {{ $rotulo }}
            </label>
        @endforeach
    </div>
    <div class="campo grupo-condicional" data-conditional-container="unidade_conservacao_resposta:sim" hidden>
        <label for="unidade_conservacao_nome">Nome da Unidade de Conservação</label>
        <input id="unidade_conservacao_nome" name="unidade_conservacao_nome" type="text" maxlength="255"
            value="{{ $valor('unidade_conservacao_nome') }}" required data-step-required>
    </div>

    <div class="campo campo-pergunta">
        <span>O empreendimento possui licença ou autorização anterior?</span>
        @foreach(['sim' => 'Sim', 'nao' => 'Não'] as $opcao => $rotulo)
            <label>
                <input type="radio" name="licenca_anterior" value="{{ $opcao }}" required
                    data-conditional-trigger data-step-required
                    {{ $valor('licenca_anterior') === $opcao ? 'checked' : '' }}>
                {{ $rotulo }}
            </label>
        @endforeach
    </div>
    <div class="grupo-condicional" data-conditional-container="licenca_anterior:sim" hidden>
        <div class="campo campo-pergunta">
            <span>Onde está localizada?</span>
            @foreach(['municipio' => 'No município', 'estado' => 'No estado'] as $opcao => $rotulo)
                <label>
                    <input type="radio" name="localizacao_licenca" value="{{ $opcao }}" required
                        data-conditional-trigger data-step-required
                        {{ $valor('localizacao_licenca') === $opcao ? 'checked' : '' }}>
                    {{ $rotulo }}
                </label>
            @endforeach
        </div>
        <div class="form-linha caracterizacao-dados-linha grupo-condicional" data-conditional-container="localizacao_licenca:municipio" hidden>
            <div class="campo">
                <label for="licenca_anterior_tipo">Tipo da licença municipal</label>
                <input id="licenca_anterior_tipo" name="licenca_anterior_tipo" type="text" maxlength="255"
                    value="{{ $valor('licenca_anterior_tipo') }}" required data-step-required>
            </div>
            <div class="campo">
                <label for="licenca_anterior_processo">Número do processo</label>
                <input id="licenca_anterior_processo" name="licenca_anterior_processo" type="text" maxlength="255"
                    value="{{ $valor('licenca_anterior_processo') }}" required data-step-required>
            </div>
        </div>
        <div class="form-linha caracterizacao-dados-linha grupo-condicional" data-conditional-container="localizacao_licenca:estado" hidden>
            <div class="campo">
                <label for="resolucao_cepram">Número da resolução CEPRAM</label>
                <input id="resolucao_cepram" name="resolucao_cepram" type="text" maxlength="255"
                    value="{{ $valor('resolucao_cepram') }}" required data-step-required>
            </div>
            <div class="campo">
                <label for="resolucao_cepram_validade">Validade CEPRAM</label>
                <input id="resolucao_cepram_validade" name="resolucao_cepram_validade" type="date"
                    value="{{ $valor('resolucao_cepram_validade') }}" required data-step-required>
            </div>
            <div class="campo">
                <label for="portaria_inema">Portaria INEMA</label>
                <input id="portaria_inema" name="portaria_inema" type="text" maxlength="255"
                    value="{{ $valor('portaria_inema') }}" required data-step-required>
            </div>
            <div class="campo">
                <label for="portaria_inema_validade">Validade INEMA</label>
                <input id="portaria_inema_validade" name="portaria_inema_validade" type="date"
                    value="{{ $valor('portaria_inema_validade') }}" required data-step-required>
            </div>
        </div>
    </div>

    <div class="campo campo-pergunta">
        <span>Existe outro processo referente a este empreendimento em tramitação na SEMMA?</span>
        @foreach(['sim' => 'Sim', 'nao' => 'Não'] as $opcao => $rotulo)
            <label>
                <input type="radio" name="processo_semma" value="{{ $opcao }}" required
                    data-conditional-trigger data-step-required
                    {{ $valor('processo_semma') === $opcao ? 'checked' : '' }}>
                {{ $rotulo }}
            </label>
        @endforeach
    </div>
    <div class="form-linha caracterizacao-dados-linha grupo-condicional" data-conditional-container="processo_semma:sim" hidden>
        <div class="campo">
            <label for="processo_semma_numero">Número do processo</label>
            <input id="processo_semma_numero" name="processo_semma_numero" type="text" maxlength="25"
                value="{{ $valor('processo_semma_numero') }}" required data-step-required>
        </div>
        <div class="campo">
            <label for="processo_semma_tipo">Tipo do processo</label>
            <input id="processo_semma_tipo" name="processo_semma_tipo" type="text" maxlength="255"
                value="{{ $valor('processo_semma_tipo') }}" required data-step-required>
        </div>
    </div>

    <div class="campo campo-pergunta">
        <span>Há pendência ambiental junto ao INEMA, IBAMA ou SEMMA relacionada ao empreendimento?</span>
        @foreach(['sim' => 'Sim', 'nao' => 'Não'] as $opcao => $rotulo)
            <label>
                <input type="radio" name="pendencia_ambiental" value="{{ $opcao }}" required
                    data-conditional-trigger data-step-required
                    {{ $valor('pendencia_ambiental') === $opcao ? 'checked' : '' }}>
                {{ $rotulo }}
            </label>
        @endforeach
    </div>
    <div class="form-linha caracterizacao-dados-linha caracterizacao-pendencias grupo-condicional" data-conditional-container="pendencia_ambiental:sim" hidden>
        @foreach([
            'notificacao_pendencia' => 'Número da notificação',
            'embargo_pendencia' => 'Número do embargo',
            'advertencia_pendencia' => 'Número da advertência',
            'apreensao_pendencia' => 'Número da apreensão',
            'multa_pendencia' => 'Número da multa',
            'interdicao_pendencia' => 'Número da interdição',
        ] as $campo => $rotulo)
            <div class="campo">
                <label for="{{ $campo }}">{{ $rotulo }}</label>
                <input id="{{ $campo }}" name="{{ $campo }}" type="text" maxlength="50" value="{{ $valor($campo) }}">
            </div>
        @endforeach
    </div>
</fieldset>
