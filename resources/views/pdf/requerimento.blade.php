<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Requerimento - {{ $aluno->nome }}</title>
    <style>
        {!! file_get_contents(public_path('css/requerimento-pdf.css')) !!}
    </style>
</head>
<body>
    @php
        $modelos = \App\Models\Setor::obterSetoresFormatados();
        $modeloAtivo = null;
        if (!empty($setorChave)) {
            if (isset($modelos[$setorChave])) {
                $modeloAtivo = $modelos[$setorChave];
            } else {
                foreach ($modelos as $mod) {
                    if (strcasecmp($mod['setor_sigla'] ?? '', $setorChave) === 0 || ($mod['id'] ?? '') == $setorChave) {
                        $modeloAtivo = $mod;
                        break;
                    }
                }
            }
        }
        $modeloAtivo = $modeloAtivo ?: (reset($modelos) ?: []);
        $setorNomeOficial = $modeloAtivo['setor_nome'] ?? $setorNome ?? 'Setor ResponsÃ¡vel';
        $listaObjetos = array_values($modeloAtivo['objetos'] ?? []);
        $colunasObjetos = array_chunk($listaObjetos, (int) ceil(count($listaObjetos) / 2));
        $objSelecionado = trim($objeto ?? '');
        $emailSetor = $modeloAtivo['rodape_contato'] ?? $modeloAtivo['email'] ?? '';
        $endereco = $aluno->endereco;
        $cidadeUf = $endereco?->cidade ? $endereco->cidade . ($endereco->estado ? ' - ' . $endereco->estado : '') : '';
        $logoPath = public_path('img/logoVertical.png');
        $logoIfba = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';
    @endphp

    <table class="topo">
        <tr>
            <td class="cabecalho">
                <img class="logo" src="{{ $logoIfba }}" alt="IFBA">
                <div class="instituto">
                    INSTITUTO FEDERAL DE EDUCAÃ‡ÃƒO, CIÃŠNCIA E TECNOLOGIA DA BAHIA<br>
                    <span class="campus">CAMPUS SEABRA</span><br>
                    <span class="setor">{{ strtoupper($setorNomeOficial) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="titulo">REQUERIMENTO NÂº {{ $numeroProtocolo ?: '________________' }}</div>

    <div class="secao">
        <div class="secao-titulo">IDENTIFICAÃ‡ÃƒO DO REQUERENTE</div>
        <table class="campos">
            <colgroup>
                <col style="width: 25%;">
                <col style="width: 25%;">
                <col style="width: 25%;">
                <col style="width: 25%;">
            </colgroup>
            <tr>
                <td colspan="3"><span class="rotulo">Nome do Requerente</span><span class="valor">{{ $aluno->nome }}</span></td>
                <td><span class="rotulo">NÂº do CPF</span><span class="valor">{{ $aluno->cpf ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="4"><span class="rotulo">NÂº da TURMA</span><span class="valor">{{ $aluno->tipo_processo_formatado ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="2"><span class="rotulo">EndereÃ§o</span><span class="valor">{{ $endereco?->rua ?? '' }}</span></td>
                <td colspan="2"><span class="rotulo">Cidade</span><span class="valor">{{ $cidadeUf }}</span></td>
            </tr>
            <tr>
                <td><span class="rotulo">Bairro</span><span class="valor">{{ $endereco?->bairro ?? '' }}</span></td>
                <td><span class="rotulo">Telefone</span><span class="valor">{{ $aluno->telefone ?? '' }}</span></td>
                <td><span class="rotulo">E-mail</span><span class="valor">{{ $aluno->email }}</span></td>
                <td><span class="rotulo">CEP</span><span class="valor">{{ $endereco?->cep ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="2"><span class="rotulo">Tipo de Licença</span><span class="valor">{{ $aluno->tipo_processo_formatado ?? '' }}</span></td>
                <td><span class="rotulo">Data</span><span class="valor">{{ date('d/m/Y') }}</span></td>
                <td><span class="rotulo">Assinatura</span><span class="valor"></span></td>
            </tr>
        </table>
    </div>

    <div class="secao">
        <div class="secao-titulo">OBJETO DO REQUERIMENTO</div>
        <div class="objeto">
            <table class="objeto-grid">
                <tr>
                    @foreach($colunasObjetos as $coluna)
                        <td>
                            @foreach($coluna as $descricao)
                                @php
                                    $baseDescricao = trim(preg_replace('/\s*\(.*?\).*/', '', $descricao));
                                    $selecionado = $objSelecionado === $descricao || ($baseDescricao !== '' && stripos($objSelecionado, $baseDescricao) !== false);
                                @endphp
                                <div class="check {{ $selecionado ? 'marcado' : '' }}">[{{ $selecionado ? 'X' : ' ' }}] {{ $descricao }}</div>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">EXPOSIÃ‡ÃƒO DE MOTIVOS</div>
        <div class="linhas">
            @if(!empty($mensagem))<div style="font-size: 7pt; margin-bottom: 1mm;">{!! nl2br(e($mensagem)) !!}</div>@endif
            <div class="linha"></div><div class="linha"></div><div class="linha"></div><div class="linha"></div>
        </div>
    </div>

    <table class="pareceres">
        <tr>
            <td><div class="parecer-titulo">Parecer da CoordenaÃ§Ã£o de Curso/COTEP/Dacad</div><div class="parecer-linhas"></div><div class="assinatura">ASS: ____________________ DATA: ____/____/____</div></td>
            <td><div class="parecer-titulo">Parecer da Biblioteca</div><div class="parecer-linhas"></div><div class="assinatura">ASS: ____________________ DATA: ____/____/____</div></td>
        </tr>
    </table>

</body>
</html>

