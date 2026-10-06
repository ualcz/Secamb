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

        // 5. Filtro por Intervalo de Datas com fallback de 30 dias por padrão
        $hasDataInicio = $request->filled('data_inicio');
        $hasDataFim = $request->filled('data_fim');

        if ($hasDataInicio || $hasDataFim) {
            if ($hasDataInicio) {
                $query->whereDate('created_at', '>=', $request->input('data_inicio'));
            }
            if ($hasDataFim) {
                $query->whereDate('created_at', '<=', $request->input('data_fim'));
            }
        } else {
            // Regra Padrão: Se o usuário não definiu datas, busca somente dos últimos 30 dias
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        // Paginando para evitar lentidão e mantendo os parâmetros de busca na URL
        $requerimentos = $query->latest()->paginate(15)->appends($request->query());
        $setores = Setor::where('ativo', true)->orderBy('setor_sigla')->get();

        return view('admin.consultaRequerimento', [
            'requerimentos' => $requerimentos,
            'setores'       => $setores,
        ]);
    }
}
