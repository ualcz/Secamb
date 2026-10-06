<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Representação de Empreendimento - SECAMB
 * Registra a autorização de representação legal e aceite do termo pelo cidadão.
 */
class EmpreendimentoUsuario extends Pivot
{
    protected $table = 'empreendimento_usuario';

    public $incrementing = true;

    protected $fillable = [
        'empreendimento_id',
        'usuario_id',
        'termo_aceito',
        'termo_aceito_em',
        'status', // 'ativo', 'pendente', 'revogado'
    ];

    protected $casts = [
        'termo_aceito' => 'boolean',
        'termo_aceito_em' => 'datetime',
    ];

    public function empreendimento(): BelongsTo
    {
        return $this->belongsTo(Empreendimento::class, 'empreendimento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
