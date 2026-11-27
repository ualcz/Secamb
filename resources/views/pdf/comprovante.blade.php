<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Requerimento - {{ $nomeRequerente->nome }}</title>
    <style>
        {!! file_get_contents(resource_path('css/pdf/comprovante.css')) !!}
    </style>
</head>
<body>
    {{-- MARCA D'ÁGUA DA PREFEITURA --}}
    <div class="marca-dagua">
        <img src="{{ public_path('img/logo_prefeitura_seabra.png') }}">
    </div>
    {{-- CONTEÚDO DO DOCUMENTO --}}
    <div class="conteudo-pdf">
    {{-- ====== CABEÇALHO ====== --}}
    <table style="width: 100%;" class="cabecalho">
        <tr>
            <td>
                <img src="{{ public_path('img/logo_prefeitura_seabra.png') }}" alt="Logo da Prefeitura de Seabra" style="width: 60px;">
            </td>
            <td>
                <strong>Prefeitura Municipal de Seabra</strong><br>
                
                Rua da Consolação, n° 113, Bairro da Boa Vista, CEP: 46.900-335 - Seabra-BA
            </td>
        </tr>
    </table>

    <p class="titulo"><strong>COMPROVANTE DE PROTOCOLO DE REQUERIMENTO - {{ date('Y') }}</strong></p>
    <div class="protocolo">
        <p><strong>Data da Solicitação:</strong> 
            {{ $dataSolicitacao ? $dataSolicitacao->format('d/m/Y H:i') : date('d/m/Y') }}
        </p>
        <p><strong>Número do protocolo:</strong> {{ date('Y') }}/{{ $numeroProtocolo }}</p>
    <div>
    <hr>

    <h4>INFORMAÇÕES ADICIONAIS</h4>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th><strong>Nome do requerente:</strong></th>
            <td>{{ $nomeRequerente->nome }}</td>
        </tr>
        <tr>
            <th><strong>Descrição:</strong></th>
            <td>{{ $descricao ?? 'Não informado' }}</td>
        </tr>
        <tr>
            <th><strong>Objeto do requerimento:</strong></th>
            <td>{{ $objeto ?? 'Não informado' }}</td>
        </tr>
        <tr>
            <th><strong>Setor:</strong></th>
            <td>{{ $setor ?? 'Não informado' }}</td>
        </tr>
    </table>
    <hr>
    <p><small>Documento gerado automaticamente pelo SECAMB - Prefeitura Municipal de Seabra | {{ date('d/m/Y H:i:s') }}</small></p>
</div>
</body>
</html>

