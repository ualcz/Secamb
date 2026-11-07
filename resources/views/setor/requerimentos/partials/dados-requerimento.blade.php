<div class="card-painel">
    <div class="card-header-flex">
        <div>
            <span class="info-label">Nº Protocolo</span>
            <h1 class="card-titulo" style="font-size: 1.5rem; color: #2563eb;">
                {{ $requerimento->numero_protocolo }}
            </h1>
        </div>
        <div>
            @php
                $statusClass = match($requerimento->status) {
                    'Aberto' => 'badge-Aberto',
                    'Em Análise', 'Em Analise' => 'badge-analise',
                    'Concluído', 'Concluido' => 'badge-concluido',
                    'Indeferido' => 'badge-indeferido',
                    'Despacho' => 'badge-despacho',
                    default => 'badge-analise'
                };
            @endphp
            <span class="badge {{ $statusClass }}">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                </svg>
                {{ $requerimento->status }}
            </span>
        </div>
    </div>

    <div class="grid-2">
        <div class="info-grupo">
            <span class="info-label">Objeto do Requerimento</span>
            <span class="info-valor">{{ $requerimento->objetoDoRequerimento }}</span>
        </div>
        <div class="info-grupo">
            <span class="info-label">Data e Hora de Envio</span>
            <span class="info-valor">{{ $requerimento->created_at->format('d/m/Y às H:i') }}</span>
        </div>
    </div>

    <div class="info-grupo" style="margin-top: 0.75rem;">
        <span class="info-label">Descrição da Solicitação</span>
        <div class="box-motivo">
            <span class="info-valor">{{ $requerimento->motivo ?: 'Sem observações adicionais informadas.' }}</span>
        </div>
    </div>

    @if($requerimento->empreendimento)
        <div style="margin-top: 1rem; padding: 14px 18px; border-radius: 8px; border: 1px solid #bbf7d0; border-left: 4px solid #059669; background-color: #f0fdf4;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <strong style="color: #065f46; font-size: 0.95rem;">Empreendimento Vinculado</strong>
                </div>
                @if($requerimento->empreendimento->porte)
                    <span style="background: #059669; color: #fff; font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 4px;">
                        Porte: {{ $requerimento->empreendimento->porte }}
                    </span>
                @endif
            </div>

            <div class="grid-2">
                <div class="info-grupo">
                    <span class="info-label">Nome / Razão Social</span>
                    <span class="info-valor" style="font-weight: 600; color: #0f172a;">{{ $requerimento->empreendimento->nome }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">CNPJ</span>
                    <span class="info-valor">{{ $requerimento->empreendimento->cnpj ?? 'Não informado' }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Atividade / Ramo</span>
                    <span class="info-valor">{{ $requerimento->empreendimento->tipo_atividade ?? 'Não informada' }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Cód. Atividade / CNAE</span>
                    <span class="info-valor">{{ $requerimento->empreendimento->cnae ?? 'Não informado' }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Fase Atual</span>
                    <span class="info-valor">{{ $requerimento->empreendimento->fase_operacao ?? 'Não informada' }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Localização</span>
                    <span class="info-valor">{{ $requerimento->empreendimento->endereco_completo ?: 'Seabra - BA' }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Bacia Hidrográfica</span>
                    <span class="info-valor">{{ $requerimento->empreendimento->bacia_hidrografica ?? 'Não informada' }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Recurso Hídrico</span>
                    <span class="info-valor">{{ $requerimento->empreendimento->recurso_hidrico ?? 'Não informado' }}</span>
                </div>
                @if($requerimento->empreendimento->latitude || $requerimento->empreendimento->longitude)
                <div class="info-grupo">
                    <span class="info-label">Coordenadas (SIRGAS 2000)</span>
                    <span class="info-valor">
                        Lat: {{ $requerimento->empreendimento->latitude ?? '—' }} | Long: {{ $requerimento->empreendimento->longitude ?? '—' }}
                    </span>
                </div>
                @endif
                <div class="info-grupo">
                    <span class="info-label">Unidade de Conservação</span>
                    <span class="info-valor">{{ $requerimento->empreendimento->unidade_conservacao ?? 'Não possui' }}</span>
                </div>
            </div>

            {{-- Licenças anteriores e processos SEMMA --}}
            @if($requerimento->empreendimento->licenca_anterior_tipo || $requerimento->empreendimento->resolucao_cepram || $requerimento->empreendimento->processo_semma_numero)
            <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #a7f3d0; font-size: 0.825rem; color: #065f46;">
                <strong>Histórico Ambiental:</strong>
                <ul style="margin: 4px 0 0 16px; padding: 0;">
                    @if($requerimento->empreendimento->licenca_anterior_tipo)
                        <li>Licença Municipal Anterior: {{ $requerimento->empreendimento->licenca_anterior_tipo }} (Proc. {{ $requerimento->empreendimento->licenca_anterior_processo ?? '—' }})</li>
                    @endif
                    @if($requerimento->empreendimento->resolucao_cepram || $requerimento->empreendimento->portaria_inema)
                        <li>Licença Estadual: CEPRAM nº {{ $requerimento->empreendimento->resolucao_cepram ?? '—' }} | INEMA: {{ $requerimento->empreendimento->portaria_inema ?? '—' }}</li>
                    @endif
                    @if($requerimento->empreendimento->processo_semma_numero)
                        <li>Processo em tramitação na SEMMA: Nº {{ $requerimento->empreendimento->processo_semma_numero }} ({{ $requerimento->empreendimento->processo_semma_tipo ?? '—' }})</li>
                    @endif
                </ul>
            </div>
            @endif

            {{-- Pendências declaradas --}}
            @php
                $pendencias = array_filter([
                    $requerimento->empreendimento->notificacao_pendencia ? 'Notificação: ' . $requerimento->empreendimento->notificacao_pendencia : null,
                    $requerimento->empreendimento->embargo_pendencia ? 'Embargo: ' . $requerimento->empreendimento->embargo_pendencia : null,
                    $requerimento->empreendimento->multa_pendencia ? 'Multa: ' . $requerimento->empreendimento->multa_pendencia : null,
                    $requerimento->empreendimento->advertencia_pendencia ? 'Advertência: ' . $requerimento->empreendimento->advertencia_pendencia : null,
                    $requerimento->empreendimento->apreensao_pendencia ? 'Apreensão: ' . $requerimento->empreendimento->apreensao_pendencia : null,
                    $requerimento->empreendimento->interdicao_pendencia ? 'Interdição: ' . $requerimento->empreendimento->interdicao_pendencia : null,
                ]);
            @endphp
            @if(count($pendencias) > 0)
            <div style="margin-top: 8px; padding: 6px 10px; border-radius: 6px; background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 0.8rem;">
                <strong>⚠️ Pendências Declaradas:</strong> {{ implode(', ', $pendencias) }}
            </div>
            @endif
        </div>
    @endif

    @if(session('success'))
        <div style="background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 6px; margin-top: 16px; display: flex; align-items: center; gap: 8px; font-size: 0.875rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-top: 16px; font-size: 0.875rem;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
