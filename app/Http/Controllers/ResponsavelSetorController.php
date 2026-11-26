<?php

namespace App\Http\Controllers;

use App\Models\Setor;
use App\Models\AssuntoRequerimento;
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
            ->whereIn('status', Requerimento::STATUS_ANALISE_ATIVA)
            ->count();

        $totalConcluidos = (clone $baseQuery)
            ->whereIn('status', Requerimento::STATUS_TERMINAIS)
            ->count();

        $totalIndeferidos = (clone $baseQuery)
            ->where('status', Requerimento::STATUS_INDEFERIDO)
            ->count();

        $totalAberto = (clone $baseQuery)
            ->where('status', Requerimento::STATUS_PROTOCOLADO)
            ->count();

        $query = (clone $baseQuery)->latest();

        if ($request->filled('aluno')) {
            $aluno = trim($request->input('aluno'));
            $query->whereHas('usuario', function ($q) use ($aluno) {
                $q->where('nome', 'like', "%{$aluno}%")
                  ->orWhere('matricula', 'like', "%{$aluno}%");
            });
        }

        $tipoPessoa = $request->input('turma') ?: $request->input('tipo_pessoa');
        if (!empty($tipoPessoa)) {
            $query->whereHas('usuario', function ($q) use ($tipoPessoa) {
                $q->where('tipo_registro', $tipoPessoa);
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

        // Carrega o usuário, endereço, assunto, empreendimento e os históricos (linha do tempo com documentos)
        $requerimento->load(['usuario.endereco', 'assunto', 'historicos.usuario', 'historicos.setor', 'historicos.documentos', 'setorRetorno', 'empreendimento']);
        $setoresDestino = Setor::query()
            ->with(['assuntosAtivos' => function ($q) {
                $q->orderBy('ordem');
            }])
            ->where('ativo', true)
            ->where('id', '!=', $setor->id)
            ->whereHas('responsaveis')
            ->orderBy('setor_nome')
            ->get();

        $catalogoSetoresDestino = $setoresDestino->mapWithKeys(function ($s) {
            return [
                $s->id => $s->assuntosAtivos->map(function ($a) {
                    return [
                        'id'         => $a->id,
                        'descricao'  => $a->descricao,
                        'observacao' => $a->observacao,
                    ];
                })->values()->all(),
            ];
        })->all();

        return view('setor.requerimentos.show', compact('setor', 'requerimento', 'setoresDestino', 'catalogoSetoresDestino'));
    }

    public function atualizarStatus(Request $request, Setor $setor, Requerimento $requerimento)
    {
        $this->autorizarSetor($setor);

        if ((int) $requerimento->setor_id !== (int) $setor->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status'     => ['required', 'string', Rule::in($requerimento->proximosStatusPossiveis())],
            'observacao' => [
                Rule::requiredIf(fn () => in_array($request->input('status'), [
                    Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
                    Requerimento::STATUS_INDEFERIDO,
                    Requerimento::STATUS_ENCERRADO,
                ], true)),
                'nullable',
                'string',
                'max:10000',
            ],
            'nome_documento_solicitado' => [
                Rule::requiredIf(fn () => $request->input('status') === Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO),
                'nullable',
                'string',
                'max:255',
            ],
            'arquivos'   => [
                Rule::requiredIf(fn () => $request->input('status') === Requerimento::STATUS_LICENCA_EMITIDA),
                'nullable',
                'array',
                'min:1',
            ],
            'arquivos.*' => 'required|file|max:51200',
        ]);

        $solicitaNovoDocumento = $validated['status'] === Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO;
        $nomeDocumentoSolicitado = $solicitaNovoDocumento
            ? $validated['nome_documento_solicitado']
            : null;
        $mensagemHistorico = trim((string) ($validated['observacao'] ?? ''));
        if ($mensagemHistorico === '') {
            $mensagemHistorico = "Status alterado para {$validated['status']} pelo setor.";
        }

        $novoHistorico = DB::transaction(function () use (
            $requerimento,
            $setor,
            $validated,
            $mensagemHistorico,
            $solicitaNovoDocumento,
            $nomeDocumentoSolicitado
        ) {
            $atual = Requerimento::query()->lockForUpdate()->findOrFail($requerimento->id);

            if ((int) $atual->setor_id !== (int) $setor->id) {
                abort(404);
            }
            if (!in_array($validated['status'], $atual->proximosStatusPossiveis(), true)) {
                abort(409, 'O status do processo mudou. Atualize a página e tente novamente.');
            }

            $atual->update([
                'status' => $validated['status'],
                'data_devolucao' => $validated['status'] === Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO ? now() : null,
                'setor_retorno_id' => in_array($validated['status'], Requerimento::STATUS_TERMINAIS, true)
                    ? null
                    : $atual->setor_retorno_id,
            ]);

            return HistoricoRequerimento::create([
                'requerimento_id'           => $atual->id,
                'user_id'                   => Auth::id() ?? $atual->usuario_id,
                'setor_id'                  => $setor->id,
                'status'                    => $atual->status,
                'observacao'                => $mensagemHistorico,
                'solicita_novo_documento'   => $solicitaNovoDocumento,
                'nome_documento_solicitado' => $nomeDocumentoSolicitado,
            ]);
        });
        $requerimento->refresh();

        // Salva arquivos anexados pelo servidor vinculados ao histórico
        $arquivos = $request->file('arquivos', []);
        if (!empty($arquivos) && $novoHistorico) {
            $tituloDocumento = $validated['status'] === Requerimento::STATUS_LICENCA_EMITIDA
                ? 'Licença ambiental emitida'
                : 'Documento do setor';

            app(DocumentoRequerimentoService::class)->salvarArquivos(
                requerimento: $requerimento,
                historico: $novoHistorico,
                arquivos: is_array($arquivos) ? $arquivos : [$arquivos],
                usuario: auth()->user(),
                titulo: $tituloDocumento
            );
        }

        // Envia e-mail de notificação para o aluno (com cópia para o setor)
        $requerimento->notificarPartes(
            mensagem: $mensagemHistorico,
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

        if ((int) $requerimento->setor_id !== (int) $setor->id) {
            abort(404);
        }
        if ($requerimento->isTerminal()) {
            abort(409, 'Processos encerrados não podem ser encaminhados.');
        }

        $validated = $request->validate([
            'setor_destino_id'    => ['required', 'integer', Rule::exists('setores', 'id')->where('ativo', true)],
            'assunto_destino_id'  => ['nullable', 'integer', Rule::exists('assuntos_requerimentos', 'id')->where('ativo', true)],
            'tipo_encaminhamento' => ['nullable', 'string', 'in:transferencia,despacho'],
            'observacao'          => 'required|string|max:10000',
            'arquivos'            => 'nullable|array',
            'arquivos.*'          => 'nullable|file|max:51200',
        ]);

        $tipoEncaminhamento = $validated['tipo_encaminhamento'] ?? 'transferencia';

        $destino = Setor::query()
            ->where('ativo', true)
            ->whereHas('responsaveis')
            ->find($validated['setor_destino_id']);

        if (!$destino || (int) $destino->id === (int) $setor->id) {
            return back()->withErrors(['setor_destino_id' => 'Selecione outro setor ativo com responsáveis.']);
        }

        $assuntoDestino = !empty($validated['assunto_destino_id'])
            ? AssuntoRequerimento::where('setor_id', $destino->id)->where('ativo', true)->find($validated['assunto_destino_id'])
            : null;

        $historico = DB::transaction(function () use ($setor, $destino, $requerimento, $validated, $assuntoDestino, $tipoEncaminhamento) {
            $atual = Requerimento::query()->lockForUpdate()->findOrFail($requerimento->id);
            if ((int) $atual->setor_id !== (int) $setor->id) {
                abort(409, 'Este requerimento já foi encaminhado ou mudou de setor.');
            }
            if ($atual->isTerminal()) {
                abort(409, 'Processos encerrados não podem ser encaminhados.');
            }

            if ($tipoEncaminhamento === 'despacho') {
                $atual->update([
                    'setor_id' => $destino->id,
                    'setor_retorno_id' => $setor->id,
                    'status' => Requerimento::STATUS_EM_ANALISE,
                    'data_devolucao' => null,
                ]);
                $orientacaoTexto = "Despacho de {$setor->setor_sigla} para {$destino->setor_sigla}.";
            } else {
                // Transferência direta/definitiva entre setores
                $atual->update([
                    'setor_id' => $destino->id,
                    'setor_retorno_id' => null,
                    'status' => Requerimento::STATUS_EM_ANALISE,
                    'data_devolucao' => null,
                ]);
                $orientacaoTexto = "Encaminhado de {$setor->setor_sigla} para {$destino->setor_sigla}.";
            }

            if ($assuntoDestino) {
                $orientacaoTexto .= "\n[Serviço Solicitado: {$assuntoDestino->descricao}]";
            }
            $orientacaoTexto .= "\n{$validated['observacao']}";

            return HistoricoRequerimento::create([
                'requerimento_id' => $atual->id,
                'user_id' => Auth::id(),
                'setor_id' => $setor->id,
                'status' => $atual->status,
                'observacao' => $orientacaoTexto,
            ]);
        });

        $arquivos = $request->file('arquivos', []);
        if (!empty($arquivos)) {
            app(DocumentoRequerimentoService::class)->salvarArquivos(
                requerimento: $requerimento,
                historico: $historico,
                arquivos: $arquivos,
                usuario: Auth::user(),
                titulo: $tipoEncaminhamento === 'despacho' ? 'Documento de despacho' : 'Documento de encaminhamento'
            );
        }

        $msgSucesso = $tipoEncaminhamento === 'despacho'
            ? 'Requerimento encaminhado para despacho em ' . $destino->setor_nome . '.'
            : 'Requerimento transferido para ' . $destino->setor_nome . ' com sucesso.';

        return redirect()
            ->route('setor.responsavel.dashboard', $setor->id)
            ->with('success', $msgSucesso);
    }

    public function responderEncaminhamento(Request $request, Setor $setor, Requerimento $requerimento)
    {
        $this->autorizarSetor($setor);

        if ((int) $requerimento->setor_id !== (int) $setor->id || !$requerimento->setor_retorno_id) {
            abort(404);
        }
        if ($requerimento->isTerminal()) {
            abort(409, 'Processos encerrados não podem receber respostas de encaminhamento.');
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
            if ($atual->isTerminal()) {
                abort(409, 'Processos encerrados não podem receber respostas de encaminhamento.');
            }

            $setorOrigem = Setor::findOrFail($atual->setor_retorno_id);
            $atual->update([
                'setor_id' => $setorOrigem->id,
                'setor_retorno_id' => null,
            ]);

            $historico = HistoricoRequerimento::create([
                'requerimento_id' => $atual->id,
                'user_id' => Auth::id(),
                'setor_id' => $setor->id,
                'status' => $atual->status,
                'observacao' => "Resposta de {$setor->setor_sigla} para {$setorOrigem->setor_sigla}.\n{$validated['observacao']}",
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
