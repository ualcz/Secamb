<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Empreendimento - SECAMB (Prefeitura Municipal de Seabra)
 *
 * Representa empresas, indústrias, comércios e empreendimentos sujeitos
 * ao controle e fiscalização ambiental municipal.
 *
 * O endereço do empreendimento é armazenado diretamente nesta tabela
 * (colunas endereco, bairro, cep, cidade, estado), pois é parte central
 * do formulário de licenciamento e não muda com frequência.
 */
class Empreendimento extends Model
{
    protected $table = 'empreendimentos';

    protected $fillable = [
        // Identificação
        'usuario_id',          // Usuário que cadastrou o empreendimento (criador)
        'nome',                // Nome Fantasia ou Razão Social
        'cnpj',                // CNPJ (null se for pessoa física/produtor rural)
        'tipo_atividade',      // Descrição da atividade econômica principal

        // Localização (fixo no empreendimento — não usa tabela enderecos)
        'endereco',            // Rua/Estrada e número
        'bairro',
        'cep',
        'cidade',              // Padrão: Seabra
        'estado',              // Padrão: BA

        // Dados ambientais do empreendimento (Seção 1.2 do manual)
        'bacia_hidrografica',  // Bacia Hidrográfica onde está inserido
        'recurso_hidrico',     // Recurso Hídrico utilizado/impactado
        'fase_operacao',       // 'Localização' | 'Instalação' | 'Operação' | 'Não se Aplica'

        // Contato/Responsável pelo empreendimento
        'contato_nome',
        'contato_telefone',
        'contato_celular',
        'contato_email',

        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /** Usuário que realizou o cadastro inicial do empreendimento. */
    public function criador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Cidadãos que possuem representação legal cadastrada para este empreendimento.
     * Registrado na tabela pivot empreendimento_usuario com aceite do Termo.
     */
    public function representantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'empreendimento_usuario',
            'empreendimento_id',
            'usuario_id'
        )->withPivot(['termo_aceito', 'termo_aceito_em', 'status'])->withTimestamps();
    }

    /**
     * Processos de Licenciamento Ambiental vinculados a este empreendimento.
     */
    public function processos(): HasMany
    {
        return $this->hasMany(Requerimento::class, 'empreendimento_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACESSORES
    |--------------------------------------------------------------------------
    */

    /** Endereço do empreendimento formatado em linha única. */
    public function getEnderecoCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->endereco,
            $this->bairro,
            $this->cidade && $this->estado ? "{$this->cidade} - {$this->estado}" : ($this->cidade ?: $this->estado),
            $this->cep ? "CEP {$this->cep}" : null,
        ]);

        return implode(', ', $partes);
    }
}
