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
use Illuminate\Support\Facades\Mail;

/**
 * Processo de Licenciamento Ambiental - SECAMB
 * Prefeitura Municipal de Seabra / Secretaria Municipal de Meio Ambiente
 *
 * Ciclo de vida do status:
 *   Novo → Em Atendimento → Devolvido ⟳ (cidadão responde) → Finalizado
 *                        → Indeferido (encerrado negativamente)
 *   Devolvido sem resposta em 90 dias → Expirado
 */
#[ObservedBy([RequerimentoObserver::class])]
class Requerimento extends Model
{
    protected $table = 'requerimentos';

    // ─── Status do processo ───────────────────────────────────────────────────
    public const STATUS_NOVO           = 'Novo';
    public const STATUS_EM_ATENDIMENTO = 'Em Atendimento';
    public const STATUS_DEVOLVIDO      = 'Devolvido';
    public const STATUS_FINALIZADO     = 'Finalizado';
    public const STATUS_INDEFERIDO     = 'Indeferido';
    public const STATUS_EXPIRADO       = 'Expirado';

    protected $fillable = [
        // ─── Identificação ───────────────────────────────────────────────────
        'numero_protocolo',       // Gerado automaticamente na abertura

        // ─── Partes envolvidas ───────────────────────────────────────────────
        'usuario_id',             // Cidadão requerente (Pessoa Física ou Jurídica)
        'empreendimento_id',      // Empreendimento para o qual se solicita a licença
        'setor_id',               // Secretaria/Setor da Prefeitura responsável pela análise
        'setor_retorno_id',       // Setor de destino no encaminhamento interno
        'tecnico_responsavel_id', // Servidor responsável pela análise técnica

        // ─── Tipo e descrição ────────────────────────────────────────────────
        'objetoDoRequerimento',   // Tipo/assunto do requerimento
        'assunto_requerimento_id',// Tipo de licença/assunto cadastrado no painel admin
        'tipo_processo',          // Descrição textual do tipo (LP, LI, LO, etc.)
        'descricao',              // Detalhamento livre do processo

        // ─── Controle de tramitação ──────────────────────────────────────────
        'status',                 // Ver constantes STATUS_* acima
        'observacao_analise',     // Parecer técnico / justificativa de devolução/indeferimento
        'data_devolucao',         // Data em que o processo foi devolvido ao cidadão
    ];

    protected $casts = [
        'data_devolucao' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /** Cidadão que abriu o processo. */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
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
        return $this->status ?: self::STATUS_NOVO;
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

    /**
     * Verifica se o processo expirou (devolvido há mais de 90 dias sem resposta).
     * Se expirado, atualiza o status automaticamente.
     */
    public function verificarExpiracao(): bool
    {
        if ($this->status === self::STATUS_DEVOLVIDO && $this->data_devolucao) {
            if ($this->data_devolucao->addDays(90)->isPast()) {
                $this->update(['status' => self::STATUS_EXPIRADO]);
                return true;
            }
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
        $emailsSetor = array_values(array_unique(array_filter(array_map('trim', $emailsSetor))));

        $emailsCidadao = array_values(array_unique(array_filter(array_map('trim', [$cidadao?->email]))));

        $to = in_array($remetente, ['setor', 'prefeitura'], true) ? $emailsCidadao : $emailsSetor;

        if (!empty($to)) {
            Mail::to($to)->send(
                new AtualizacaoRequerimentoMail($this, $mensagem, $remetente, $arquivos, $solicitaNovoDocumento)
            );
        }
    }
}
