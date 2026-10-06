<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tipos de Licenças disponíveis para seleção no formulário - SECAMB
 *
 * Categorias de processos ambientais municipais da Prefeitura de Seabra:
 *  LP  - Licença Prévia
 *  LI  - Licença de Instalação
 *  LO  - Licença de Operação
 *  LRA - Licença de Regularização Ambiental
 *  AA  - Autorização Ambiental
 *  RL  - Renovação de Licença
 *
 * Cada setor/secretaria define quais tipos de processo aceita,
 * configurado via AssuntoRequerimento no painel administrativo.
 */
class AssuntoRequerimento extends Model
{
    protected $table = 'assuntos_requerimentos';

    protected $fillable = [
        'setor_id',    // Setor/Secretaria da Prefeitura que gerenicia este tipo de processo
        'descricao',   // Nome do tipo de processo (ex: "Licença de Operação")
        'observacao',  // Instruções adicionais para o cidadão
        'link_norma',  // URL da legislação / norma municipal
        'ordem',       // Ordem de exibição no formulário
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /** Secretaria/Setor da Prefeitura responsável por este tipo de processo. */
    public function setor()
    {
        return $this->belongsTo(Setor::class, 'setor_id');
    }

    /** Documentos exigidos pelo setor para este tipo de processo. */
    public function documentos()
    {
        return $this->hasMany(DocumentoAssunto::class, 'assunto_requerimento_id');
    }

    /** Apenas documentos marcados como obrigatórios. */
    public function documentosObrigatorios()
    {
        return $this->hasMany(DocumentoAssunto::class, 'assunto_requerimento_id')
            ->where('obrigatorio', true);
    }
}
