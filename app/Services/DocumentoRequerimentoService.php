<?php

namespace App\Services;

use App\Http\Controllers\RequerimentoPdfController;
use App\Models\DocumentoAssunto;
use App\Models\DocumentoRequerimento;
use App\Models\HistoricoRequerimento;
use App\Models\Requerimento;
use App\Models\Usuario;
use App\Services\Pdf\PdfMergerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoRequerimentoService
{
    public function gerarESalvarPdfRequerimento(
        ?Requerimento $requerimento,
        Usuario $aluno,
        string $setorNome,
        array $arquivos = [],
        ?HistoricoRequerimento $historico = null,
        ?Usuario $usuario = null,
        ?string $setorChave = null,
        ?string $objeto = null,
        ?string $mensagem = null
    ): array {
        $arquivosNaoMesclados = [];

        try {
            $pdf = RequerimentoPdfController::criarPdf(
                nomeRequerente: $aluno,
                setorNome: $setorNome,
                setorChave: $setorChave,
                objeto: $objeto,
                mensagem: $mensagem,
                numeroProtocolo: $requerimento?->numero_protocolo,
                requerimento: $requerimento
            );
            $conteudo = $pdf->output();
        } catch (\Throwable $e) {
            logger()->error('Erro ao gerar PDF do requerimento: ' . $e->getMessage());
            throw new \RuntimeException('Não foi possível gerar o PDF do requerimento.', 0, $e);
        }

        if (!empty($arquivos)) {
            try {
                $conteudo = app(PdfMergerService::class)->mesclarComAnexos(
                    $conteudo,
                    $arquivos,
                    $arquivosNaoMesclados
                );
            } catch (\Throwable $e) {
                logger()->warning('Não foi possível incluir todos os anexos no PDF do requerimento; os documentos originais foram mantidos.', [
                    'requerimento_id' => $requerimento?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($arquivosNaoMesclados)) {
            logger()->warning('Alguns documentos não puderam ser incluídos no PDF unificado; os originais foram mantidos.', [
                'requerimento_id' => $requerimento?->id,
                'quantidade' => count($arquivosNaoMesclados),
            ]);
        }

        $nomePdf = 'Requerimento_' . Str::slug($aluno->nome) . '_' . date('Ymd_His') . '.pdf';

        if ($requerimento) {
            $documentoSalvo = $this->salvarConteudo(
                conteudo: $conteudo,
                nomeOriginal: $nomePdf,
                requerimento: $requerimento,
                historico: $historico,
                usuario: $usuario,
                nomeDocumento: 'Requerimento enviado'
            );

            if (!$documentoSalvo) {
                throw new \RuntimeException('Não foi possível salvar o PDF do requerimento no histórico.');
            }
        }

        return [
            'pdf' => [
                'nome' => $nomePdf,
                'conteudo' => $conteudo,
            ],
            'arquivos_nao_mesclados' => $arquivosNaoMesclados,
        ];
    }

    /**
     * Salva um único arquivo enviado e registra no banco de dados.
     */
    public function salvarArquivo(
        UploadedFile $arquivo,
        Requerimento $requerimento,
        ?HistoricoRequerimento $historico = null,
        ?Usuario $usuario = null,
        ?string $nomeDocumento = null
    ): ?DocumentoRequerimento {
        if (!$arquivo->isValid()) {
            return null;
        }

        $nomeOriginal = $arquivo->getClientOriginalName();
        $extensao = strtolower($arquivo->getClientOriginalExtension() ?: $arquivo->extension() ?: 'bin');
        $mimeType = $arquivo->getClientMimeType() ?: $arquivo->getMimeType();
        $tamanho = $arquivo->getSize() ?: 0;

        // Gera nome seguro único para o arquivo no disco
        $nomeArquivoArmazenado = Str::random(20) . '_' . Str::slug(pathinfo($nomeOriginal, PATHINFO_FILENAME)) . '.' . $extensao;
        $pastaDestino = 'documentos_requerimentos/' . $requerimento->id;

        // Salva no disco 'local' (storage/app/private/...)
        $caminhoSalvo = $arquivo->storeAs($pastaDestino, $nomeArquivoArmazenado, 'local');

        return DocumentoRequerimento::create([
            'requerimento_id'           => $requerimento->id,
            'historico_requerimento_id' => $historico?->id,
            'user_id'                   => $usuario?->id ?? auth()->id(),
            'nome_documento'            => $nomeDocumento ?: $nomeOriginal,
            'nome_original'             => $nomeOriginal,
            'caminho'                   => $caminhoSalvo,
            'extensao'                  => $extensao,
            'mime_type'                 => $mimeType,
            'tamanho'                   => $tamanho,
        ]);
    }

    public function salvarConteudo(
        string $conteudo,
        string $nomeOriginal,
        Requerimento $requerimento,
        ?HistoricoRequerimento $historico = null,
        ?Usuario $usuario = null,
        ?string $nomeDocumento = null,
        string $mimeType = 'application/pdf'
    ): ?DocumentoRequerimento {
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION) ?: 'bin');
        $nomeArquivoArmazenado = Str::random(20) . '_' . Str::slug(pathinfo($nomeOriginal, PATHINFO_FILENAME)) . '.' . $extensao;
        $caminho = 'documentos_requerimentos/' . $requerimento->id . '/' . $nomeArquivoArmazenado;

        if (!Storage::disk('local')->put($caminho, $conteudo)) {
            return null;
        }

        return DocumentoRequerimento::create([
            'requerimento_id'           => $requerimento->id,
            'historico_requerimento_id' => $historico?->id,
            'user_id'                   => $usuario?->id ?? auth()->id(),
            'nome_documento'            => $nomeDocumento ?: $nomeOriginal,
            'nome_original'             => $nomeOriginal,
            'caminho'                   => $caminho,
            'extensao'                  => $extensao,
            'mime_type'                 => $mimeType,
            'tamanho'                   => strlen($conteudo),
        ]);
    }

    /**
     * Salva múltiplos arquivos com o mesmo título / classificação.
     */
    public function salvarArquivos(
        Requerimento $requerimento,
        ?HistoricoRequerimento $historico,
        array $arquivos,
        ?Usuario $usuario = null,
        ?string $titulo = null
    ): array {
        $salvos = [];
        foreach ($arquivos as $arquivo) {
            if ($arquivo instanceof UploadedFile && $arquivo->isValid()) {
                $doc = $this->salvarArquivo($arquivo, $requerimento, $historico, $usuario, $titulo);
                if ($doc) {
                    $salvos[] = $doc;
                }
            }
        }
        return $salvos;
    }

    /**
     * Salva os documentos iniciais enviados na criação do requerimento:
     * - Documentos obrigatórios (mapeados por ID do DocumentoAssunto)
     * - Anexos complementares
     */
    public function salvarDocumentosIniciais(
        Requerimento $requerimento,
        ?HistoricoRequerimento $historico,
        array $documentosInput = [],
        array $arquivosComplementares = [],
        ?Usuario $usuario = null
    ): array {
        $salvos = [];

        // 1. Documentos vinculados ao assunto
        foreach ($documentosInput as $docAssuntoId => $arquivo) {
            if ($arquivo instanceof UploadedFile && $arquivo->isValid()) {
                $nomeDoc = 'Documento Obrigatório';
                if (is_numeric($docAssuntoId)) {
                    $docAssunto = DocumentoAssunto::find($docAssuntoId);
                    if ($docAssunto && !empty($docAssunto->nome)) {
                        $nomeDoc = $docAssunto->nome;
                    }
                }

                $doc = $this->salvarArquivo($arquivo, $requerimento, $historico, $usuario, $nomeDoc);
                if ($doc) {
                    $salvos[] = $doc;
                }
            }
        }

        // 2. Anexos complementares
        foreach ($arquivosComplementares as $arquivo) {
            if ($arquivo instanceof UploadedFile && $arquivo->isValid()) {
                $doc = $this->salvarArquivo($arquivo, $requerimento, $historico, $usuario, 'Anexo Complementar');
                if ($doc) {
                    $salvos[] = $doc;
                }
            }
        }

        return $salvos;
    }
}
