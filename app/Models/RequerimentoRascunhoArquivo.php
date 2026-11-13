<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequerimentoRascunhoArquivo extends Model
{
    protected $table = 'requerimento_rascunho_arquivos';

    protected $fillable = [
        'rascunho_id',
        'campo',
        'indice',
        'nome_original',
        'caminho',
        'mime_type',
        'tamanho',
    ];

    public function rascunho(): BelongsTo
    {
        return $this->belongsTo(RequerimentoRascunho::class, 'rascunho_id');
    }
}
