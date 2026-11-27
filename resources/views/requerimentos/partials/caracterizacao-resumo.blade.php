@php
    $caracterizacao = $requerimento->caracterizacao_empreendimento;
    $empreendimentoLegado = $requerimento->empreendimento;

    if (!$caracterizacao && $empreendimentoLegado) {
        $unidadeConservacaoLegada = trim((string) $empreendimentoLegado->unidade_conservacao);
        $unidadeConservacaoRespostaLegada = $unidadeConservacaoLegada === ''
            ? null
            : (stripos($unidadeConservacaoLegada, 'não possui') !== false
                || stripos($unidadeConservacaoLegada, 'nao possui') !== false
                ? 'nao'
                : 'sim');
        $pendenciasLegadas = [
            'notificacao_pendencia' => $empreendimentoLegado->notificacao_pendencia,
            'embargo_pendencia' => $empreendimentoLegado->embargo_pendencia,
            'advertencia_pendencia' => $empreendimentoLegado->advertencia_pendencia,
            'apreensao_pendencia' => $empreendimentoLegado->apreensao_pendencia,
            'multa_pendencia' => $empreendimentoLegado->multa_pendencia,
            'interdicao_pendencia' => $empreendimentoLegado->interdicao_pendencia,
        ];
        $temLicencaLegada = $empreendimentoLegado->licenca_anterior_tipo
            || $empreendimentoLegado->licenca_anterior_processo
            || $empreendimentoLegado->resolucao_cepram
            || $empreendimentoLegado->portaria_inema;

        $caracterizacao = [
            'bacia_hidrografica' => $empreendimentoLegado->bacia_hidrografica,
            'recurso_hidrico' => $empreendimentoLegado->recurso_hidrico,
            'latitude' => $empreendimentoLegado->latitude,
            'longitude' => $empreendimentoLegado->longitude,
            'unidade_conservacao_resposta' => $unidadeConservacaoRespostaLegada,
            'unidade_conservacao_nome' => $empreendimentoLegado->unidade_conservacao,
            'licenca_anterior' => $temLicencaLegada ? 'sim' : null,
            'localizacao_licenca' => $empreendimentoLegado->localizacao_licenca,
            'licenca_anterior_tipo' => $empreendimentoLegado->licenca_anterior_tipo,
            'licenca_anterior_processo' => $empreendimentoLegado->licenca_anterior_processo,
            'resolucao_cepram' => $empreendimentoLegado->resolucao_cepram,
            'resolucao_cepram_validade' => $empreendimentoLegado->resolucao_cepram_validade,
            'portaria_inema' => $empreendimentoLegado->portaria_inema,
            'portaria_inema_validade' => $empreendimentoLegado->portaria_inema_validade,
            'processo_semma' => $empreendimentoLegado->processo_semma_numero
                || $empreendimentoLegado->processo_semma_tipo
                ? 'sim'
                : null,
            'processo_semma_numero' => $empreendimentoLegado->processo_semma_numero,
            'processo_semma_tipo' => $empreendimentoLegado->processo_semma_tipo,
            'pendencia_ambiental' => count(array_filter($pendenciasLegadas)) ? 'sim' : null,
            ...$pendenciasLegadas,
        ];
    }

    $rotuloResposta = fn ($valor) => $valor === 'sim' ? 'Sim' : ($valor === 'nao' ? 'Não' : 'Não informado');
    $itensPendencia = array_filter([
        $caracterizacao['notificacao_pendencia'] ?? null ? 'Notificação: ' . $caracterizacao['notificacao_pendencia'] : null,
        $caracterizacao['embargo_pendencia'] ?? null ? 'Embargo: ' . $caracterizacao['embargo_pendencia'] : null,
        $caracterizacao['advertencia_pendencia'] ?? null ? 'Advertência: ' . $caracterizacao['advertencia_pendencia'] : null,
        $caracterizacao['apreensao_pendencia'] ?? null ? 'Apreensão: ' . $caracterizacao['apreensao_pendencia'] : null,
        $caracterizacao['multa_pendencia'] ?? null ? 'Multa: ' . $caracterizacao['multa_pendencia'] : null,
        $caracterizacao['interdicao_pendencia'] ?? null ? 'Interdição: ' . $caracterizacao['interdicao_pendencia'] : null,
    ]);
