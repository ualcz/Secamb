<?php

namespace App\Http\Controllers;

use App\Models\Requerimento;
use App\Models\Setor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminConsultaController extends Controller
{
    public function index(Request $request)
    {
        $query = Requerimento::with(['usuario', 'assunto.setor']);

        // 1. Filtro por Aluno (Nome ou Matrícula)
        if ($request->filled('aluno')) {
            $aluno = trim($request->input('aluno'));
            $query->whereHas('usuario', function ($q) use ($aluno) {
                $q->where('nome', 'LIKE', "%{$aluno}%")
                  ->orWhere('matricula', 'LIKE', "%{$aluno}%");
            });
        }

        // 2. Filtro por Tipo de Pessoa (Pessoa Física ou Pessoa Jurídica)
        $tipoPessoa = $request->input('turma') ?: $request->input('tipo_pessoa');
        if (!empty($tipoPessoa)) {
            $query->whereHas('usuario', function ($q) use ($tipoPessoa) {
                $q->where('tipo_registro', $tipoPessoa);
            });
        }

        // 3. Filtro por Setor
        if ($request->filled('setor')) {
            $setorFiltro = $request->input('setor');
            $query->where(function ($q) use ($setorFiltro) {
                $q->whereHas('assunto', function ($aq) use ($setorFiltro) {
                    $aq->where('setor_id', $setorFiltro);
                })
                ->orWhereIn('objetoDoRequerimento', function ($sub) use ($setorFiltro) {
                    $sub->select('descricao')
                        ->from('assuntos_requerimentos')
                        ->where('setor_id', $setorFiltro);
                });
            });
        }

        // 4. Filtro por Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 5. Filtro por intervalo de datas, somente quando informado
        $hasDataInicio = $request->filled('data_inicio');
        $hasDataFim = $request->filled('data_fim');

        if ($hasDataInicio || $hasDataFim) {
            if ($hasDataInicio) {
                $query->whereDate('created_at', '>=', $request->input('data_inicio'));
            }
            if ($hasDataFim) {
                $query->whereDate('created_at', '<=', $request->input('data_fim'));
            }
        }

        $metricQuery = clone $query;
        $totalEncontrados = (clone $metricQuery)->count();
        $totalAtivos = (clone $metricQuery)->whereNotIn('status', Requerimento::STATUS_TERMINAIS)->count();
        $totalConcluidos = (clone $metricQuery)->whereIn('status', Requerimento::STATUS_TERMINAIS)->count();
        $totalComplementacao = (clone $metricQuery)
            ->where('status', Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)
            ->count();
        $complementacoesVencidas = (clone $metricQuery)
            ->where('status', Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)
            ->where('data_devolucao', '<=', now()->subDays(Requerimento::DIAS_LIMITE_COMPLEMENTACAO))
            ->count();

        $processosAtivos = (clone $metricQuery)
            ->whereNotIn('status', Requerimento::STATUS_TERMINAIS)
            ->get(['created_at']);
        $idadeMediaAtivos = $processosAtivos->isEmpty()
            ? 0
            : (int) round($processosAtivos->avg(
                fn ($processo) => $processo->created_at
                    ? Carbon::parse($processo->created_at)->diffInDays(now())
                    : 0
            ));

        $requerimentos = $query->latest()->paginate(15)->withQueryString();
        $setores = Setor::where('ativo', true)->orderBy('setor_sigla')->get();
        $statusBanco = Requerimento::query()
            ->whereNotNull('status')
            ->distinct()
            ->pluck('status')
            ->filter()
            ->values();
        $statusPresentes = $statusBanco->all();
        $statusDisponiveis = collect(Requerimento::STATUS_DISPONIVEIS)
            ->filter(fn ($status) => in_array($status, $statusPresentes, true))
            ->merge($statusBanco->reject(
                fn ($status) => in_array($status, Requerimento::STATUS_DISPONIVEIS, true)
            ))
            ->values();

        return view('admin.consultaRequerimento', [
            'requerimentos' => $requerimentos,
            'setores'       => $setores,
            'totalEncontrados' => $totalEncontrados,
            'totalAtivos' => $totalAtivos,
            'totalConcluidos' => $totalConcluidos,
            'totalComplementacao' => $totalComplementacao,
            'complementacoesVencidas' => $complementacoesVencidas,
            'idadeMediaAtivos' => $idadeMediaAtivos,
            'statusFiltro' => $statusDisponiveis,
        ]);
    }
}
