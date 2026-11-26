<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Requerimento;
use App\Models\Empreendimento;
use App\Models\Setor;
use App\Charts\QtdRequerimentosAbertos;
use App\Charts\QtdRequerimentosNaoConcluidos;
use App\Charts\QtdRequerimentosConcluidos;
use App\Charts\QtdRequerimentosEmAndamento;
use Carbon\Carbon;


class AdminDashboardController extends Controller
{
    public function index(Request $request, QtdRequerimentosAbertos $chart, QtdRequerimentosNaoConcluidos $naoConcluidos, QtdRequerimentosConcluidos $concluidos, QtdRequerimentosEmAndamento $andamento){
        $periodo = $request->get('periodo', 'mes');
        $periodo = in_array($periodo, ['semana', 'mes', 'ano'], true) ? $periodo : 'mes';

        $inicio = match ($periodo) {
            'semana' => now()->startOfWeek(),
            'ano' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
        $fim = match ($periodo) {
            'semana' => now()->endOfWeek(),
            'ano' => now()->endOfYear(),
            default => now()->endOfMonth(),
        };
        $periodoLabel = match ($periodo) {
            'semana' => 'Esta semana',
            'ano' => 'Este ano',
            default => 'Este mês',
        };

        $totalRequerimentos = Requerimento::count();
        $totalEmpreendimentos = Empreendimento::count();
        $totalSetores = Setor::where('ativo', true)->count();
        $totalAnalise = Requerimento::whereIn('status', Requerimento::STATUS_ANALISE_ATIVA)->count();
        $totalRecebidos = Requerimento::whereBetween('created_at', [$inicio, $fim])->count();
        $totalConcluidos = Requerimento::whereIn('status', Requerimento::STATUS_TERMINAIS)
            ->whereBetween('updated_at', [$inicio, $fim])
            ->count();
        $totalComplementacao = Requerimento::where('status', Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)->count();
        $totalProtocolados = Requerimento::where('status', Requerimento::STATUS_PROTOCOLADO)->count();
        $totalVistoria = Requerimento::whereIn('status', [
            Requerimento::STATUS_AGUARDANDO_VISTORIA,
            Requerimento::STATUS_VISTORIA_CONCLUIDA,
        ])->count();
        $complementacoesVencidas = Requerimento::where('status', Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)
            ->where('data_devolucao', '<=', now()->subDays(Requerimento::DIAS_LIMITE_COMPLEMENTACAO))
            ->count();

        // O status que aparece com mais frequência será representado em forma de porcentagem;
        $statusDominanteData = Requerimento::whereBetween('created_at', [$inicio, $fim])
                                ->select('status',\DB::raw('count(*) as total'))
                                ->groupBy('status')
                                ->orderByDesc('total')
                                ->first();

        $statusDominanteNome = $statusDominanteData ? $statusDominanteData->status : 'Nenhum';
        $statusDominantePorcentagem = 0;

        if ($statusDominanteData && $totalRecebidos > 0){
            $statusDominantePorcentagem = ($statusDominanteData->total / $totalRecebidos) * 100;
            $statusDominantePorcentagem = round($statusDominantePorcentagem, 1);
        }

        $statusDistribuicao = Requerimento::selectRaw('status, count(*) as total')
            ->whereNotNull('status')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'status' => $item->status,
                'total' => (int) $item->total,
            ])
            ->toArray();

        $trendLabels = [];
        $trendRecebidos = [];
        $trendConcluidos = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->copy()->subMonths($i);
            $inicioMes = $mes->copy()->startOfMonth();
            $fimMes = $mes->copy()->endOfMonth();
            $trendLabels[] = $mes->translatedFormat('M');
            $trendRecebidos[] = Requerimento::whereBetween('created_at', [$inicioMes, $fimMes])->count();
            $trendConcluidos[] = Requerimento::whereIn('status', Requerimento::STATUS_TERMINAIS)
                ->whereBetween('updated_at', [$inicioMes, $fimMes])
                ->count();
        }

        $cargaSetores = Setor::where('ativo', true)
            ->withCount(['requerimentos as ativos' => function ($query) {
                $query->whereNotIn('status', Requerimento::STATUS_TERMINAIS);
            }])
            ->orderByDesc('ativos')
            ->take(6)
            ->get()
            ->map(fn ($setor) => [
                'nome' => $setor->setor_sigla ?: $setor->setor_nome,
                'total' => (int) $setor->ativos,
            ])
            ->toArray();

        $processosConcluidos = Requerimento::whereIn('status', Requerimento::STATUS_TERMINAIS)
            ->get(['created_at', 'updated_at']);
        $tempoMedioDias = $processosConcluidos->isEmpty()
            ? 0
            : (int) round($processosConcluidos->avg(
                fn ($processo) => $processo->created_at
                    ? Carbon::parse($processo->created_at)->diffInDays(Carbon::parse($processo->updated_at))
                    : 0
            ));
        $taxaDentroSla = $processosConcluidos->isEmpty()
            ? 0
            : (int) round(($processosConcluidos->filter(
                fn ($processo) => Carbon::parse($processo->created_at)->diffInDays(Carbon::parse($processo->updated_at)) <= 30
            )->count() / $processosConcluidos->count()) * 100);

        $recentes = Requerimento::with(['usuario', 'setor'])
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'chart' => $chart->build(),
            'barChart' => $naoConcluidos->build(),
            'concluidos' => $concluidos->build(),
            'andamento' => $andamento->build(),
            'totalRequerimentos' => $totalRequerimentos,
            'totalEmpreendimentos' => $totalEmpreendimentos,
            'totalSetores' => $totalSetores,
            'totalAnalise' => $totalAnalise,
            'totalRecebidos' => $totalRecebidos,
            'totalConcluidos' => $totalConcluidos,
            'totalComplementacao' => $totalComplementacao,
            'totalProtocolados' => $totalProtocolados,
            'totalVistoria' => $totalVistoria,
            'complementacoesVencidas' => $complementacoesVencidas,
            'tempoMedioDias' => $tempoMedioDias,
            'taxaDentroSla' => $taxaDentroSla,
            'periodo' => $periodo,
            'periodoLabel' => $periodoLabel,
            'statusDominanteNome' => $statusDominanteNome,
            'statusDominantePorcentagem' => $statusDominantePorcentagem,
            'statusDistribuicao' => $statusDistribuicao,
            'trendLabels' => $trendLabels,
            'trendRecebidos' => $trendRecebidos,
            'trendConcluidos' => $trendConcluidos,
            'cargaSetores' => $cargaSetores,
            'recentes' => $recentes,
            'notFound' => 'Nenhum registro encontrado.'
        ]);
    }
}