@endphp

<div class="grid-2 caracterizacao-resumo">
    <div class="info-grupo">
        <span class="info-label">Bacia Hidrográfica</span>
        <span class="info-valor">{{ $caracterizacao['bacia_hidrografica'] ?? 'Não informada' }}</span>
    </div>
    <div class="info-grupo">
        <span class="info-label">Recurso Hídrico</span>
        <span class="info-valor">{{ $caracterizacao['recurso_hidrico'] ?? 'Não informado' }}</span>
    </div>
    <div class="info-grupo">
        <span class="info-label">Coordenadas (SIRGAS 2000)</span>
        <span class="info-valor">Lat: {{ $caracterizacao['latitude'] ?? '—' }} | Long: {{ $caracterizacao['longitude'] ?? '—' }}</span>
    </div>
    <div class="info-grupo">
        <span class="info-label">Unidade de Conservação</span>
        <span class="info-valor">
            {{ $rotuloResposta($caracterizacao['unidade_conservacao_resposta'] ?? null) }}
            @if(($caracterizacao['unidade_conservacao_resposta'] ?? null) === 'sim')
                — {{ $caracterizacao['unidade_conservacao_nome'] ?? 'Nome não informado' }}
            @endif
        </span>
    </div>
    <div class="info-grupo">
        <span class="info-label">Licença ou Autorização Anterior</span>
        <span class="info-valor">
            {{ $rotuloResposta($caracterizacao['licenca_anterior'] ?? null) }}
            @if(($caracterizacao['licenca_anterior'] ?? null) === 'sim')
                @if(($caracterizacao['localizacao_licenca'] ?? null) === 'municipio')
                    — Municipal: {{ $caracterizacao['licenca_anterior_tipo'] ?? 'Tipo não informado' }},
                    processo {{ $caracterizacao['licenca_anterior_processo'] ?? 'não informado' }}
                @elseif(($caracterizacao['localizacao_licenca'] ?? null) === 'estado')
                    — Estadual: CEPRAM {{ $caracterizacao['resolucao_cepram'] ?? '—' }},
                    INEMA {{ $caracterizacao['portaria_inema'] ?? '—' }}
                    @if(!empty($caracterizacao['resolucao_cepram_validade']))
                        (CEPRAM válido até {{ \Carbon\Carbon::parse($caracterizacao['resolucao_cepram_validade'])->format('d/m/Y') }})
                    @endif
                    @if(!empty($caracterizacao['portaria_inema_validade']))
                        (INEMA válido até {{ \Carbon\Carbon::parse($caracterizacao['portaria_inema_validade'])->format('d/m/Y') }})
                    @endif
                @endif
            @endif
        </span>
    </div>
    <div class="info-grupo">
        <span class="info-label">Processo em Tramitação na SEMMA</span>
        <span class="info-valor">
            {{ $rotuloResposta($caracterizacao['processo_semma'] ?? null) }}
            @if(($caracterizacao['processo_semma'] ?? null) === 'sim')
                — Nº {{ $caracterizacao['processo_semma_numero'] ?? '—' }} ({{ $caracterizacao['processo_semma_tipo'] ?? 'tipo não informado' }})
            @endif
        </span>
    </div>
    <div class="info-grupo">
        <span class="info-label">Pendências Ambientais</span>
        <span class="info-valor">
            {{ $rotuloResposta($caracterizacao['pendencia_ambiental'] ?? null) }}
            @if(($caracterizacao['pendencia_ambiental'] ?? null) === 'sim')
                — {{ $itensPendencia ? implode('; ', $itensPendencia) : 'Detalhes não informados' }}
            @endif
        </span>
    </div>
</div>
