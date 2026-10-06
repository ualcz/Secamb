<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Requerimento - {{ $nomeRequerente->nome }}</title>
    <style>
        {!! file_get_contents(public_path('css/comprovante-pdf.css')) !!}
    </style>
</head>
<body>
    {{-- ====== CABEÃ‡ALHO INSTITUCIONAL ====== --}}
    <table style="width: 100%;" class="cabecalho">
        <tr>
            <td>
                <img src="{{ public_path('img/logoVertical.png') }}" alt="Logo IFBA" style="width: 60px;">
            </td>
            <td>
                <strong>Instituto Federal de EducaÃ§Ã£o, CiÃªncia e Tecnologia da Bahia</strong><br>
                Campus Seabra | CÃ³digo INEP: 29448743<br>
                Estrada Vicinal para Tenda, Barro Vermelho, CEP 46.900-000, Seabra - BA
            </td>
        </tr>
    </table>

    <p class="titulo"><strong>COMPROVANTE DE PROTOCOLO DE REQUERIMENTO - {{ date('Y') }}</strong></p>
    <div class="protocolo">
        <p><strong>Data da SolicitaÃ§Ã£o:</strong> 
            {{ $dataSolicitacao ? $dataSolicitacao->format('d/m/Y H:i') : date('d/m/Y') }}
        </p>
        <p><strong>NÃºmero do protocolo:</strong> {{ $numeroProtocolo }}</p>
    <div>
    <hr>

    <h4>INFORMAÃ‡Ã•ES ADICIONAIS</h4>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th><strong>Nome do requerente:</strong></th>
            <td>{{ $nomeRequerente->nome }}</td>
        </tr>
        <tr>
            <th><strong>NÃºmero da turma:</strong></th>
            <td>{{ $nomeRequerente->tipo_processo_formatado ?? 'NÃ£o informado' }}</td>
        </tr>
        <tr>
            <th><strong>Objeto do requerimento:</strong></th>
            <td>{{ $objeto ?? 'NÃ£o informado' }}</td>
        </tr>
        <tr>
            <th><strong>Setor:</strong></th>
            <td>{{ $setor ?? 'NÃ£o informado' }}</td>
        </tr>
    </table>
    <hr>
    <p><small>Documento gerado automaticamente pelo SDP - IFBA Campus Seabra | {{ date('d/m/Y H:i:s') }}</small></p>
</body>
</html>

