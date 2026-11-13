<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequerimentoRascunho extends Model
{
    protected $table = 'requerimento_rascunhos';

    protected $fillable = [
        'usuario_id',
        'setor_id',
        'objeto_chave',
        'campos',
        'etapa',
    ];

    protected $casts = [
        'campos' => 'array',
        'etapa' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'setor_id');
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(RequerimentoRascunhoArquivo::class, 'rascunho_id');
    }
}
