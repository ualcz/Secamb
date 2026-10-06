<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tipos de Licenças e Autorizações Ambientais - SECAMB
 *
 * Exemplos: Licença Prévia (LP), Licença de Instalação (LI),
 * Licença de Operação (LO), Licença de Regularização (LRA),
 * Autorização Ambiental, Renovação de Licença.
 *
 * ATENÇÃO: O campo tipo_processo em Requerimento é uma string descritiva
 * e não usa FK para esta tabela. Esta tabela serve como catálogo de referência
 * para o administrador e para popular listas de seleção no formulário.
 */
class TipoProcesso extends Model
{
    protected $table = 'tipos_processos';

    protected $fillable = [
        'nome',            // Ex: Licença Prévia
        'sigla',           // Ex: LP
        'descricao',       // Descrição da finalidade
        'legislacao_base', // Fundamento legal municipal/estadual
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
