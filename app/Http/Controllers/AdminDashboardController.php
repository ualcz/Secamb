<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Requerimento;
use App\Charts\QtdRequerimentosAbertos;
use App\Charts\QtdRequerimentosNaoConcluidos;
use App\Charts\QtdRequerimentosConcluidos;
use App\Charts\QtdRequerimentosEmAndamento;


class AdminDashboardController extends Controller
{
    public function index(Request $request, QtdRequerimentosAbertos $chart, QtdRequerimentosNaoConcluidos $naoConcluidos, QtdRequerimentosConcluidos $concluidos, QtdRequerimentosEmAndamento $andamento){
        $periodo = $request->get('periodo', 'mes');
        $totalRequerimentos = Requerimento::whereYear('created_at', now()->year)->count();
        $totalAnalise = Requerimento::where('status', 'Em Análise')->count();
        if ($periodo === 'semana') {
            $totalRecebidos = Requerimento::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();
        } else { 
            $totalRecebidos = Requerimento::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count();
        }
        return view('admin.dashboard', [
            'chart' => $chart->build(),
            'barChart' => $naoConcluidos->build(),
            'concluidos' => $concluidos->build(),
            'andamento' => $andamento->build(),
            'totalRequerimentos' => $totalRequerimentos,
            'totalAnalise' => $totalAnalise,
            'totalRecebidos' => $totalRecebidos,
            'periodo' => $periodo,
            'notFound' => 'Nenhum registro encontrado.'
        ]);
    }
}
