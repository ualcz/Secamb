<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmacaoRequerimentoUsuarioMail;
use App\Mail\NotificacaoSetorMail;
use App\Models\AssuntoRequerimento;
use App\Models\Empreendimento;
use App\Models\Requerimento;
use App\Models\RequerimentoRascunho;
use App\Models\Setor;
use App\Models\Usuario;
use App\Services\DocumentoRequerimentoService;
use App\Support\CaracterizacaoEmpreendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EnvioEmailController extends Controller
{
    /**
     * Protocola o requerimento:
     * salva no banco de dados, registra o histórico e salva os arquivos no disco.
     * Nenhum e-mail é enviado — apenas uma notificação de sucesso na tela.
     */
    public function enviar(Request $request)
    {
        $validated = $request->validate([
            'setor'                => 'nullable|string',
            'setor_id'             => 'nullable',
            'empreendimento_id'    => 'required|integer|exists:empreendimentos,id',
            'representante_legal_id' => 'nullable|integer|exists:usuarios,id',
            'objeto'               => 'nullable|string|max:255',
            'objetoDoRequerimento' => 'nullable|string|max:255',
            'objeto_outro'         => 'nullable|string|max:255',
            'motivo'               => 'required|string|max:3000',
            'aceite_declaracao'    => ['required', 'accepted'],
            'mensagem'             => 'nullable|string|max:3000',
            'celular'              => 'nullable|string|max:30',
            'rua'                  => 'nullable|string|max:255',
            'numero'               => 'nullable|string|max:20',
            'complemento'          => 'nullable|string|max:255',
            'bairro'               => 'nullable|string|max:255',
            'cidade'               => 'nullable|string|max:255',
            'estado'               => 'nullable|string|max:2',
            'cep'                  => 'nullable|string|max:10',
            'endereco'             => 'nullable|string|max:255',
            ...CaracterizacaoEmpreendimento::rules(),
            'arquivos.*'           => 'nullable|file|max:51200',
            'documentos.*'         => 'nullable|file|max:51200',
        ], [
            'aceite_declaracao.required' => 'Marque a caixa para confirmar sua concordância com a declaração.',
            'aceite_declaracao.accepted' => 'É necessário aceitar a declaração para protocolar o requerimento.',
            'representante_legal_id' => 'Selecione um representante legal ativo vinculado ao empreendimento.',
            ...CaracterizacaoEmpreendimento::mensagens(),
        ]);

        // 1. Identifica o setor de destino
        $setorParam = $request->input('setor_id') ?? $request->input('setor');
        $setor = null;
        if (is_numeric($setorParam)) {
            $setor = Setor::find($setorParam);
        }
        if (!$setor && !empty($setorParam)) {
            $setor = Setor::where('setor_sigla', $setorParam)->first();
        }
        if (!$setor) {
            $setor = Setor::where('ativo', true)->where('is_interno', false)->first();
        }

        if (!$setor) {
            return back()->withErrors(['setor' => 'O setor selecionado é inválido.'])->withInput();
        }

        if ($setor->is_interno && in_array(auth()->user()?->role, ['cidadao', 'tecnico'], true)) {
            return back()->withErrors(['setor' => 'Não é permitido submeter requerimentos diretamente para um setor interno.'])->withInput();
        }

        $cidadao = auth()->user();
        $empreendimento = Empreendimento::with(['criador', 'representantes'])
            ->findOrFail($request->input('empreendimento_id'));
        if (!$cidadao->todosEmpreendimentos()->whereKey($empreendimento->id)->exists()) {
            throw ValidationException::withMessages([
                'empreendimento_id' => 'O empreendimento selecionado não está vinculado à sua conta.',
            ]);
        }

        $representantesElegiveis = $empreendimento->representantesElegiveisParaRequerimento();
        $representanteLegal = $representantesElegiveis->firstWhere(
            'id',
            (int) ($validated['representante_legal_id'] ?? 0)
        );
        if (!$representanteLegal && !array_key_exists('representante_legal_id', $validated) && $representantesElegiveis->count() === 1) {
            $representanteLegal = $representantesElegiveis->first();
        }
        if (!$representanteLegal) {
            throw ValidationException::withMessages([
                'representante_legal_id' => 'Selecione um representante legal ativo e com termo aceito para este empreendimento.',
            ]);
        }

        $caracterizacao = CaracterizacaoEmpreendimento::persistir($validated);

        // 2. Atualiza dados de contato do cidadão se preenchidos
        $dadosUsuario = [];
        if ($request->filled('celular')) {
            $dadosUsuario['celular'] = $request->input('celular');
        }
        if (!empty($dadosUsuario)) {
            try {
                $cidadao->update($dadosUsuario);
            } catch (\Throwable $e) {
                Log::info('Erro ao atualizar contato do cidadão: ' . $e->getMessage());
            }
        }

        // 3. Atualiza endereço do cidadão se preenchido
        $dadosEndereco = [];
        foreach (['rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep'] as $campo) {
            if ($request->filled($campo)) {
                $dadosEndereco[$campo] = $request->input($campo);
            }
        }
        if (!empty($dadosEndereco)) {
            try {
                $cidadao->endereco()->updateOrCreate([], $dadosEndereco);
                $cidadao->load('endereco');
            } catch (\Throwable $e) {
                Log::info('Erro ao atualizar endereço do cidadão: ' . $e->getMessage());
            }
        }

        // 4. Determina o objeto e motivo do requerimento
        $objeto = !empty($request->input('objeto_outro'))
            ? 'Outros: ' . $request->input('objeto_outro')
            : ($request->input('objetoDoRequerimento') ?? $request->input('objeto', 'Licenciamento Ambiental Geral'));

        $motivo = !empty($request->input('motivo'))
            ? $request->input('motivo')
            : (!empty($request->input('mensagem')) ? $request->input('mensagem') : 'Solicitação de ' . $objeto);

        // 5. Valida documentos obrigatórios vinculados ao assunto
        $assunto = null;
        if (empty($request->input('objeto_outro'))) {
            $objetoTexto = $request->input('objetoDoRequerimento') ?? $request->input('objeto');
            $assunto = AssuntoRequerimento::where('descricao', $objetoTexto)->first();

            if ($assunto) {
                $docsObrigatorios = $assunto->documentosObrigatorios()->get();
                $documentosEnviados = $request->file('documentos', []);

                $errosAnexos = [];
                foreach ($docsObrigatorios as $doc) {
                    $arquivoDoc = $documentosEnviados[$doc->id] ?? null;
                    if (!$arquivoDoc || !($arquivoDoc instanceof \Illuminate\Http\UploadedFile) || !$arquivoDoc->isValid()) {
                        $errosAnexos[] = "O documento '{$doc->nome}' é obrigatório para a solicitação de '{$assunto->descricao}'.";
                    }
                }

                if (!empty($errosAnexos)) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => implode(' ', $errosAnexos),
                            'errors'  => $errosAnexos
                        ], 422);
                    }
                    return back()->withErrors($errosAnexos)->withInput();
                }
            }
        }

        // 6. Persiste Requerimento, Histórico e Documentos em transação
        $requerimento = null;
        $historico    = null;

        try {
            DB::beginTransaction();

            if (!$assunto) {
                $assunto = AssuntoRequerimento::where('descricao', $objeto)->first();
            }

            $requerimento = Requerimento::create([
                'usuario_id'              => $representanteLegal->id,
                'protocolado_por_id'      => $cidadao->id,
                'empreendimento_id'       => $request->input('empreendimento_id'),
                'setor_id'                => $setor->id,
                'assunto_requerimento_id' => $assunto?->id,
                'objetoDoRequerimento'    => $objeto,
                'tipo_processo'           => $objeto,
                'descricao'               => $motivo,
                'motivo'                  => $motivo,
                'caracterizacao_empreendimento' => $caracterizacao,
                'status'                  => Requerimento::STATUS_PROTOCOLADO,
            ]);

            // Gera o número de protocolo: ANO/ID
            $this->gerarNumeroProtocolo($requerimento);

            // O Observer já cria o histórico inicial — apenas o recuperamos
            $historico = $requerimento->historicos()->latest()->first();

            // Salva arquivos no storage
            $documentosInput = $request->file('documentos', []);
            $arquivosComplementares = $request->file('arquivos', []);
            app(DocumentoRequerimentoService::class)->salvarDocumentosIniciais(
                requerimento: $requerimento,
                historico: $historico,
                documentosInput: is_array($documentosInput) ? $documentosInput : [],
                arquivosComplementares: is_array($arquivosComplementares) ? $arquivosComplementares : [],
                usuario: $cidadao
            );

            app(DocumentoRequerimentoService::class)->gerarESalvarPdfRequerimento(
                requerimento: $requerimento,
                aluno: $representanteLegal,
                setorNome: $setor->setor_nome,
                historico: $historico,
                usuario: $cidadao,
                setorChave: $setor->setor_sigla,
                objeto: $objeto,
                mensagem: $motivo
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao protocolar requerimento: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ocorreu um erro ao protocolar seu requerimento: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->withErrors(['geral' => 'Ocorreu um erro ao protocolar seu requerimento: ' . $e->getMessage()])
                ->withInput();
        }

        $this->limparRascunhoEnviado(
            $cidadao->id,
            $setor->id,
            (string) $request->input('objetoDoRequerimento')
        );

        // Envia notificações por e-mail em segundo plano (defer), liberando a resposta HTTP imediata ao usuário
        \Illuminate\Support\defer(function () use ($requerimento, $representanteLegal, $cidadao, $setor, $objeto) {
            $this->enviarNotificacoes($requerimento, $representanteLegal, $cidadao, $setor, $objeto);
        });

        // Suporte a resposta JSON para animação de sucesso no loading
        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('sucesso', "Requerimento nº {$requerimento->numero_protocolo} enviado com sucesso!");
            return response()->json([
                'success'   => true,
                'protocolo' => $requerimento->numero_protocolo,
                'redirect'  => route('requerimentos.cidadao.meusRequerimentos'),
                'mensagem'  => "Requerimento nº {$requerimento->numero_protocolo} enviado com sucesso!",
            ]);
        }

        return redirect()
            ->route('requerimentos.cidadao.meusRequerimentos')
            ->with('sucesso', "Requerimento nº {$requerimento->numero_protocolo} enviado com sucesso!");
    }

    /**
     * Envia notificação simples (sem anexos) ao cidadão e ao setor.
     */
    private function enviarNotificacoes(
        Requerimento $requerimento,
        Usuario $representanteLegal,
        Usuario $protocoladoPor,
        Setor $setor,
        string $objeto
    ): void {
        // 1. Confirmação ao titular; o usuário que protocolou recebe cópia quando for diferente.
        try {
            $emailTitular = trim((string) $representanteLegal->email);
            if (!empty($emailTitular)) {
                $email = Mail::to($emailTitular);
                $emailProtocolador = trim((string) $protocoladoPor->email);
                if ($protocoladoPor->id !== $representanteLegal->id && $emailProtocolador !== '') {
                    $email->cc($emailProtocolador);
                }
                $email->send(new ConfirmacaoRequerimentoUsuarioMail(
                    aluno: $representanteLegal,
                    setorNome: $setor->setor_nome,
                    objeto: $objeto,
                    requerimento: $requerimento,
                    protocoladoPor: $protocoladoPor,
                ));
                Log::info("Confirmação enviada ao titular [{$emailTitular}] — protocolo {$requerimento->numero_protocolo}");
            }
        } catch (\Throwable $e) {
            Log::warning("Falha ao enviar confirmação do protocolo: " . $e->getMessage());
        }

        // 2. Notificação ao setor
        try {
            $emailSetor = trim((string) $setor->email);
            if (!empty($emailSetor)) {
                Mail::to($emailSetor)->send(new NotificacaoSetorMail(
                    cidadao: $representanteLegal,
                    setor: $setor,
                    objeto: $objeto,
                    requerimento: $requerimento,
                    protocoladoPor: $protocoladoPor,
                ));
                Log::info("Notificação enviada ao setor [{$emailSetor}] — protocolo {$requerimento->numero_protocolo}");
            }
        } catch (\Throwable $e) {
            Log::warning("Falha ao notificar setor: " . $e->getMessage());
        }
    }

    /**
     * Gera o número de protocolo no formato ANO/ID.
     */
    public function gerarNumeroProtocolo(Requerimento $requerimento): void
    {
        $ano = $requerimento->created_at?->format('Y') ?? date('Y');
        $requerimento->numero_protocolo = $ano . '/' . $requerimento->id;
        $requerimento->save();
    }

    private function limparRascunhoEnviado(int $usuarioId, int $setorId, string $objeto): void
    {
        try {
            $rascunho = RequerimentoRascunho::where('usuario_id', $usuarioId)
                ->where('setor_id', $setorId)
                ->where('objeto_chave', $objeto)
                ->first();

            if (!$rascunho) {
                return;
            }

            $caminhos = $rascunho->arquivos()->pluck('caminho')->all();
            $rascunho->delete();
            Storage::disk('local')->delete($caminhos);
        } catch (\Throwable $exception) {
            Log::warning('Não foi possível remover o rascunho após o protocolo do requerimento.', [
                'usuario_id' => $usuarioId,
                'setor_id' => $setorId,
                'objeto' => $objeto,
                'erro' => $exception->getMessage(),
            ]);
        }
    }
}
