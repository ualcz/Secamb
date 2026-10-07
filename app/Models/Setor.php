<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Setor extends Model
{
    protected $table = 'setores';

    protected $fillable = [
        'setor_sigla',
        'setor_nome',
        'email',
        'titulo',
        'ativo',
        'is_interno',
    ];

    protected $casts = [
        'ativo'      => 'boolean',
        'is_interno' => 'boolean',
    ];

    public function isInterno(): bool
    {
        return (bool) $this->is_interno;
    }

    public function isPublico(): bool
    {
        return !$this->isInterno();
    }

    public function scopePublicos($query)
    {
        return $query->where('is_interno', false)->where('ativo', true);
    }

    public function scopeInternos($query)
    {
        return $query->where('is_interno', true);
    }

    public function assuntos()
    {
        return $this->hasMany(AssuntoRequerimento::class, 'setor_id')->orderBy('ordem');
    }

    public function assuntosAtivos()
    {
        return $this->hasMany(AssuntoRequerimento::class, 'setor_id')
            ->where('ativo', true)
            ->orderBy('ordem');
    }

    public function requerimentos()
    {
        return $this->hasMany(Requerimento::class, 'setor_id');
    }

    public function responsaveis(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'setor_responsavel', 'setor_id', 'usuario_id');
    }

    /**
     * Retorna os setores formatados para uso nos controllers e views.
     * Por padrão retorna apenas setores públicos para criação de requerimentos.
     */
    public static function obterSetoresFormatados(bool $apenasPublicos = true): array
    {
        try {
            // Incluído 'responsaveis' no Eager Loading para evitar queries N+1
            $query = static::with(['responsaveis', 'assuntosAtivos.documentos'])
                ->where('ativo', true);

            if ($apenasPublicos) {
                $query->where('is_interno', false);
            }

            $setoresBanco = $query->get();

            if ($setoresBanco->isEmpty()) {
                return [];
            }

            $resultado = [];
            foreach ($setoresBanco as $mod) {
                $objetos = [];
                $assuntosDetalhes = [];

                foreach ($mod->assuntosAtivos as $assunto) {
                    $chave = str_pad((string) $assunto->id, 2, '0', STR_PAD_LEFT);
                    $objetos[$chave] = $assunto->descricao;
                    $assuntosDetalhes[] = [
                        'id'                     => $assunto->id,
                        'descricao'              => $assunto->descricao,
                        'observacao'             => $assunto->observacao,
                        'link_norma'             => $assunto->link_norma,
                        'ordem'                  => $assunto->ordem,
                        'documentos_obrigatorios' => $assunto->documentos
                            ->map(fn($d) => [
                                'id'            => $d->id,
                                'nome'          => $d->nome,
                                'descricao'     => $d->descricao,
                                'link_modelo'   => $d->link_modelo,
                                'obrigatorio'   => $d->obrigatorio,
                                'tipos_aceitos' => $d->tipos_aceitos,
                            ])->values()->toArray(),
                    ];
                }

                $resultado[$mod->id] = [
                    'id'            => $mod->id,
                    'setor_sigla'   => $mod->setor_sigla,
                    'setor_nome'    => $mod->setor_nome,
                    'email'         => $mod->email,
                    'titulo'        => $mod->titulo,
                    'is_interno'    => (bool) $mod->is_interno,
                    'responsaveis'  => $mod->responsaveis->map(fn($r) => [
                        'id'    => $r->id,
                        'nome'  => $r->nome ?? $r->name,
                        'email' => $r->email,
                    ])->toArray(),
                    'objetos'           => $objetos,
                    'assuntos_detalhes' => $assuntosDetalhes,
                ];
            }

            return $resultado;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Alias para compatibilidade com chamadas legadas
     */
    public static function obterModelosFormatados(bool $apenasPublicos = true): array
    {
        return static::obterSetoresFormatados($apenasPublicos);
    }
}
