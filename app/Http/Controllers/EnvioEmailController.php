<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmacaoRequerimentoUsuarioMail;
use App\Mail\NotificacaoSetorMail;
use App\Models\AssuntoRequerimento;
use App\Models\Requerimento;
use App\Models\Setor;
use App\Services\DocumentoRequerimentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnvioEmailController extends Controller
{
    /**
     * Protocola o requerimento:
     * salva no banco de dados, registra o histórico e salva os arquivos no disco.
     * Nenhum e-mail é enviado — apenas uma notificação de sucesso na tela.
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'setor'                => 'nullable|string',
            'setor_id'             => 'nullable',
            'empreendimento_id'    => 'nullable|exists:empreendimentos,id',
            'objeto'               => 'nullable|string|max:255',
            'objetoDoRequerimento' => 'nullable|string|max:255',
            'objeto_outro'         => 'nullable|string|max:255',
            'motivo'               => 'nullable|string|max:3000',
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
            'arquivos.*'           => 'nullable|file|max:51200',
            'documentos.*'         => 'nullable|file|max:51200',
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

        if ($setor->is_interno && auth()->user()?->role === 'cidadao') {
            return back()->withErrors(['setor' => 'Não é permitido submeter requerimentos diretamente para um setor interno.'])->withInput();
        }

        $cidadao = auth()->user();

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
                'usuario_id'              => $cidadao->id,
                'empreendimento_id'       => $request->input('empreendimento_id'),
                'setor_id'                => $setor->id,
                'assunto_requerimento_id' => $assunto?->id,
                'objetoDoRequerimento'    => $objeto,
                'tipo_processo'           => $objeto,
                'descricao'               => $motivo,
                'motivo'                  => $motivo,
                'status'                  => 'Aberto',
            ]);

            // Gera o número de protocolo: ANO/ID
            $this->gerarNumeroProtocolo($requerimento);

            // O Observer já cria o histórico inicial — apenas o recuperamos
            $historico = $requerimento->historicos()->latest()->first();

            // Salva arquivos no storage
            app(DocumentoRequerimentoService::class)->salvarDocumentosIniciais(
                requerimento: $requerimento,
                historico: $historico,
                documentosInput: $request->file('documentos', []),
                arquivosComplementares: $request->file('arquivos', []),
                usuario: $cidadao
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao protocolar requerimento: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['geral' => 'Ocorreu um erro ao protocolar seu requerimento: ' . $e->getMessage()])
                ->withInput();
        }

        // Envia notificações por e-mail de forma resiliente (falha não bloqueia o usuário)
        $this->enviarNotificacoes($requerimento, $cidadao, $setor, $objeto);

        return redirect()
            ->route('requerimentos.aluno.meusRequerimentos')
            ->with('sucesso', "Requerimento nº {$requerimento->numero_protocolo} enviado com sucesso!");
    }

    /**
     * Envia notificação simples (sem anexos) ao cidadão e ao setor.
     */
    private function enviarNotificacoes(
        Requerimento $requerimento,
        $cidadao,
        Setor $setor,
        string $objeto
    ): void {
        // 1. Notificação ao cidadão
        try {
            $emailCidadao = trim((string) $cidadao->email);
            if (!empty($emailCidadao)) {
                Mail::to($emailCidadao)->send(new ConfirmacaoRequerimentoUsuarioMail(
                    aluno: $cidadao,
                    setorNome: $setor->setor_nome,
                    objeto: $objeto,
                    requerimento: $requerimento,
                ));
                Log::info("Notificação enviada ao cidadão [{$emailCidadao}] — protocolo {$requerimento->numero_protocolo}");
            }
        } catch (\Throwable $e) {
            Log::warning("Falha ao notificar cidadão: " . $e->getMessage());
        }

        // 2. Notificação ao setor
        try {
            $emailSetor = trim((string) $setor->email);
            if (!empty($emailSetor)) {
                Mail::to($emailSetor)->send(new NotificacaoSetorMail(
                    cidadao: $cidadao,
                    setor: $setor,
                    objeto: $objeto,
                    requerimento: $requerimento,
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
}
