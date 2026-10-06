<?php

namespace App\Http\Controllers;

use App\Mail\InformacoesAlunoMail;
use App\Models\Requerimento;
use App\Models\Setor;
use App\Services\DocumentoRequerimentoService;
use App\Services\RequerimentoEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnvioEmailController extends Controller
{
    /**
     * Protocola o processo de licenciamento ambiental:
     * salva no banco, gera PDF, e envia e-mail ao setor e ao cidadão.
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'setor'                => 'nullable|string',
            'setor_id'             => 'nullable',
            'objeto'               => 'nullable|string|max:255',
            'objetoDoRequerimento' => 'nullable|string|max:255',
            'objeto_outro'         => 'nullable|string|max:255',
            'motivo'               => 'nullable|string|max:3000',
            'mensagem'             => 'nullable|string|max:3000',
            'telefone'             => 'nullable|string|max:30',
            'rua'                  => 'nullable|string|max:255',
            'numero'               => 'nullable|string|max:20',
            'bairro'               => 'nullable|string|max:255',
            'cidade'               => 'nullable|string|max:255',
            'estado'               => 'nullable|string|max:2',
            'cep'                  => 'nullable|string|max:10',
            'endereco'             => 'nullable|string|max:255',
            'email_adicional'      => 'nullable|email',
            'arquivos.*'           => 'nullable|file|max:51200', // 50MB por arquivo complementar
            'documentos.*'         => 'nullable|file|max:51200', // 50MB por documento obrigatório
        ]);

        $setorParam = $request->input('setor_id') ?? $request->input('setor');
        $setor = null;
        if (is_numeric($setorParam)) {
            $setor = Setor::find($setorParam);
        }
        if (!$setor && !empty($setorParam)) {
            $setor = Setor::where('setor_sigla', $setorParam)->first();
        }
        if (!$setor) {
            $setor = Setor::where('ativo', true)->first();
        }

        if (!$setor) {
            return back()->withErrors(['setor' => 'O setor selecionado é inválido.']);
        }

        $cidadao = auth()->user();
        $aluno   = $cidadao; // alias mantido para compatibilidade com chamadas internas

        // 1. Atualiza campos cadastrais do cidadão (telefone/celular se informados no form)
        $dadosUsuario = [];
        if ($request->filled('telefone')) {
            $dadosUsuario['telefone'] = $request->input('telefone');
        }
        if ($request->filled('celular')) {
            $dadosUsuario['celular'] = $request->input('celular');
        }

        if (!empty($dadosUsuario)) {
            try {
                $cidadao->update($dadosUsuario);
            } catch (\Throwable $e) {
                logger()->info('Não foi possível persistir dados cadastrais do cidadão: ' . $e->getMessage());
            }
        }

        // 2. Atualiza dados de endereço na tabela 'enderecos'
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
                logger()->info('Não foi possível persistir dados de endereço: ' . $e->getMessage());
            }
        }

        $objeto = !empty($request->input('objeto_outro'))
            ? 'Outros: ' . $request->input('objeto_outro')
            : ($request->input('objetoDoRequerimento') ?? $request->input('objeto', 'Requerimento Geral'));

        $motivo = !empty($request->input('motivo'))
            ? $request->input('motivo')
            : (!empty($request->input('mensagem')) ? $request->input('mensagem') : 'Solicitação de ' . $objeto);

        $servicoEmail = app(RequerimentoEmailService::class);
        $destinatarios = $servicoEmail->resolverDestinatarios(
            $setor,
            $cidadao,
            $request->input('email_adicional')
        );

        if ($destinatarios === null) {
            return back()->withErrors(['geral' => 'Nenhum e-mail de destino válido foi encontrado.']);
        }

        // 2. Validação de documentos obrigatórios e coleta de arquivos
        $assunto = null;
        if (empty($request->input('objeto_outro'))) {
            $objetoTexto = $request->input('objetoDoRequerimento') ?? $request->input('objeto');
            $assunto = \App\Models\AssuntoRequerimento::where('descricao', $objetoTexto)->first();

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

        // Coleta todos os arquivos enviados (específicos de documentos + complementares)
        $todosArquivosInput = [
            $request->file('documentos', []),
            $request->file('arquivos', [])
        ];

        $arquivos = [];
        array_walk_recursive($todosArquivosInput, function ($item) use (&$arquivos) {
            if ($item instanceof \Illuminate\Http\UploadedFile && $item->isValid()) {
                $arquivos[] = $item;
            }
        });

        logger()->info('Requerimento: arquivos coletados', [
            'total'     => count($arquivos),
            'nomes'     => array_map(fn($f) => $f->getClientOriginalName(), $arquivos),
            'documentos_raw' => array_keys($request->file('documentos', [])),
        ]);

        // 3. Salva o registro no banco de dados
        $requerimento = null;
        try {
            // Tenta encontrar o assunto pelo texto selecionado
            $assunto = \App\Models\AssuntoRequerimento::where('descricao', $objeto)->first();

            $requerimento = Requerimento::create([
                'usuario_id'             => $aluno->id,
                'assunto_requerimento_id' => $assunto?->id,
                'objetoDoRequerimento'   => $objeto,
                'motivo'                 => $motivo,
                'status'                 => 'Aberto',
                'setor_id'                => $setor->id,
            ]);
            // Chama método para gerar número de protocolo;
            $this->gerarNumeroProtocolo($requerimento);

            $dominioEmail = substr(strrchr((string) config('mail.from.address'), '@') ?: '', 1);
            $dominioEmail = $dominioEmail ?: (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost');
            $requerimento->forceFill([
                'email_message_id' => Str::uuid() . '@' . $dominioEmail,
            ])->save();

            // Salva no banco e no storage os documentos enviados vinculados ao histórico inicial
            $historicoInicial = $requerimento->historicos()->first();
            app(DocumentoRequerimentoService::class)->salvarDocumentosIniciais(
                requerimento: $requerimento,
                historico: $historicoInicial,
                documentosInput: $request->file('documentos', []),
                arquivosComplementares: $request->file('arquivos', []),
                usuario: $aluno
            );
        } catch (\Exception $e) {
            logger()->warning('Não foi possível salvar requerimento no BD: ' . $e->getMessage());
        }

        $resultadoPdf = app(DocumentoRequerimentoService::class)->gerarESalvarPdfRequerimento(
            requerimento: $requerimento,
            aluno: $aluno,
            setorNome: $setor->setor_nome,
            arquivos: $arquivos,
            historico: $requerimento?->historicos()->first(),
            usuario: $aluno,
            setorChave: (string) $setor->id,
            objeto: $objeto,
            mensagem: $motivo
        );

        $mailable = new InformacoesAlunoMail(
            aluno: $aluno,
            setorNome: $setor->setor_nome,
            mensagem: $motivo,
            arquivos: $resultadoPdf['arquivos_nao_mesclados'],
            objeto: $objeto,
            setorChave: (string) $setor->id,
            requerimento: $requerimento,
            pdfRequerimento: $resultadoPdf['pdf']
        );

        $servicoEmail->enviar($mailable, $destinatarios);

        return back()->with('sucesso', 'Requerimento enviado com sucesso!');
    }

    public function gerarNumeroProtocolo(Requerimento $requerimento){
        $ano = $requerimento->created_at?->format('Y') ?? date('Y');
        $requerimento->numero_protocolo = $ano . '/' . $requerimento->id;
        $requerimento->save();
    }
}
