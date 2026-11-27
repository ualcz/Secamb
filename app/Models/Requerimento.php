<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use App\Models\Empreendimento;
use App\Models\Setor;
use App\Models\AssuntoRequerimento;
use App\Models\HistoricoRequerimento;
use App\Models\DocumentoRequerimento;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\RequerimentoObserver;
use App\Mail\AtualizacaoRequerimentoMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Processo de Licenciamento Ambiental - SECAMB
 * Prefeitura Municipal de Seabra / Secretaria Municipal de Meio Ambiente
 *
 * Ciclo de vida do status:
 *   Protocolado → Em análise → Em análise técnica → Análise concluída
 *   Análise concluída → vistoria opcional → Deferido → Licença emitida
 *                                      ↘ Indeferido / Encerrado
 *   Aguardando complementação → Em análise após resposta do cidadão
 *   Sem resposta à complementação em 120 dias → Arquivado
 */
#[ObservedBy([RequerimentoObserver::class])]
class Requerimento extends Model
{
    protected $table = 'requerimentos';

    public const DECLARACAO_RESPONSABILIDADE = 'Declaro, para os devidos fins, que as informações prestadas neste requerimento são verdadeiras e de minha inteira responsabilidade, '
        . 'estando ciente de que a prestação de informações falsas poderá acarretar as sanções previstas na Lei Federal nº 9.605/1998 (Lei de Crimes Ambientais), '
        . 'no Decreto Estadual nº 14.024/2012 e na Lei Municipal nº 498/2013 de Seabra-BA, bem como a nulidade deste ato e a cassação de eventuais licenças '
        . 'concedidas com base nas informações aqui declaradas.';

    // ─── Status do processo ───────────────────────────────────────────────────
    public const STATUS_PROTOCOLADO                 = 'Protocolado';
    public const STATUS_EM_ANALISE                  = 'Em análise';
    public const STATUS_EM_ANALISE_TECNICA          = 'Em análise técnica';
    public const STATUS_ANALISE_CONCLUIDA           = 'Análise concluída';
    public const STATUS_AGUARDANDO_COMPLEMENTACAO   = 'Aguardando complementação';
    public const STATUS_AGUARDANDO_VISTORIA         = 'Aguardando vistoria';
    public const STATUS_VISTORIA_CONCLUIDA          = 'Vistoria concluída';
    public const STATUS_DEFERIDO                    = 'Deferido';
    public const STATUS_LICENCA_EMITIDA             = 'Licença emitida';
    public const STATUS_INDEFERIDO                  = 'Indeferido';
    public const STATUS_ENCERRADO                   = 'Encerrado';
    public const STATUS_ARQUIVADO_COMPLEMENTACAO    = 'Arquivado por falta de complementação';
    public const DIAS_LIMITE_COMPLEMENTACAO         = 120;
    public const STATUS_ANALISE_ATIVA = [
        self::STATUS_EM_ANALISE,
        self::STATUS_EM_ANALISE_TECNICA,
        self::STATUS_ANALISE_CONCLUIDA,
        self::STATUS_AGUARDANDO_VISTORIA,
        self::STATUS_VISTORIA_CONCLUIDA,
    ];

    public const STATUS_TERMINAIS = [
        self::STATUS_LICENCA_EMITIDA,
        self::STATUS_INDEFERIDO,
        self::STATUS_ENCERRADO,
        self::STATUS_ARQUIVADO_COMPLEMENTACAO,
    ];

    public const STATUS_DISPONIVEIS = [
        self::STATUS_PROTOCOLADO,
        self::STATUS_EM_ANALISE,
        self::STATUS_EM_ANALISE_TECNICA,
        self::STATUS_ANALISE_CONCLUIDA,
        self::STATUS_AGUARDANDO_COMPLEMENTACAO,
        self::STATUS_AGUARDANDO_VISTORIA,
        self::STATUS_VISTORIA_CONCLUIDA,
        self::STATUS_DEFERIDO,
        self::STATUS_LICENCA_EMITIDA,
        self::STATUS_INDEFERIDO,
        self::STATUS_ENCERRADO,
        self::STATUS_ARQUIVADO_COMPLEMENTACAO,
    ];

