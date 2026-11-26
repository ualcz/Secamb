@php
    $statusPossiveis = $requerimento->proximosStatusPossiveis();
    $processoFinalizado = $requerimento->isTerminal();
    $statusAntigo = old('status');
    $statusSelecionado = in_array($statusAntigo, $statusPossiveis, true)
        ? $statusAntigo
        : ($statusPossiveis[0] ?? '');
    $statusAtual = $requerimento->status ?: \App\Models\Requerimento::STATUS_PROTOCOLADO;
    $podeAnexarDocumento = !$processoFinalizado
        && $statusAtual !== \App\Models\Requerimento::STATUS_PROTOCOLADO;
    $solicitaComplementacao = $statusSelecionado === \App\Models\Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO;
    $statusExigeJustificativa = in_array($statusSelecionado, [
        \App\Models\Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
        \App\Models\Requerimento::STATUS_INDEFERIDO,
        \App\Models\Requerimento::STATUS_ENCERRADO,
    ], true);
    $rotuloObservacao = match ($statusSelecionado) {
        \App\Models\Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO => 'Orientação para complementação',
        \App\Models\Requerimento::STATUS_INDEFERIDO => 'Justificativa do indeferimento',
        \App\Models\Requerimento::STATUS_ENCERRADO => 'Motivo do encerramento',
        default => 'Observação (opcional)',
    };
@endphp

<section class="card-painel painel-acoes" aria-labelledby="titulo-acoes-requerimento">
    <h2 id="titulo-acoes-requerimento" class="painel-acoes-titulo">Ações do requerimento</h2>

    <div class="acao-botoes">
        @if(!$processoFinalizado && $requerimento->setor_retorno_id)
            <button type="button" class="acao-botao acao-botao-responder" data-modal-target="modal-responder-encaminhamento" aria-haspopup="dialog" aria-controls="modal-responder-encaminhamento">
                <span class="acao-botao-icone" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 016 6v1"/></svg>
                </span>
                <span class="acao-botao-texto">
                    <strong>Responder encaminhamento</strong>
                    <span>Devolver para {{ $requerimento->setorRetorno?->setor_sigla }}</span>
                </span>
                <span class="acao-botao-seta" aria-hidden="true">→</span>
            </button>
        @endif

        @if($statusPossiveis)
            <button type="button" class="acao-botao acao-botao-atender" data-modal-target="modal-atender" aria-haspopup="dialog" aria-controls="modal-atender">
                <span class="acao-botao-icone" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/><circle cx="12" cy="12" r="10"/></svg>
                </span>
                <span class="acao-botao-texto">
                    <strong>Atender requerimento</strong>
                    <span>Atualizar status e anexar documento</span>
                </span>
                <span class="acao-botao-seta" aria-hidden="true">→</span>
            </button>
        @endif

        @if($podeAnexarDocumento)
            <button type="button" class="acao-botao acao-botao-atender" data-modal-target="modal-anexar-documento" aria-haspopup="dialog" aria-controls="modal-anexar-documento">
                <span class="acao-botao-icone" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.82-2.83l8.48-8.48"/></svg>
                </span>
                <span class="acao-botao-texto">
                    <strong>Anexar documento</strong>
                    <span>Registrar juntada sem alterar o status</span>
                </span>
                <span class="acao-botao-seta" aria-hidden="true">→</span>
            </button>
        @endif

        @if(!$processoFinalizado)
            <button type="button" class="acao-botao acao-botao-encaminhar" data-modal-target="modal-encaminhar" aria-haspopup="dialog" aria-controls="modal-encaminhar">
                <span class="acao-botao-icone" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15m-6-6 6 6-6 6"/></svg>
                </span>
                <span class="acao-botao-texto">
                    <strong>Encaminhar</strong>
                    <span>Enviar para outro setor ou solicitar despacho</span>
                </span>
                <span class="acao-botao-seta" aria-hidden="true">→</span>
            </button>
        @endif
    </div>

    @if(!$statusPossiveis)
        <p class="acao-sem-transicao">Este processo não possui transições de status disponíveis nesta etapa.</p>
    @endif

    @if(!$processoFinalizado && $requerimento->setor_retorno_id)
        @include('setor.requerimentos.partials.modals.responder-encaminhamento')
    @endif
    @if($statusPossiveis)
        @include('setor.requerimentos.partials.modals.atender')
    @endif
    @if($podeAnexarDocumento)
        @include('setor.requerimentos.partials.modals.anexar-documento')
    @endif
    @if(!$processoFinalizado)
        @include('setor.requerimentos.partials.modals.encaminhar')
    @endif
</section>
