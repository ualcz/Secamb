<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoRequerimento extends Model
{
    protected $table = 'documentos_requerimentos';

    protected $fillable = [
        'requerimento_id',
        'historico_requerimento_id',
        'user_id',
        'nome_documento',
        'nome_original',
        'caminho',
        'extensao',
        'mime_type',
        'tamanho',
    ];

    public function requerimento(): BelongsTo
    {
        return $this->belongsTo(Requerimento::class, 'requerimento_id');
    }

    public function processo(): BelongsTo
    {
        return $this->requerimento();
    }

    public function historico(): BelongsTo
    {
        return $this->belongsTo(HistoricoRequerimento::class, 'historico_requerimento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    /**
     * Retorna o título legível do documento (nome_documento ou nome_original).
     */
    public function getTituloAttribute(): string
    {
        return !empty($this->nome_documento) ? $this->nome_documento : $this->nome_original;
    }

    /**
     * Formata o tamanho do arquivo em B, KB ou MB.
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = (int) ($this->tamanho ?? 0);
        if ($bytes <= 0) {
            return '0 B';
        }
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . ($unidades[$i] ?? 'B');
    }

    /**
     * Verifica se o arquivo é um PDF.
     */
    public function isPdf(): bool
    {
        $ext = strtolower($this->extensao ?? pathinfo($this->nome_original, PATHINFO_EXTENSION));
        return $ext === 'pdf' || str_contains(strtolower((string) $this->mime_type), 'pdf');
    }

    /**
     * Verifica se o arquivo é uma imagem suportada no navegador.
     */
    public function isImagem(): bool
    {
        $ext = strtolower($this->extensao ?? pathinfo($this->nome_original, PATHINFO_EXTENSION));
        return in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'], true)
            || str_starts_with(strtolower((string) $this->mime_type), 'image/');
    }

    /**
     * Verifica se pode ser pré-visualizado no navegador.
     */
    public function isVisualizavel(): bool
    {
        return $this->isPdf() || $this->isImagem() || str_contains(strtolower((string) $this->mime_type), 'text/');
    }
}
