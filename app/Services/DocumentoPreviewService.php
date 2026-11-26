<?php

namespace App\Services;

use App\Models\DocumentoRequerimento;
use App\Services\Pdf\ImagePageProcessor;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Process\Exception\ProcessStartFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class DocumentoPreviewService
{
    public function __construct(private ImagePageProcessor $imagePageProcessor)
    {
    }

    public function obterCaminhoPdf(DocumentoRequerimento $documento): string
    {
        $disco = Storage::disk('local');
        if (!$disco->exists($documento->caminho)) {
            throw new HttpException(404, 'O arquivo solicitado não foi encontrado no servidor.');
        }

        $caminhoOriginal = $disco->path($documento->caminho);
        $extensao = strtolower($documento->extensao ?: pathinfo($documento->nome_original, PATHINFO_EXTENSION));

        if ($extensao === 'pdf') {
            $this->validarCabecalhoPdf($caminhoOriginal);

            return $caminhoOriginal;
        }

        if (!in_array($extensao, DocumentoRequerimento::EXTENSOES_VISUALIZAVEIS, true)) {
            throw new HttpException(415, 'Este formato não possui conversão de visualização disponível. Baixe o arquivo original.');
        }

        $versaoDocumento = $documento->updated_at?->getTimestamp() ?? 0;
        $caminhoPreview = "documentos_requerimentos/previews/{$documento->id}-{$versaoDocumento}.pdf";
        if ($disco->exists($caminhoPreview)) {
            return $disco->path($caminhoPreview);
        }

        $conteudoPdf = in_array($extensao, DocumentoRequerimento::EXTENSOES_IMAGEM, true)
            ? $this->converterImagem($caminhoOriginal)
            : $this->converterComLibreOffice($caminhoOriginal, $extensao, $documento->id);

        $this->validarConteudoPdf($conteudoPdf);

        if (!$disco->put($caminhoPreview, $conteudoPdf)) {
            throw new HttpException(500, 'Não foi possível salvar a versão PDF para visualização.');
        }

        return $disco->path($caminhoPreview);
    }

    private function converterImagem(string $caminhoOriginal): string
    {
        $conteudoImagem = @file_get_contents($caminhoOriginal);
        $imagem = $conteudoImagem !== false ? @imagecreatefromstring($conteudoImagem) : false;
        if (!$imagem) {
            throw new HttpException(415, 'A imagem anexada está inválida ou usa um formato não suportado.');
        }
        imagedestroy($imagem);

        $pdf = new Fpdi();
        $this->imagePageProcessor->addImageAsPage($pdf, $caminhoOriginal);

        return $pdf->Output('S');
    }

    private function converterComLibreOffice(string $caminhoOriginal, string $extensao, int|string|null $documentoId): string
    {
        $executavel = config('services.libreoffice.binary', 'soffice');
        if (!is_string($executavel) || trim($executavel) === '') {
            throw new HttpException(503, 'A conversão para PDF está indisponível: configure o LibreOffice no servidor.');
        }

        $diretorioTemporario = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'secamb-preview-' . Str::uuid();
        if (!@mkdir($diretorioTemporario, 0700, true) && !is_dir($diretorioTemporario)) {
            throw new HttpException(500, 'Não foi possível preparar a conversão do documento.');
        }

        try {
            $arquivoEntrada = $diretorioTemporario . DIRECTORY_SEPARATOR . 'documento.' . $extensao;
            if (!@copy($caminhoOriginal, $arquivoEntrada)) {
                throw new HttpException(500, 'Não foi possível preparar o arquivo para conversão.');
            }

            $profilePath = $diretorioTemporario . DIRECTORY_SEPARATOR . 'profile';
            $profileUri = $this->criarFileUri($profilePath);
            $processo = new Process([
                $executavel,
                '--headless',
                '--nologo',
                '--nodefault',
                '--nofirststartwizard',
                '-env:UserInstallation=' . $profileUri,
                '--convert-to',
                'pdf',
                '--outdir',
                $diretorioTemporario,
                $arquivoEntrada,
            ]);
            $processo->setTimeout(120);

            try {
                $processo->run();
            } catch (ProcessStartFailedException $exception) {
                Log::error('LibreOffice não pôde iniciar a conversão de documento.', [
                    'documento_id' => $documentoId,
                ]);
                throw new HttpException(503, 'A conversão para PDF está indisponível: instale e configure o LibreOffice no servidor.', $exception);
            } catch (ProcessTimedOutException $exception) {
                Log::warning('A conversão do documento excedeu o tempo limite.', [
                    'documento_id' => $documentoId,
                ]);
                throw new HttpException(504, 'A conversão do documento excedeu o tempo limite. Baixe o arquivo original.', $exception);
            }

            $arquivoPdf = $diretorioTemporario . DIRECTORY_SEPARATOR . 'documento.pdf';
            if (!$processo->isSuccessful() || !is_file($arquivoPdf)) {
                Log::warning('LibreOffice não conseguiu converter o documento para PDF.', [
                    'documento_id' => $documentoId,
                    'exit_code' => $processo->getExitCode(),
                ]);
                throw new HttpException(415, 'Este documento não pôde ser convertido para visualização. Baixe o arquivo original.');
            }

            $conteudoPdf = @file_get_contents($arquivoPdf);
            if ($conteudoPdf === false) {
                throw new HttpException(500, 'Não foi possível ler o PDF convertido.');
            }

            return $conteudoPdf;
        } finally {
            File::deleteDirectory($diretorioTemporario);
        }
    }

    private function criarFileUri(string $caminho): string
    {
        $caminho = str_replace('\\', '/', $caminho);
        if (PHP_OS_FAMILY === 'Windows') {
            $caminho = '/' . ltrim($caminho, '/');
        }

        $segmentos = array_map('rawurlencode', explode('/', $caminho));
        $uri = 'file://' . implode('/', $segmentos);

        return preg_replace('/^file:\/\/\/([A-Za-z])%3A/', 'file:///$1:', $uri) ?? $uri;
    }

    private function validarCabecalhoPdf(string $caminho): void
    {
        $arquivo = @fopen($caminho, 'rb');
        $cabecalho = $arquivo ? fread($arquivo, 5) : false;
        if ($arquivo) {
            fclose($arquivo);
        }

        if ($cabecalho !== '%PDF-') {
            throw new HttpException(415, 'O arquivo PDF anexado está inválido.');
        }
    }

    private function validarConteudoPdf(string $conteudo): void
    {
        if (!str_starts_with($conteudo, '%PDF-')) {
            throw new HttpException(415, 'Não foi possível gerar uma visualização PDF válida para este arquivo.');
        }
    }
}