    protected $fillable = [
        // ─── Identificação ───────────────────────────────────────────────────
        'numero_protocolo',       // Gerado automaticamente na abertura

        // ─── Partes envolvidas ───────────────────────────────────────────────
        'usuario_id',             // Representante legal titular do requerimento
        'protocolado_por_id',     // Usuário que realizou o protocolo
        'empreendimento_id',      // Empreendimento para o qual se solicita a licença
        'setor_id',               // Secretaria/Setor da Prefeitura responsável pela análise
        'setor_retorno_id',       // Setor de destino no encaminhamento interno
        'tecnico_responsavel_id', // Servidor responsável pela análise técnica

        // ─── Tipo e descrição ────────────────────────────────────────────────
        'objetoDoRequerimento',   // Tipo/assunto do requerimento
        'assunto_requerimento_id',// Tipo de licença/assunto cadastrado no painel admin
        'tipo_processo',          // Descrição textual do tipo (LP, LI, LO, etc.)
        'descricao',              // Detalhamento livre do processo
        'motivo',                 // Justificativa / mensagem do requerimento
        'caracterizacao_empreendimento',

        // ─── Controle de tramitação ──────────────────────────────────────────
        'status',                 // Ver constantes STATUS_* acima
        'observacao_analise',     // Parecer técnico / justificativa de devolução/indeferimento
        'data_devolucao',         // Data em que o processo foi devolvido ao cidadão
    ];

    protected $casts = [
        'data_devolucao' => 'datetime',
        'caracterizacao_empreendimento' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /** Representante legal titular do processo. */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function protocoladoPor()
    {
        return $this->belongsTo(Usuario::class, 'protocolado_por_id');
    }

    /** Empreendimento vinculado ao processo. */
    public function empreendimento()
    {
        return $this->belongsTo(Empreendimento::class, 'empreendimento_id');
    }

    /** Setor/Secretaria da Prefeitura responsável. */
    public function setor()
    {
        return $this->belongsTo(Setor::class, 'setor_id');
    }

    /** Setor de destino em encaminhamentos internos. */
    public function setorRetorno()
    {
        return $this->belongsTo(Setor::class, 'setor_retorno_id');
    }

    /** Tipo de licença/assunto cadastrado pelo administrador. */
    public function assunto()
    {
        return $this->belongsTo(AssuntoRequerimento::class, 'assunto_requerimento_id');
    }

    /** Servidor/técnico responsável pela análise deste processo. */
    public function tecnicoResponsavel()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_responsavel_id');
    }

    /** Histórico de tramitação ordenado cronologicamente. */
    public function historicos()
    {
        return $this->hasMany(HistoricoRequerimento::class, 'requerimento_id')->oldest();
    }

