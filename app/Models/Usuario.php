<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Usuário do SECAMB - Prefeitura Municipal de Seabra
 *
 * Representa dois perfis distintos:
 *  - Cidadão (Pessoa Física ou Jurídica) que solicita licenciamentos.
 *  - Servidor/Técnico Municipal que analisa e tramita os processos.
 *
 * Endereço do cidadão é armazenado na tabela `enderecos` (relacionamento HasOne).
 * Endereço do empreendimento é armazenado diretamente na tabela `empreendimentos`.
 */
class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        // Identificação
        'tipo_registro',   // 'fisica' | 'juridica'
        'nome',            // Nome Completo (PF) ou Nome Fantasia (PJ)
        'razao_social',    // Razão Social (PJ)
        'cpf',             // CPF — somente Pessoa Física
        'cnpj',            // CNPJ — somente Pessoa Jurídica

        // Contato
        'email',           // E-mail principal (login + notificações)
        'telefone',        // Telefone fixo (opcional)
        'celular',         // Celular (opcional)

        // Autenticação
        'password',

        // Login Social (Google OAuth)
        'google_id',       // ID retornado pelo Google
        'avatar',          // URL da foto do perfil Google

        // Perfil de acesso
        'role',            // 'cidadao' | 'servidor' | 'admin'
        'ativo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /** Verifica se o usuário foi criado via login social (sem senha local). */
    public function hasLoginSocial(): bool
    {
        return ! is_null($this->google_id);
    }

    /*
    |--------------------------------------------------------------------------
    | PERFIS DE ACESSO
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Servidor ou admin da Prefeitura têm acesso aos painéis de gestão. */
    public function isServidor(): bool
    {
        return in_array(strtolower(trim((string) $this->role)), ['servidor', 'tecnico', 'admin'], true);
    }

    /** Cidadão que abre processos de licenciamento. */
    public function isCidadao(): bool
    {
        return $this->role === 'cidadao';
    }

    public function isPessoaFisica(): bool
    {
        return $this->tipo_registro === 'fisica';
    }

    public function isPessoaJuridica(): bool
    {
        return $this->tipo_registro === 'juridica';
    }

    /** Formatação do tipo de pessoa (Pessoa Física ou Pessoa Jurídica). */
    public function getTipoPessoaFormatadoAttribute(): string
    {
        return match ($this->tipo_registro) {
            'fisica' => 'Pessoa Física',
            'juridica' => 'Pessoa Jurídica',
            default => '—',
        };
    }

    /** Compatibilidade para chamadas legadas de tipo_processo_formatado no usuário. */
    public function getTipoProcessoFormatadoAttribute(): string
    {
        return $this->tipo_pessoa_formatado;
    }

    /** Documento principal de identificação (CPF ou CNPJ). */
    public function getDocumentoIdentificacaoAttribute(): ?string
    {
        return $this->isPessoaJuridica() ? $this->cnpj : $this->cpf;
    }

    /*
    |--------------------------------------------------------------------------
    | EMPREENDIMENTOS
    |--------------------------------------------------------------------------
    */

    /**
     * Empreendimentos em que o cidadão figura como representante legal
     * (com aceite do Termo de Declaração do Representante Legal).
     */
    public function empreendimentos(): BelongsToMany
    {
        return $this->belongsToMany(
            Empreendimento::class,
            'empreendimento_usuario',
            'usuario_id',
            'empreendimento_id'
        )->withPivot(['termo_aceito', 'termo_aceito_em', 'status'])->withTimestamps();
    }

    /**
     * Empreendimentos cujo cadastro inicial foi realizado por este usuário.
     */
    public function empreendimentosCadastrados(): HasMany
    {
        return $this->hasMany(Empreendimento::class, 'usuario_id');
    }

    /**
     * Retorna a query de todos os empreendimentos vinculados ao usuário (criados ou representados).
     */
    public function todosEmpreendimentos()
    {
        return Empreendimento::where(function ($q) {
            $q->where('usuario_id', $this->id)
              ->orWhereHas('representantes', function ($sub) {
                  $sub->where('usuario_id', $this->id);
              });
        });
    }

    /**
     * Verifica se o usuário tem permissão para gerenciar/editar o empreendimento.
     */
    public function podeGerenciarEmpreendimento(Empreendimento $empreendimento): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ((int) $empreendimento->usuario_id === (int) $this->id) {
            return true;
        }

        return $this->empreendimentos()->where('empreendimentos.id', $empreendimento->id)->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESSOS DE LICENCIAMENTO
    |--------------------------------------------------------------------------
    */

    /** Processos (requerimentos) abertos por este cidadão. */
    public function processos(): HasMany
    {
        return $this->hasMany(Requerimento::class, 'usuario_id');
    }

    /** Requerimentos abertos pelo usuário (método utilizado pelos controllers). */
    public function requerimentos(): HasMany
    {
        return $this->processos();
    }

    /** Processos em que este servidor é o técnico responsável pela análise. */
    public function processosEmAnalise(): HasMany
    {
        return $this->hasMany(Requerimento::class, 'tecnico_responsavel_id');
    }

    /** Requerimentos em análise pelo servidor. */
    public function requerimentosEmAnalise(): HasMany
    {
        return $this->processosEmAnalise();
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSABILIDADE DE SETOR / SECRETARIA MUNICIPAL
    |--------------------------------------------------------------------------
    */

    /** Setores/Secretarias municipais vinculados a este servidor como responsável. */
    public function setoresSobResponsabilidade(): BelongsToMany
    {
        return $this->belongsToMany(Setor::class, 'setor_responsavel', 'usuario_id', 'setor_id');
    }

    public function ehResponsavel(): bool
    {
        return $this->setoresSobResponsabilidade()->exists();
    }

    public function ehResponsavelDoSetor(int $setorId): bool
    {
        return $this->setoresSobResponsabilidade()->where('setores.id', $setorId)->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | OUTROS RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /** Histórico de tramitações em que este usuário atuou (como autor de despacho). */
    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoRequerimento::class, 'user_id');
    }

    /** Endereço residencial / da sede do cidadão (tabela enderecos). */
    public function endereco()
    {
        return $this->hasOne(Endereco::class, 'usuario_id');
    }
}
