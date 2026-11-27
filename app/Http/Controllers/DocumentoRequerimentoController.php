<?php

namespace App\Http\Controllers;

use App\Models\DocumentoRequerimento;
use App\Services\DocumentoPreviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DocumentoRequerimentoController extends Controller
{
    /**
     * Valida se o usuário autenticado tem permissão para acessar o documento.
     */
    private function autorizarAcesso(DocumentoRequerimento $documento): void
    {
        $usuario = auth()->user();
        if (!$usuario) {
            abort(401, 'Usuário não autenticado.');
        }

        $requerimento = $documento->requerimento;
        if (!$requerimento) {
            abort(404, 'Requerimento associado não encontrado.');
        }

        // 1. Administrador tem acesso irrestrito
        if ($usuario->isAdmin()) {
            return;
        }

        // 2. Aluno proprietário do requerimento
        if ($usuario->podeAcompanharRequerimento($requerimento)) {
            return;
        }

        // 3. Servidor responsável pelo setor ou com papel de servidor/professor
        if ($usuario->ehResponsavelDoSetor($requerimento->setor_id) || $usuario->isServidor()) {
            return;
        }

        abort(403, 'Você não possui permissão para visualizar ou baixar este documento.');
    }

    /**
     * Visualização inline do arquivo (para iframes, imagens ou nova aba).
     */
    public function visualizar(DocumentoRequerimento $documento, DocumentoPreviewService $previewService)
    {
        $this->autorizarAcesso($documento);

        if (!Storage::disk('local')->exists($documento->caminho)) {
            abort(404, 'O arquivo solicitado não foi encontrado no servidor.');
        }

        $caminhoPdf = $previewService->obterCaminhoPdf($documento);
        $nomeBase = pathinfo($documento->nome_original, PATHINFO_FILENAME);
        $nomePdf = Str::slug($nomeBase ?: 'documento') . '.pdf';

        return response()->file($caminhoPdf, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . rawurlencode($nomePdf) . '"',
            'Cache-Control'       => 'private, max-age=3600',
        ]);
    }

    /**
     * Download direto do arquivo com o nome original.
     */
    public function baixar(DocumentoRequerimento $documento)
    {
        $this->autorizarAcesso($documento);

        if (!Storage::disk('local')->exists($documento->caminho)) {
            abort(404, 'O arquivo solicitado não foi encontrado no servidor.');
        }

        return Storage::disk('local')->download($documento->caminho, $documento->nome_original);
    }
}
