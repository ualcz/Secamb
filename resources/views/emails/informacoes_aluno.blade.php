<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Informações do Requerente</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5; color: #333;">

    <p><strong>Setor Selecionado:</strong> {{ $setorNome }}</p>

    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 600px;">
        <tr style="background-color: #f0f0f0;">
            <th align="left">Campo</th>
            <th align="left">Informação</th>
        </tr>
        <tr>
            <td><strong>Nome / Razão Social</strong></td>
            <td>{{ $aluno->nome }}</td>
        </tr>
        <tr>
            <td><strong>CPF / CNPJ</strong></td>
            <td>{{ $aluno->cpf ?? 'Não informado' }}</td>
        </tr>
        <tr>
            <td><strong>E-mail</strong></td>
            <td>{{ $aluno->email ?? 'Não informado' }}</td>
        </tr>
        <tr>
            <td><strong>Telefone</strong></td>
            <td>{{ $aluno->telefone ?? 'Não informado' }}</td>
        </tr>
    </table>

    <h3>Mensagem / Observações do Requerente:</h3>
    <p style="background-color: #f9f9f9; padding: 10px; border-left: 3px solid #0056b3;">
        {!! nl2br(e($mensagem)) !!}
    </p>

    <p style="font-size: 11px; color: #666;">
        Enviado automaticamente pelo Sistema SECAMB - Prefeitura Municipal de Seabra.
    </p>
</body>
</html>
