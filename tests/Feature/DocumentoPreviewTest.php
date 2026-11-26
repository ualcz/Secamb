<?php

use App\Models\DocumentoRequerimento;
use App\Services\DocumentoPreviewService;
use Illuminate\Support\Facades\Storage;

test('image preview is generated as pdf while keeping the original file', function () {
    Storage::fake('local');

    $imagem = imagecreatetruecolor(8, 8);
    ob_start();
    imagepng($imagem);
    $conteudoOriginal = ob_get_clean();
    imagedestroy($imagem);

    $caminhoOriginal = 'documentos_requerimentos/12/foto.png';
    Storage::disk('local')->put($caminhoOriginal, $conteudoOriginal);

    $documento = new DocumentoRequerimento([
        'caminho' => $caminhoOriginal,
        'extensao' => 'png',
        'nome_original' => 'foto.png',
    ]);
    $documento->setAttribute('id', 12);
    $documento->setAttribute('updated_at', now());

    $caminhoPdf = app(DocumentoPreviewService::class)->obterCaminhoPdf($documento);

    expect(substr(file_get_contents($caminhoPdf), 0, 5))->toBe('%PDF-')
        ->and(Storage::disk('local')->get($caminhoOriginal))->toBe($conteudoOriginal)
        ->and((new DocumentoRequerimento(['extensao' => 'docx']))->isVisualizavel())->toBeTrue();
});
