<?php

namespace App\Http\Controllers;

use App\Models\Setor;
use App\Models\Requerimento;
use App\Models\HistoricoRequerimento;
use App\Services\DocumentoRequerimentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ResponsavelSetorController extends Controller
{
    private function autorizarSetor(Setor $setor): void
    {
        if (!Auth::user()->ehResponsavelDoSetor($setor->id)) {
            abort(403, 'Você não possui permissão para acessar este setor.');
        }
    }

    public function index(Request $request, $id)
    {
        $setor = Setor::findOrFail($id);

        $this->autorizarSetor($setor);

        $baseQuery = Requerimento::with(['usuario', 'assunto.setor'])
            ->where('setor_id', $setor->id);

        $totalAnalise = (clone $baseQuery)
            ->where('status', 'Em Análise')
            ->count();

        $totalConcluidos = (clone $baseQuery)
            ->where('status', 'Concluído')
            ->count();

        $totalIndeferidos = (clone $baseQuery)
            ->where('status', 'Indeferido')
            ->count();

        $totalAberto = (clone $baseQuery)
            ->where('status', 'Aberto')
            ->count();

        $query = (clone $baseQuery)->latest();

        if ($request->filled('aluno')) {
            $aluno = trim($request->input('aluno'));
            $query->whereHas('usuario', function ($q) use ($aluno) {
                $q->where('nome', 'like', "%{$aluno}%")
                  ->orWhere('matricula', 'like', "%{$aluno}%");
            });
        }


        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->input('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->input('data_fim'));
        }

        $requerimentos = $query->paginate(15)->appends($request->query());

        return view('setor.responsavel.dashboard', compact(
            'setor',
            'totalAnalise',
            'totalConcluidos',
            'totalIndeferidos',
            'totalAberto',
            'requerimentos'
        ));
    }

    public function show(Setor $setor, Requerimento $requerimento)
    {
        $this->autorizarSetor($setor);

        if ((int) $requerimento->setor_id !== (int) $setor->id) {
            abort(404);
        }

        // Carrega o usuário, endereço, assunto e os históricos (linha do tempo com documentos)
        $requerimento->load(['usuario.endereco', 'assunto', 'historicos.usuario', 'historicos.documentos', 'setorRetorno']);
        $setoresDestino = Setor::query()
            ->where('ativo', true)
            ->where('id', '!=', $setor->id)
            ->whereHas('responsaveis')
            ->orderBy('setor_nome')
            ->get();

        return view('setor.requerimentos.show', compact('setor', 'requerimento', 'setoresDestino'));
    }

    public function atualizarStatus(Request $request, Setor $setor, Requerimento $requerimento)
    {
        $this->autorizarSetor($setor);

        if ((int) $requerimento->setor_id !== (int) $setor->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status'     => 'required|in:Em Análise,Indeferido,Concluído',
            'observacao' => 'required_if:status,Indeferido|nullable|string',
            'solicita_novo_documento' => 'nullable|boolean',
            'nome_documento_solicitado' => 'required_if:solicita_novo_documento,1|nullable|string',
            'arquivos'   => 'nullable|array',
            'arquivos.*' => 'nullable|file|max:51200',
        ]);

        $solicitaNovoDocumento = $validated['status'] === 'Indeferido'
            || $request->boolean('solicita_novo_documento');
        $request->merge([
            'solicita_novo_documento' => $solicitaNovoDocumento,
            'nome_documento_solicitado' => $solicitaNovoDocumento
                ? ($validated['nome_documento_solicitado'] ?? 'Documento indeferido')
                : null,
        ]);

        $requerimento->update([
            'status' => $validated['status'],
        ]);

        $novoHistorico = HistoricoRequerimento::create([
            'requerimento_id'           => $requerimento->id,
            'user_id'                   => Auth::id() ?? $requerimento->usuario_id,
            'status'                    => $requerimento->status,
            'observacao'                => $validated['observacao'] ?? 'Despacho registrado pelo setor.',
            'solicita_novo_documento'   => $solicitaNovoDocumento,
            'nome_documento_solicitado' => $solicitaNovoDocumento ? ($validated['nome_documento_solicitado'] ?? 'Documento indeferido') : null,
        ]);

        // Salva arquivos anexados pelo servidor vinculados ao histórico
        $arquivos = $request->file('arquivos', []);
        if (!empty($arquivos) && $novoHistorico) {
            app(DocumentoRequerimentoService::class)->salvarArquivos(
                requerimento: $requerimento,
                historico: $novoHistorico,
                arquivos: is_array($arquivos) ? $arquivos : [$arquivos],
                usuario: auth()->user(),
                titulo: 'Despacho / Documento do Setor'
            );
        }

        // Envia e-mail de notificação para o aluno (com cópia para o setor)
        $requerimento->notificarPartes(
            mensagem: $validated['observacao'] ?? '',
            remetente: 'setor',
            arquivos: is_array($arquivos) ? $arquivos : [],
            solicitaNovoDocumento: $solicitaNovoDocumento
        );

        activity()
            ->performedOn($requerimento)
            ->causedBy(auth()->user())
            ->withProperties([
                'status' => $validated['status'],
                'setor' => $setor->setor_sigla,
            ])
            ->log('Status do requerimento atualizado');

        return redirect()
            ->back()
            ->with('success', 'Status do requerimento atualizado para "' . $requerimento->status . '" com sucesso!');
    }

    public function encaminhar(Request $request, Setor $setor, Requerimento $requerimento)
    {
        $this->autorizarSetor($setor);

        if ((int) $requerimento->setor_id !== (int) $setor->id || $requerimento->setor_retorno_id) {
            abort(404);
        }

        $validated = $request->validate([
            'setor_destino_id' => ['required', 'integer', Rule::exists('setores', 'id')->where('ativo', true)],
            'observacao' => 'required|string|max:10000',
            'arquivos' => 'nullable|array',
            'arquivos.*' => 'nullable|file|max:51200',
        ]);

        $destino = Setor::query()
            ->where('ativo', true)
            ->whereHas('responsaveis')
            ->find($validated['setor_destino_id']);

        if (!$destino || (int) $destino->id === (int) $setor->id) {
            return back()->withErrors(['setor_destino_id' => 'Selecione outro setor ativo com responsáveis.']);
        }

        $historico = DB::transaction(function () use ($setor, $destino, $requerimento, $validated) {
            $atual = Requerimento::query()->lockForUpdate()->findOrFail($requerimento->id);
            if ((int) $atual->setor_id !== (int) $setor->id || $atual->setor_retorno_id) {
                abort(409, 'Este requerimento já foi encaminhado ou mudou de setor.');
            }

            $atual->update([
                'setor_id' => $destino->id,
                'setor_retorno_id' => $setor->id,
                'status' => 'Despacho',
            ]);

            return HistoricoRequerimento::create([
                'requerimento_id' => $atual->id,
                'user_id' => Auth::id(),
                'status' => $atual->status,
                'observacao' => "Orientação para: {$destino->setor_sigla}\n{$validated['observacao']}",
            ]);
        });

        $arquivos = $request->file('arquivos', []);
        if (!empty($arquivos)) {
            app(DocumentoRequerimentoService::class)->salvarArquivos(
                requerimento: $requerimento,
                historico: $historico,
                arquivos: $arquivos,
                usuario: Auth::user(),
                titulo: 'Documento de encaminhamento'
            );
        }

        return redirect()
            ->route('setor.responsavel.dashboard', $setor->id)
            ->with('success', 'Requerimento encaminhado para ' . $destino->setor_nome . '.');
    }

    public function responderEncaminhamento(Request $request, Setor $setor, Requerimento $requerimento)
    {
        $this->autorizarSetor($setor);

        if ((int) $requerimento->setor_id !== (int) $setor->id || !$requerimento->setor_retorno_id) {
            abort(404);
        }

        $validated = $request->validate([
            'observacao' => 'required|string|max:10000',
            'arquivos' => 'nullable|array',
            'arquivos.*' => 'nullable|file|max:51200',
        ]);

        [$setorOrigem, $historico] = DB::transaction(function () use ($setor, $requerimento, $validated) {
            $atual = Requerimento::query()->lockForUpdate()->findOrFail($requerimento->id);
            if ((int) $atual->setor_id !== (int) $setor->id || !$atual->setor_retorno_id) {
                abort(409, 'Este requerimento já foi devolvido ou mudou de setor.');
            }

            $setorOrigem = Setor::findOrFail($atual->setor_retorno_id);
            $atual->update([
                'setor_id' => $setorOrigem->id,
                'setor_retorno_id' => null,
            ]);

            $historico = HistoricoRequerimento::create([
                'requerimento_id' => $atual->id,
                'user_id' => Auth::id(),
                'status' => $atual->status,
                'observacao' => "Resposta para: {$setorOrigem->setor_sigla}\n{$validated['observacao']}",
            ]);

            return [$setorOrigem, $historico];
        });

        $arquivos = $request->file('arquivos', []);
        if (!empty($arquivos)) {
            app(DocumentoRequerimentoService::class)->salvarArquivos(
                requerimento: $requerimento,
                historico: $historico,
                arquivos: $arquivos,
                usuario: Auth::user(),
                titulo: 'Resposta do setor'
            );
        }

        return redirect()
            ->route('setor.responsavel.dashboard', $setor->id)
            ->with('success', 'Resposta registrada e requerimento devolvido para ' . $setorOrigem->setor_nome . '.');
    }
}
