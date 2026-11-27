<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Requerimento de Licenciamento Ambiental - {{ $aluno->nome }}</title>
    <style>
        {!! file_get_contents(public_path('css/requerimento-pdf.css')) !!}
    </style>
</head>
<body>
    @php
        /* ── Dados do setor ──────────────────────────────────── */
        $modelos = \App\Models\Setor::obterSetoresFormatados();
        $modeloAtivo = null;
        if (!empty($setorChave)) {
            if (isset($modelos[$setorChave])) {
                $modeloAtivo = $modelos[$setorChave];
            } else {
                foreach ($modelos as $mod) {
                    if (strcasecmp($mod['setor_sigla'] ?? '', $setorChave) === 0
                        || ($mod['id'] ?? '') == $setorChave) {
                        $modeloAtivo = $mod;
                        break;
                    }
                }
            }
        }
        $modeloAtivo       = $modeloAtivo ?: (reset($modelos) ?: []);
        $setorNomeOficial  = $modeloAtivo['setor_nome'] ?? $setorNome ?? 'Secretaria de Meio Ambiente';
        $listaObjetos      = array_values($modeloAtivo['objetos'] ?? []);
        $colunasObjetos    = array_chunk($listaObjetos, max(1, (int) ceil(count($listaObjetos) / 2)));
        $objSelecionado    = trim($objeto ?? '');
        $emailSetor        = $modeloAtivo['rodape_contato'] ?? $modeloAtivo['email'] ?? '';

        /* ── Dados do requerente ─────────────────────────────── */
        $endereco   = $aluno->endereco;
        $cidadeUf   = $endereco?->cidade
                        ? $endereco->cidade . ($endereco->estado ? ' - ' . $endereco->estado : '')
                        : 'Seabra - BA';

        /* ── Logo da Prefeitura ──────────────────────────────── */
        $logoPath = public_path('img/logoVertical.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';

        /* ── Dados do empreendimento ─────────────────────────── */
        $emp = $requerimento->empreendimento ?? null;
        $caracterizacao = $requerimento?->caracterizacao_empreendimento;
        if (!$caracterizacao && $emp) {
            $unidadeConservacaoLegada = trim((string) $emp->unidade_conservacao);
            $unidadeConservacaoRespostaLegada = $unidadeConservacaoLegada === ''
                ? null
                : (stripos($unidadeConservacaoLegada, 'não possui') !== false
                    || stripos($unidadeConservacaoLegada, 'nao possui') !== false
                    ? 'nao'
                    : 'sim');
            $pendenciasLegadas = [
                'notificacao_pendencia' => $emp->notificacao_pendencia,
                'embargo_pendencia' => $emp->embargo_pendencia,
                'advertencia_pendencia' => $emp->advertencia_pendencia,
                'apreensao_pendencia' => $emp->apreensao_pendencia,
                'multa_pendencia' => $emp->multa_pendencia,
                'interdicao_pendencia' => $emp->interdicao_pendencia,
            ];
            $temLicencaLegada = $emp->licenca_anterior_tipo
                || $emp->licenca_anterior_processo
                || $emp->resolucao_cepram
                || $emp->portaria_inema;
            $caracterizacao = [
                'bacia_hidrografica' => $emp->bacia_hidrografica,
                'recurso_hidrico' => $emp->recurso_hidrico,
                'latitude' => $emp->latitude,
                'longitude' => $emp->longitude,
                'unidade_conservacao_resposta' => $unidadeConservacaoRespostaLegada,
                'unidade_conservacao_nome' => $emp->unidade_conservacao,
                'licenca_anterior' => $temLicencaLegada ? 'sim' : null,
                'localizacao_licenca' => $emp->localizacao_licenca,
                'licenca_anterior_tipo' => $emp->licenca_anterior_tipo,
                'licenca_anterior_processo' => $emp->licenca_anterior_processo,
                'resolucao_cepram' => $emp->resolucao_cepram,
                'resolucao_cepram_validade' => $emp->resolucao_cepram_validade,
                'portaria_inema' => $emp->portaria_inema,
                'portaria_inema_validade' => $emp->portaria_inema_validade,
                'processo_semma' => $emp->processo_semma_numero || $emp->processo_semma_tipo ? 'sim' : null,
                'processo_semma_numero' => $emp->processo_semma_numero,
                'processo_semma_tipo' => $emp->processo_semma_tipo,
                'pendencia_ambiental' => count(array_filter($pendenciasLegadas)) ? 'sim' : null,
                ...$pendenciasLegadas,
            ];
        }
    @endphp

    {{-- ══════════════════════════════════════════════════════
         CABEÇALHO PREFEITURA
    ══════════════════════════════════════════════════════ --}}
    <table class="topo">
        <tr>
            @if($logoBase64)
            <td class="td-logo">
                <img class="logo" src="{{ $logoBase64 }}" alt="Brasão">
            </td>
            @endif
            <td class="td-titulo">
                <div class="prefeitura-nome">Prefeitura Municipal de Seabra</div>
                <div class="prefeitura-sub">Estado da Bahia</div>
                <div class="prefeitura-setor">{{ $setorNomeOficial }}</div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════════
         TÍTULO & PROTOCOLO
    ══════════════════════════════════════════════════════ --}}
    <div class="titulo-doc">Requerimento de Licenciamento Ambiental</div>
    <div class="fundamento-legal" style="font-size: 6.5pt; text-align: center; color: #444; margin-top: -2mm; margin-bottom: 2mm;">
        <strong>Fundamento Legal:</strong> Resolução CEPRAM nº 4.789/2018, Lei Municipal nº 498/2013 e Decreto Municipal nº 301/2014
    </div>
    <div class="protocolo-linha">
        Protocolo Nº: <strong>{{ $numeroProtocolo ?: '_______________' }}</strong>
        &nbsp;&nbsp;&nbsp;
        Data de Abertura: <strong>{{ date('d/m/Y') }}</strong>
    </div>

    {{-- ══════════════════════════════════════════════════════
         1. IDENTIFICAÇÃO DO REQUERENTE
    ══════════════════════════════════════════════════════ --}}
    <div class="secao">
        <div class="secao-titulo">1. Identificação do Requerente</div>
        <table class="campos">
            <colgroup>
                <col style="width:50%">
                <col style="width:50%">
            </colgroup>
            <tr>
                <td>
                    <span class="rotulo">Nome</span>
                    <span class="valor">{{ $aluno->nome }}</span>
                </td>
                <td>
                    <span class="rotulo">CPF</span>
                    <span class="valor">{{ $aluno->cpf ?? '—' }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════
         2. DADOS DO EMPREENDIMENTO
    ══════════════════════════════════════════════════════ --}}
    <div class="secao">
        <div class="secao-titulo">2. Dados do Empreendimento / Atividade</div>
        <table class="campos">
            <colgroup>
                <col style="width:40%">
                <col style="width:20%">
                <col style="width:20%">
                <col style="width:20%">
            </colgroup>
            <tr>
                <td colspan="2">
                    <span class="rotulo">Nome / Razão Social do Empreendimento</span>
                    <span class="valor">{{ $emp?->nome ?? '(Requerimento em nome próprio / Pessoa Física)' }}</span>
                </td>
                <td>
                    <span class="rotulo">CNPJ</span>
                    <span class="valor">{{ $emp?->cnpj ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Porte do Empreendimento</span>
                    <span class="valor">{{ $emp?->porte ?? 'Não informado' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="rotulo">Tipo de Atividade Econômica</span>
                    <span class="valor">{{ $emp?->tipo_atividade ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Código da Atividade (CNAE / Tipologia)</span>
                    <span class="valor">{{ $emp?->cnae ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Fase Atual</span>
                    <span class="valor">{{ $emp?->fase_operacao ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="rotulo">Endereço do Empreendimento</span>
                    <span class="valor">{{ $emp?->endereco ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Bairro / Município</span>
                    <span class="valor">{{ ($emp?->bairro ? $emp->bairro . ' - ' : '') . ($emp?->cidade ?? 'Seabra') . '/' . ($emp?->estado ?? 'BA') }}</span>
                </td>
                <td>
                    <span class="rotulo">CEP</span>
                    <span class="valor">{{ $emp?->cep ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="rotulo">Bacia Hidrográfica</span>
                    <span class="valor">{{ $caracterizacao['bacia_hidrografica'] ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Recurso Hídrico</span>
                    <span class="valor">{{ $caracterizacao['recurso_hidrico'] ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Latitude (SIRGAS 2000)</span>
                    <span class="valor">{{ $caracterizacao['latitude'] ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Longitude (SIRGAS 2000)</span>
                    <span class="valor">{{ $caracterizacao['longitude'] ?? '—' }}</span>
                </td>
            </tr>
            @if($emp)
            <tr>
                <td colspan="2">
                    <span class="rotulo">Situado em Unidade de Conservação ou Amortecimento?</span>
                    <span class="valor">
                        @if(($caracterizacao['unidade_conservacao_resposta'] ?? null) === 'sim')
                            Sim — {{ $caracterizacao['unidade_conservacao_nome'] ?? 'Unidade não informada' }}
                        @elseif(($caracterizacao['unidade_conservacao_resposta'] ?? null) === 'nao')
                            Não
                        @else
                            Não informado
                        @endif
                    </span>
                </td>
                <td colspan="2">
                    <span class="rotulo">Existe Outro Processo na SEMMA?</span>
                    <span class="valor">
                        @if(($caracterizacao['processo_semma'] ?? null) === 'sim')
                            Sim (Nº {{ $caracterizacao['processo_semma_numero'] ?? '—' }} - Tipo: {{ $caracterizacao['processo_semma_tipo'] ?? '—' }})
                        @elseif(($caracterizacao['processo_semma'] ?? null) === 'nao')
                            Não
                        @else
                            Não informado
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="rotulo">Possui Licença ou Autorização Anterior?</span>
                    <span class="valor">
                        @if(($caracterizacao['licenca_anterior'] ?? null) === 'sim')
                            @if(($caracterizacao['localizacao_licenca'] ?? null) === 'municipio')
                                Município - Tipo: {{ $caracterizacao['licenca_anterior_tipo'] ?? '—' }} (Proc. {{ $caracterizacao['licenca_anterior_processo'] ?? '—' }})
                            @else
                                Estado - CEPRAM: {{ $caracterizacao['resolucao_cepram'] ?? '—' }} | INEMA: {{ $caracterizacao['portaria_inema'] ?? '—' }}
                                @if(!empty($caracterizacao['resolucao_cepram_validade']))
                                    (CEPRAM válido até {{ \Carbon\Carbon::parse($caracterizacao['resolucao_cepram_validade'])->format('d/m/Y') }})
                                @endif
                                @if(!empty($caracterizacao['portaria_inema_validade']))
                                    (INEMA válido até {{ \Carbon\Carbon::parse($caracterizacao['portaria_inema_validade'])->format('d/m/Y') }})
                                @endif
                            @endif
                        @elseif(($caracterizacao['licenca_anterior'] ?? null) === 'nao')
                            Não
                        @else
                            Não informado
                        @endif
                    </span>
                </td>
                <td colspan="2">
                    <span class="rotulo">Pendências Ambientais (INEMA / IBAMA / SEMMA)</span>
                    <span class="valor">
                        @php
                            $pendencias = array_filter([
                                $caracterizacao['notificacao_pendencia'] ? 'Notificação: ' . $caracterizacao['notificacao_pendencia'] : null,
                                $caracterizacao['embargo_pendencia'] ? 'Embargo: ' . $caracterizacao['embargo_pendencia'] : null,
                                $caracterizacao['multa_pendencia'] ? 'Multa: ' . $caracterizacao['multa_pendencia'] : null,
                                $caracterizacao['advertencia_pendencia'] ? 'Advertência: ' . $caracterizacao['advertencia_pendencia'] : null,
                                $caracterizacao['apreensao_pendencia'] ? 'Apreensão: ' . $caracterizacao['apreensao_pendencia'] : null,
                                $caracterizacao['interdicao_pendencia'] ? 'Interdição: ' . $caracterizacao['interdicao_pendencia'] : null,
                            ]);
                        @endphp
                        @if(($caracterizacao['pendencia_ambiental'] ?? null) === 'sim')
                            {{ count($pendencias) > 0 ? implode('; ', $pendencias) : 'Detalhes não informados' }}
                        @elseif(($caracterizacao['pendencia_ambiental'] ?? null) === 'nao')
                            Não
                        @else
                            Não informado
                        @endif
                    </span>
                </td>
            </tr>
            @if($emp->contato_nome || $emp->contato_celular || $emp->contato_email)
            <tr>
                <td colspan="2">
                    <span class="rotulo">Contato do Responsável / Representante</span>
                    <span class="valor">{{ $emp->contato_nome ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Telefone / Celular</span>
                    <span class="valor">{{ $emp->contato_celular ?? $emp->contato_telefone ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">E-mail de Contato</span>
                    <span class="valor">{{ $emp->contato_email ?? '—' }}</span>
                </td>
            </tr>
            @endif
            @endif
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════
         3. OBJETO DO REQUERIMENTO
    ══════════════════════════════════════════════════════ --}}
    <div class="secao">
        <div class="secao-titulo">3. Objeto do Requerimento (Tipo de Licença Solicitada)</div>
        <div class="objeto">
            @if(count($colunasObjetos) > 0)
            <table class="objeto-grid">
                <tr>
                    @foreach($colunasObjetos as $coluna)
                        <td>
                            @foreach($coluna as $descricao)
                                @php
                                    $baseDescricao = trim(preg_replace('/\s*\(.*?\).*/', '', $descricao));
                                    $selecionado = $objSelecionado === $descricao
                                        || ($baseDescricao !== '' && stripos($objSelecionado, $baseDescricao) !== false);
                                @endphp
                                <div class="check {{ $selecionado ? 'marcado' : '' }}">
                                    <span class="check-indicator">{{ $selecionado ? 'X' : '' }}</span>
                                    {{ $descricao }}
                                </div>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            </table>
            @else
                <div style="font-size:7.5pt;">{{ $objSelecionado ?: '—' }}</div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         4. DESCRIÇÃO DA SOLICITAÇÃO
    ══════════════════════════════════════════════════════ --}}
    <div class="secao">
        <div class="secao-titulo">4. Descrição da Solicitação</div>
        <div class="linhas">
            @if(!empty($mensagem))
                <div style="font-size: 7.5pt; margin-bottom: 1mm;">{!! nl2br(e($mensagem)) !!}</div>
            @endif
            <div class="linha"></div>
            <div class="linha"></div>
            <div class="linha"></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         5. DECLARAÇÃO DO REQUERENTE
    ══════════════════════════════════════════════════════ --}}
    <div class="secao">
        <div class="secao-titulo">5. Declaração</div>
        <div class="declaracao">
            {{ \App\Models\Requerimento::DECLARACAO_RESPONSABILIDADE }}
        </div>
        @if($declaracaoAceita)
            <div class="declaracao-aceite-registro">
                Aceite eletrônico registrado no protocolo
                <strong>{{ $numeroProtocolo }}</strong>
                em <strong>{{ $dataAceite?->format('d/m/Y H:i') ?? date('d/m/Y H:i') }}</strong>.
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════
         RODAPÉ
    ══════════════════════════════════════════════════════ --}}
    <div class="rodape">
        Prefeitura Municipal de Seabra – Estado da Bahia &nbsp;|&nbsp; {{ $setorNomeOficial }}
        @if($emailSetor) &nbsp;|&nbsp; {{ $emailSetor }} @endif
        &nbsp;|&nbsp; Protocolo Nº {{ $numeroProtocolo ?: '_______________' }} &nbsp;|&nbsp; Emitido em {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
