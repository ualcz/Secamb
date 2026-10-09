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


class AdminDashboardController extends Controller
{
    public function index(Request $request, QtdRequerimentosAbertos $chart, QtdRequerimentosNaoConcluidos $naoConcluidos, QtdRequerimentosConcluidos $concluidos, QtdRequerimentosEmAndamento $andamento){
        $periodo = $request->get('periodo', 'mes');
        $totalRequerimentos = Requerimento::whereYear('created_at', now()->year)->count();
        $totalEmpreendimentos = Empreendimento::get()->count();
        $totalSetores = Setor::get()->count();
        $totalAnalise = Requerimento::where('status', 'Em Análise')->count();
        if ($periodo === 'semana') {
            $totalRecebidos = Requerimento::whereBetween('created_at', [
                $inicio = now()->startOfWeek(),
                $fim = now()->endOfWeek()
            ])->count();
        } else { 
            $totalRecebidos = Requerimento::whereBetween('created_at', [
                $inicio = now()->startOfMonth(),
                $fim = now()->endOfMonth()
            ])->count();
        }

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
            'periodo' => $periodo,
            'statusDominanteNome' => $statusDominanteNome,
            'statusDominantePorcentagem' => $statusDominantePorcentagem,
            'notFound' => 'Nenhum registro encontrado.'
        ]);
    }
}
