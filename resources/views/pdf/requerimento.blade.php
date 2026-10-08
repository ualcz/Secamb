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
                <col style="width:40%">
                <col style="width:20%">
                <col style="width:20%">
                <col style="width:20%">
            </colgroup>
            <tr>
                <td colspan="2">
                    <span class="rotulo">Nome Completo / Razão Social</span>
                    <span class="valor">{{ $aluno->nome }}</span>
                </td>
                <td>
                    <span class="rotulo">CPF / CNPJ</span>
                    <span class="valor">{{ $aluno->cpf ?? $aluno->cnpj ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">RG / Inscrição Estadual</span>
                    <span class="valor">{{ $aluno->rg ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="rotulo">Endereço (Logradouro / Número)</span>
                    <span class="valor">{{ $endereco?->rua ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Bairro</span>
                    <span class="valor">{{ $endereco?->bairro ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">CEP</span>
                    <span class="valor">{{ $endereco?->cep ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="rotulo">Município / UF</span>
                    <span class="valor">{{ $cidadeUf ?: 'Seabra - BA' }}</span>
                </td>
                <td>
                    <span class="rotulo">Telefone / WhatsApp</span>
                    <span class="valor">{{ $aluno->celular ?? '—' }}</span>
                </td>
                <td colspan="2">
                    <span class="rotulo">E-mail</span>
                    <span class="valor">{{ $aluno->email }}</span>
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
                <col style="width:50%">
                <col style="width:25%">
                <col style="width:25%">
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
            </tr>
            <tr>
                <td colspan="2">
                    <span class="rotulo">Endereço do Empreendimento</span>
                    <span class="valor">{{ $emp?->endereco ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Bairro</span>
                    <span class="valor">{{ $emp?->bairro ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="rotulo">Tipo de Atividade / Ramo</span>
                    <span class="valor">{{ $emp?->tipo_atividade ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Fase de Operação</span>
                    <span class="valor">{{ $emp?->fase_operacao ?? '—' }}</span>
                </td>
                <td>
                    <span class="rotulo">Bacia Hidrográfica</span>
                    <span class="valor">{{ $emp?->bacia_hidrografica ?? '—' }}</span>
                </td>
            </tr>
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
         4. EXPOSIÇÃO DE MOTIVOS / JUSTIFICATIVA
    ══════════════════════════════════════════════════════ --}}
    <div class="secao">
        <div class="secao-titulo">4. Exposição de Motivos / Justificativa</div>
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
            Declaro, para os devidos fins, que as informações prestadas neste requerimento são verdadeiras e de minha inteira responsabilidade,
            estando ciente de que a prestação de informações falsas poderá acarretar as sanções previstas na
            Lei Federal nº 9.605/1998 (Lei de Crimes Ambientais), no Decreto Estadual nº 14.024/2012 e na Lei Municipal nº 498/2013 de Seabra-BA,
            bem como a nulidade deste ato e a cassação de eventuais licenças concedidas com base nas informações aqui declaradas.
        </div>
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