    /** Documentos anexados ao processo (pelo cidadão ou pela prefeitura). */
    public function documentos()
    {
        return $this->hasMany(DocumentoRequerimento::class, 'requerimento_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACESSORES
    |--------------------------------------------------------------------------
    */

    /**
     * Tipo de processo formatado: prioriza o assunto cadastrado no admin;
     * cai de volta para o campo tipo_processo; e por último um valor padrão.
     */
    public function getTipoProcessoFormatadoAttribute(): string
    {
        return $this->assunto?->descricao
            ?: ($this->tipo_processo ?: 'Licenciamento Ambiental');
    }

    /** Status do processo visível para o cidadão. */
    public function getStatusCidadaoAttribute(): string
    {
        return $this->status ?: self::STATUS_PROTOCOLADO;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status ?: self::STATUS_PROTOCOLADO, self::STATUS_TERMINAIS, true);
    }

    /** Status que o setor pode atribuir a partir da etapa atual. */
    public function proximosStatusPossiveis(): array
    {
        return match ($this->status ?: self::STATUS_PROTOCOLADO) {
            self::STATUS_PROTOCOLADO => [
                self::STATUS_EM_ANALISE,
            ],
            self::STATUS_EM_ANALISE => [
                self::STATUS_EM_ANALISE_TECNICA,
                self::STATUS_AGUARDANDO_COMPLEMENTACAO,
                self::STATUS_ENCERRADO,
            ],
            self::STATUS_EM_ANALISE_TECNICA => [
                self::STATUS_ANALISE_CONCLUIDA,
                self::STATUS_AGUARDANDO_COMPLEMENTACAO,
                self::STATUS_ENCERRADO,
            ],
            self::STATUS_ANALISE_CONCLUIDA => [
                self::STATUS_AGUARDANDO_VISTORIA,
                self::STATUS_AGUARDANDO_COMPLEMENTACAO,
                self::STATUS_DEFERIDO,
                self::STATUS_INDEFERIDO,
                self::STATUS_ENCERRADO,
            ],
            self::STATUS_AGUARDANDO_VISTORIA => [
                self::STATUS_VISTORIA_CONCLUIDA,
                self::STATUS_AGUARDANDO_COMPLEMENTACAO,
                self::STATUS_ENCERRADO,
            ],
            self::STATUS_VISTORIA_CONCLUIDA => [
                self::STATUS_AGUARDANDO_COMPLEMENTACAO,
                self::STATUS_DEFERIDO,
                self::STATUS_INDEFERIDO,
                self::STATUS_ENCERRADO,
            ],
            self::STATUS_DEFERIDO => [
                self::STATUS_LICENCA_EMITIDA,
                self::STATUS_ENCERRADO,
            ],
            default => [],
        };
    }

    /** Sigla da secretaria/setor gestora do processo. */
    public function getSetorSiglaAttribute(): string
    {
        return $this->assunto?->setor?->setor_sigla
            ?: $this->setor?->setor_sigla
            ?: $this->setor?->setor_nome
            ?: 'SEMMA';
    }

    /** Nome completo da secretaria/setor gestora do processo. */
    public function getSetorNomeAttribute(): string
    {
        return $this->assunto?->setor?->setor_nome
            ?: $this->setor?->setor_nome
            ?: $this->setor?->setor_sigla
            ?: 'Secretaria Municipal de Meio Ambiente';
    }

    /*
    |--------------------------------------------------------------------------
    | LÓGICA DE NEGÓCIO
    |--------------------------------------------------------------------------
    */

    /** Arquiva o processo quando a complementação fica sem resposta por 120 dias. */
    public function arquivarSeComplementacaoVencida(): bool
    {
        if (
            $this->status === self::STATUS_AGUARDANDO_COMPLEMENTACAO
            && $this->data_devolucao
            && $this->data_devolucao->lte(now()->subDays(self::DIAS_LIMITE_COMPLEMENTACAO))
        ) {
            $this->update(['status' => self::STATUS_ARQUIVADO_COMPLEMENTACAO]);
            return true;
        }
        return false;
    }

    /** Assunto do e-mail de tramitação (mantém o encadeamento de thread). */
    public function emailThreadSubject(): string
    {
        $tipo = $this->tipo_processo_formatado;
        $nome = $this->usuario?->nome ?? 'Cidadão';
        return "Processo SECAMB #{$this->numero_protocolo} [{$tipo}] - {$nome}";
    }

    /**
     * Envia notificação por e-mail às partes envolvidas.
     *   - Prefeitura despacha → e-mail vai para o cidadão.
     *   - Cidadão responde   → e-mail vai para o setor da prefeitura.
     */
    public function notificarPartes(
        string $mensagem,
        string $remetente,
        array  $arquivos = [],
        bool   $solicitaNovoDocumento = false
    ): void {
        $setor   = $this->setor;
        $cidadao = $this->usuario;

        $emailsSetor = [];
        if (!empty($setor?->email)) {
            $emailsSetor = is_array($setor->email) ? $setor->email : [$setor->email];
        }
        $emailsSetor = array_values(array_unique(array_filter(
            array_map('trim', $emailsSetor),
            fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        )));

        $emailsCidadao = array_values(array_unique(array_filter(
            array_map(fn ($email) => trim((string) $email), [$cidadao?->email]),
            fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        )));

        $to = in_array($remetente, ['setor', 'prefeitura'], true) ? $emailsCidadao : $emailsSetor;

        if (!empty($to)) {
            try {
                Mail::to($to)->send(
                    new AtualizacaoRequerimentoMail($this, $mensagem, $remetente, $arquivos, $solicitaNovoDocumento)
                );
            } catch (\Throwable $e) {
                Log::warning("Falha ao enviar notificação do requerimento #{$this->numero_protocolo}: " . $e->getMessage());
            }
        }
    }
}
