@extends('layouts.app')

@section('title', 'Dashboard - Secamb')
@section('tag', 'Administração')

@section('content')
    @php($dashboardRoute = auth()->user()->isAdmin() ? 'admin.dashboard' : 'servidor.dashboard')
    <style>
        {!! file_get_contents(public_path('css/dashboard.css')) !!}
    </style>
<div class="dashboard-shell">
    <div class="dashboard-heading">
        <div class="dashboard-period" aria-label="Filtro de período">
            @foreach (['semana' => 'Semana', 'mes' => 'Mês', 'ano' => 'Ano'] as $valor => $rotulo)
                <a href="{{ route($dashboardRoute, ['periodo' => $valor]) }}"
                   class="{{ $periodo === $valor ? 'active' : '' }}">{{ $rotulo }}</a>
            @endforeach
        </div>
    </div>

    <div class="metric-grid">
        <article class="metric-card metric-primary">
            <div>
                <span class="metric-label">Recebidos</span>
                <strong>{{ number_format($totalRecebidos, 0, ',', '.') }}</strong>
                <small>{{ $periodoLabel }}</small>
            </div>
            <span class="metric-icon" aria-hidden="true">↓</span>
        </article>
        <article class="metric-card">
            <div>
                <span class="metric-label">Em atendimento</span>
                <strong>{{ number_format($totalAnalise, 0, ',', '.') }}</strong>
                <small>{{ $totalProtocolados }} aguardando entrada</small>
            </div>
            <span class="metric-icon metric-icon-blue" aria-hidden="true">◷</span>
        </article>
        <article class="metric-card">
            <div>
                <span class="metric-label">Concluídos</span>
                <strong>{{ number_format($totalConcluidos, 0, ',', '.') }}</strong>
                <small>{{ $periodoLabel }}</small>
            </div>
            <span class="metric-icon metric-icon-green" aria-hidden="true">✓</span>
        </article>
        <article class="metric-card {{ $complementacoesVencidas > 0 ? 'metric-alert' : '' }}">
            <div>
                <span class="metric-label">Complementação</span>
                <strong>{{ number_format($totalComplementacao, 0, ',', '.') }}</strong>
                <small>{{ $complementacoesVencidas }} vencidas há mais de 120 dias</small>
            </div>
            <span class="metric-icon metric-icon-amber" aria-hidden="true">!</span>
        </article>
    </div>

    <div class="insight-grid">
        <article class="dashboard-panel trend-panel">
            <div class="panel-heading">
                <div>
                    <h2>Movimentação dos processos</h2>
                    <p>Recebidos e concluídos nos últimos 6 meses</p>
                </div>
            </div>
            <div id="dashboard-trend" class="apex-chart"></div>
        </article>

        <article class="dashboard-panel status-panel">
            <div class="panel-heading">
                <div>
                    <h2>Distribuição atual</h2>
                    <p>Todos os processos por situação</p>
                </div>
            </div>
            <div id="dashboard-status" class="apex-chart status-chart"></div>
        </article>
    </div>

    <div class="operations-grid">
        <article class="dashboard-panel">
            <div class="panel-heading">
                <div>
                    <h2>Carga por setor</h2>
                    <p>Processos não concluídos</p>
                </div>
            </div>
            <div id="dashboard-sectors" class="apex-chart sector-chart"></div>
        </article>

        <article class="dashboard-panel">
            <div class="panel-heading">
                <div>
                    <h2>Indicadores de desempenho</h2>
                    <p>Eficiência da tramitação</p>
                </div>
            </div>
            <div class="kpi-list">
                <div class="kpi-row">
                    <div>
                        <span>Tempo médio de conclusão</span>
                        <strong>{{ $tempoMedioDias }} dias</strong>
                    </div>
                    <div class="kpi-progress"><span style="width: {{ min(100, max(8, 100 - ($tempoMedioDias * 2))) }}%"></span></div>
                </div>
                <div class="kpi-row">
                    <div>
                        <span>Concluídos em até 30 dias</span>
                        <strong>{{ $taxaDentroSla }}%</strong>
                    </div>
                    <div class="kpi-progress kpi-progress-green"><span style="width: {{ $taxaDentroSla }}%"></span></div>
                </div>
                <div class="kpi-summary">
                    <div><span>{{ $totalVistoria }}</span><small>Em vistoria</small></div>
                    <div><span>{{ $totalEmpreendimentos }}</span><small>Empreendimentos</small></div>
                    <div><span>{{ $totalSetores }}</span><small>Setores ativos</small></div>
                </div>
            </div>
        </article>
    </div>

    <article class="dashboard-panel recent-panel">
        <div class="panel-heading">
            <div>
                <h2>Últimos requerimentos</h2>
                <p>Entradas mais recentes no sistema</p>
            </div>
            <a href="{{ route('admin.consultar-requerimentos') }}" class="panel-link">Ver todos</a>
        </div>
        <div class="table-scroll">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Protocolo</th>
                        <th>Tipo</th>
                        <th>Setor</th>
                        <th>Status</th>
                        <th>Entrada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentes as $requerimento)
                        <tr>
                            <td><strong>{{ $requerimento->numero_protocolo ?: '#' . $requerimento->id }}</strong></td>
                            <td>{{ $requerimento->tipo_processo_formatado }}</td>
                            <td>{{ $requerimento->setor_nome }}</td>
                            <td><span class="status-pill">{{ $requerimento->status ?: 'Protocolado' }}</span></td>
                            <td>{{ $requerimento->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-row">Nenhum requerimento encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const common = {
        chart: { toolbar: { show: false }, fontFamily: 'inherit' },
        colors: ['#1d4ed8', '#10b981'],
        grid: { borderColor: '#e8edf4', strokeDashArray: 4 },
        tooltip: { theme: 'light' },
    };

    new ApexCharts(document.querySelector('#dashboard-trend'), {
        ...common,
        series: [
            { name: 'Recebidos', data: @json($trendRecebidos) },
            { name: 'Concluídos', data: @json($trendConcluidos) },
        ],
        chart: { ...common.chart, type: 'area', height: 290 },
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.18, opacityTo: 0.03 } },
        dataLabels: { enabled: false },
        xaxis: { categories: @json($trendLabels), labels: { style: { colors: '#64748b' } } },
        yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: '#64748b' } } },
        legend: { position: 'top', horizontalAlign: 'right' },
    }).render();

    new ApexCharts(document.querySelector('#dashboard-status'), {
        series: @json(array_column($statusDistribuicao, 'total')),
        labels: @json(array_column($statusDistribuicao, 'status')),
        chart: { type: 'donut', height: 290, fontFamily: 'inherit' },
        colors: ['#2563eb', '#0ea5e9', '#14b8a6', '#f59e0b', '#8b5cf6', '#ef4444', '#64748b'],
        legend: { position: 'bottom', fontSize: '12px' },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '68%', labels: {
            show: true,
            total: { show: true, label: 'Processos', color: '#64748b', formatter: () => '{{ $totalRequerimentos }}' }
        } } } },
    }).render();

    new ApexCharts(document.querySelector('#dashboard-sectors'), {
        series: [{ name: 'Processos ativos', data: @json(array_column($cargaSetores, 'total')) }],
        chart: { type: 'bar', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
        colors: ['#2563eb'],
        dataLabels: { enabled: true },
        grid: { borderColor: '#e8edf4', strokeDashArray: 4, xaxis: { lines: { show: true } } },
        xaxis: { categories: @json(array_column($cargaSetores, 'nome')), labels: { style: { colors: '#64748b' } } },
        yaxis: { labels: { style: { colors: '#64748b' } } },
    }).render();
});
</script>

@endsection
