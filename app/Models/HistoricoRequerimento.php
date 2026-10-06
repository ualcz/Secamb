<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de Histórico e Tramitação de Processos - SECAMB
 * Registra o andamento, despachos e pareceres da Prefeitura de Seabra e respostas do Cidadão.
 */
class HistoricoRequerimento extends Model
{
    protected $table = 'historico_requerimentos';

    protected $fillable = [
        'requerimento_id',
        'user_id',                     // Usuário autor do despacho (Servidor da Prefeitura ou Cidadão)
        'status',                      // Novo, Em Atendimento, Devolvido, Finalizado, Indeferido
        'observacao',                  // Parecer técnico / Justificativa / Resposta do cidadão
        'solicita_novo_documento',     // Indica se foi devolvido para anexar documento complementar
        'nome_documento_solicitado',   // Nome da certidão/documento exigido pelo fiscal ambiental
    ];

    protected $casts = [
        'solicita_novo_documento' => 'boolean',
    ];

    public function requerimento(): BelongsTo
    {
        return $this->belongsTo(Requerimento::class, 'requerimento_id');
    }

    public function processo(): BelongsTo
    {
        return $this->requerimento();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function autor(): BelongsTo
    {
        return $this->usuario();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoRequerimento::class, 'historico_requerimento_id');
    }
}
