<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Endereço residencial/sede do Cidadão - SECAMB
 *
 * Armazena o endereço do cidadão (Pessoa Física ou Jurídica) vinculado
 * à tabela `usuarios`. Possui apenas relacionamento com Usuario.
 *
 * ATENÇÃO: O endereço do Empreendimento é armazenado diretamente
 * na tabela `empreendimentos` e NÃO usa esta tabela.
 */
class Endereco extends Model
{
    protected $table = 'enderecos';

    protected $fillable = [
        'usuario_id',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
    ];

    /** Cidadão ao qual este endereço pertence. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACESSORES
    |--------------------------------------------------------------------------
    */

    /** Endereço formatado em linha única legível. */
    public function getFormatadoAttribute(): string
    {
        $partes = [];

        if (!empty($this->rua)) {
            $linha = $this->rua;
            if (!empty($this->numero) && !str_contains($this->rua, $this->numero)) {
                $linha .= ', ' . $this->numero;
            }
            if (!empty($this->complemento)) {
                $linha .= ' - ' . $this->complemento;
            }
            $partes[] = $linha;
        } elseif (!empty($this->numero)) {
            $partes[] = $this->numero;
        }

        if (!empty($this->bairro)) {
            $partes[] = $this->bairro;
        }

        $cidadeEstado = array_filter([
            $this->cidade,
            $this->estado ? strtoupper($this->estado) : null,
        ]);
        if ($cidadeEstado) {
            $partes[] = implode(' - ', $cidadeEstado);
        }

        if (!empty($this->cep)) {
            $partes[] = 'CEP: ' . $this->cep;
        }

        return !empty($partes) ? implode(', ', $partes) : '';
    }

    public function __toString(): string
    {
        return $this->formatado;
    }
}
