<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1e40af; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 1.25rem; }
        .header p { margin: 4px 0 0; font-size: .875rem; opacity: .85; }
        .body { padding: 32px; color: #374151; }
        .body p { line-height: 1.6; margin: 0 0 16px; }
        .protocolo { background: #eff6ff; border-left: 4px solid #2563eb; padding: 14px 20px; border-radius: 4px; margin: 20px 0; }
        .protocolo strong { color: #1e40af; font-size: 1.1rem; }
        .info-row { display: flex; gap: 8px; margin-bottom: 8px; }
        .info-label { font-weight: bold; min-width: 100px; color: #6b7280; font-size: .875rem; }
        .info-value { color: #111827; font-size: .875rem; }
        .footer { background: #f9fafb; padding: 20px 32px; font-size: .8rem; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Requerimento nº{{ $requerimento->numero_protocolo }} Protocolado</h1>
            <p>SECAMB — Sistema de Gestão Ambiental · Seabra</p>
        </div>
        <div class="body">
            <p>Olá, <strong>{{ $aluno->nome }}</strong>!</p>
            <p>Seu requerimento foi registrado com sucesso no sistema SECAMB. Abaixo estão os detalhes do protocolo:</p>

            <div class="protocolo">
                <strong>Protocolo nº {{ $requerimento->numero_protocolo }}</strong>
            </div>

            <div class="info-row">
                <span class="info-label">Objeto:</span>
                <span class="info-value">{{ $objeto }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Setor:</span>
                <span class="info-value">{{ $setorNome }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Data/Hora:</span>
                <span class="info-value">{{ $requerimento->created_at->format('d/m/Y \à\s H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">Aberto — aguardando análise</span>
            </div>

            <p style="margin-top: 24px;">Você pode acompanhar o andamento do seu requerimento acessando o sistema a qualquer momento.</p>
        </div>
        <div class="footer">
            Este é um e-mail automático. Por favor, não responda a esta mensagem.<br>
            SECAMB — Prefeitura Municipal de Seabra
        </div>
    </div>
</body>
</html>
