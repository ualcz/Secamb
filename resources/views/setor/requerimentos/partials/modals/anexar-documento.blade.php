<dialog
    id="modal-anexar-documento"
    class="acao-modal"
    data-open-on-load="{{ old('acao') === 'anexar_documento' ? 'true' : 'false' }}"
    aria-labelledby="titulo-modal-anexar-documento"
>
    <div class="acao-modal-cabecalho">
        <div>
            <p class="acao-modal-etiqueta">Registro no histórico</p>
            <h3 id="titulo-modal-anexar-documento">Anexar documento</h3>
            <p>A juntada será registrada com data e responsável, agrupada na situação atual. O status não muda e o cidadão não receberá e-mail.</p>
        </div>
        <button type="button" class="acao-modal-fechar" data-modal-close aria-label="Fechar modal">&times;</button>
    </div>

    <div class="acao-modal-conteudo">
        @include('setor.requerimentos.partials.modals.erros')

        <form action="{{ route('setor.requerimentos.anexarDocumento', [$setor->id, $requerimento->id]) }}" method="POST" enctype="multipart/form-data" data-loading-message="Anexando documento...">
            @csrf
            <input type="hidden" name="acao" value="anexar_documento">

            <div class="acao-modal-secao">
                <label for="titulo_documento" class="info-label">Nome do documento <span>(opcional)</span></label>
                <input
                    type="text"
                    name="titulo_documento"
                    id="titulo_documento"
                    class="select-status"
                    maxlength="255"
                    value="{{ old('titulo_documento') }}"
                    placeholder="Ex.: Parecer técnico"
                >
            </div>

            <div class="acao-modal-secao acao-modal-anexo">
                <label for="arquivos_historico" class="info-label">Arquivo(s) <span>(obrigatório, até 50 MB cada)</span></label>
                <input
                    type="file"
                    name="arquivos[]"
                    id="arquivos_historico"
                    class="form-control file-input"
                    multiple
                    required
                    accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                >
                <p class="acao-modal-ajuda">Os documentos ficarão agrupados visualmente na situação atual, com registro próprio de data e responsável.</p>
            </div>

            <div class="acao-modal-secao">
                <label for="observacao_anexo" class="info-label">Contexto da juntada <span>(opcional)</span></label>
                <textarea
                    name="observacao"
                    id="observacao_anexo"
                    class="form-control"
                    rows="3"
                    maxlength="10000"
                    placeholder="Informe, se necessário, por que o documento está sendo juntado."
                >{{ old('observacao') }}</textarea>
            </div>

            <div class="acao-modal-rodape">
                <button type="button" class="btn-modal-secundario" data-modal-close>Cancelar</button>
                <button type="submit" class="btn-atualizar btn-atender">Registrar juntada</button>
            </div>
        </form>
    </div>
</dialog>
