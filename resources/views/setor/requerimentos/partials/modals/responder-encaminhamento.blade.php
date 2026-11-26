<dialog
    id="modal-responder-encaminhamento"
    class="acao-modal"
    data-open-on-load="{{ old('acao') === 'responder_encaminhamento' ? 'true' : 'false' }}"
    aria-labelledby="titulo-modal-responder"
>
    <div class="acao-modal-cabecalho">
        <div>
            <p class="acao-modal-etiqueta">Tramitação entre setores</p>
            <h3 id="titulo-modal-responder">Responder encaminhamento</h3>
            <p>O requerimento será devolvido para {{ $requerimento->setorRetorno?->setor_sigla }}.</p>
        </div>
        <button type="button" class="acao-modal-fechar" data-modal-close aria-label="Fechar modal">&times;</button>
    </div>

    <div class="acao-modal-conteudo">
        @include('setor.requerimentos.partials.modals.erros')

        <form action="{{ route('setor.requerimentos.responderEncaminhamento', [$setor->id, $requerimento->id]) }}" method="POST" enctype="multipart/form-data" data-loading-message="Enviando resposta...">
            @csrf
            <input type="hidden" name="acao" value="responder_encaminhamento">

            <div class="acao-modal-secao">
                <label for="resposta_encaminhamento" class="info-label">Resposta ou despacho</label>
                <p class="acao-modal-ajuda">Registre a manifestação que será enviada ao setor de origem.</p>
                <textarea name="observacao" id="resposta_encaminhamento" class="form-control" rows="4" placeholder="Escreva a resposta técnica ou o parecer..." required>{{ old('observacao') }}</textarea>
            </div>

            <div class="acao-modal-secao acao-modal-anexo">
                <label for="arquivos_resposta" class="info-label">Documentos de apoio <span>(opcional)</span></label>
                <input type="file" name="arquivos[]" id="arquivos_resposta" class="form-control file-input" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                <p class="acao-modal-ajuda">Você pode anexar documentos para acompanhar a resposta.</p>
            </div>

            <div class="acao-modal-rodape">
                <button type="button" class="btn-modal-secundario" data-modal-close>Cancelar</button>
                <button type="submit" class="btn-atualizar">Enviar resposta</button>
            </div>
        </form>
    </div>
</dialog>
