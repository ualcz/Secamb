<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #065f46; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 1.25rem; }
        .header p { margin: 4px 0 0; font-size: .875rem; opacity: .85; }
        .body { padding: 32px; color: #374151; }
        .body p { line-height: 1.6; margin: 0 0 16px; }
        .protocolo { background: #ecfdf5; border-left: 4px solid #059669; padding: 14px 20px; border-radius: 4px; margin: 20px 0; }
        .protocolo strong { color: #065f46; font-size: 1.1rem; }
        .info-row { display: flex; gap: 8px; margin-bottom: 8px; }
        .info-label { font-weight: bold; min-width: 120px; color: #6b7280; font-size: .875rem; }
        .info-value { color: #111827; font-size: .875rem; }
        .footer { background: #f9fafb; padding: 20px 32px; font-size: .8rem; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Requerimento Recebido nº{{ $requerimento->numero_protocolo }}</h1>
            <p>SECAMB — Sistema de Gestão Ambiental · Seabra</p>
        </div>
        <div class="body">
            <p>Prezado(a) <strong>{{ $setor->setor_nome }}</strong>,</p>
            <p>Um novo requerimento foi protocolado no sistema e está aguardando análise do setor.</p>

            <div class="protocolo">
                <strong>Protocolo nº {{ $requerimento->numero_protocolo }}</strong>
            </div>

            <div class="info-row">
                <span class="info-label">Cidadão:</span>
                <span class="info-value">{{ $cidadao->nome }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">CPF/CNPJ:</span>
                <span class="info-value">{{ $cidadao->cpf ?? $cidadao->cnpj ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Objeto:</span>
                <span class="info-value">{{ $objeto }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Data/Hora:</span>
                <span class="info-value">{{ $requerimento->created_at->format('d/m/Y \à\s H:i') }}</span>
            </div>

            <p style="margin-top: 24px;">Acesse o sistema para visualizar os documentos anexados e dar continuidade à análise.</p>
        </div>
        <div class="footer">
            Este é um e-mail automático. Por favor, não responda a esta mensagem.<br>
            SECAMB — Prefeitura Municipal de Seabra
        </div>
    </div>
</body>
</html>
