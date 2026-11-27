<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #1e40af; color: #fff; padding: 24px 30px; }
        .header h1 { margin: 0; font-size: 1.2rem; }
        .body { padding: 28px 30px; color: #374151; line-height: 1.6; }
        .info { margin: 20px 0; padding: 16px; border: 1px solid #dbeafe; border-radius: 6px; background: #eff6ff; }
        .info p { margin: 0 0 8px; }
        .info p:last-child { margin-bottom: 0; }
        .footer { padding: 18px 30px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: .85rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Vínculo de técnico confirmado</h1>
        </div>
        <div class="body">
            <p>Olá, <strong>{{ $tecnico->nome }}</strong>.</p>
            <p>Você foi autorizado a atuar como técnico neste empreendimento:</p>

            <div class="info">
                <p><strong>Empreendimento:</strong> {{ $empreendimento->nome }}</p>
                @if($empreendimento->cnpj)
                    <p><strong>CNPJ:</strong> {{ $empreendimento->cnpj }}</p>
                @endif
                <p><strong>Vinculado por:</strong> {{ $vinculador->nome }} ({{ $vinculador->email }})</p>
            </div>

            <p>Agora você pode abrir solicitações e acompanhar os processos vinculados a este empreendimento pelo SECAMB.</p>
        </div>
        <div class="footer">
            Esta é uma mensagem automática do SECAMB — Prefeitura Municipal de Seabra.
        </div>
    </div>
</body>
</html>
