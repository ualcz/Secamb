<dialog
    id="modal-atender"
    class="acao-modal"
    data-open-on-load="{{ old('acao') === 'atualizar_status' ? 'true' : 'false' }}"
    aria-labelledby="titulo-modal-atender"
>
    <div class="acao-modal-cabecalho">
        <div>
            <p class="acao-modal-etiqueta">Atendimento do processo</p>
            <h3 id="titulo-modal-atender">Atender requerimento</h3>
            <p>Atualize a etapa e registre documentos ou pareceres do setor.</p>
        </div>
        <button type="button" class="acao-modal-fechar" data-modal-close aria-label="Fechar modal">&times;</button>
    </div>

    <div class="acao-modal-conteudo">
        @include('setor.requerimentos.partials.modals.erros')

        <form action="{{ route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]) }}" method="POST" enctype="multipart/form-data" id="formCorrecao" data-loading-message="Registrando atendimento...">
            @csrf
            @method('PATCH')
            <input type="hidden" name="acao" value="atualizar_status">

            <div class="acao-modal-secao">
                <label for="status" class="info-label">Novo status</label>
                <select name="status" id="status" class="select-status" required onchange="toggleMensagemIndeferido()">
                    @foreach($statusPossiveis as $status)
                        <option value="{{ $status }}" {{ $statusSelecionado === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div id="campo-mensagem" class="acao-modal-secao">
                <label for="observacao" class="info-label" id="label-observacao">{{ $rotuloObservacao }}</label>
                <textarea
                    name="observacao"
                    id="observacao"
                    class="form-control"
                    rows="3"
                    placeholder="Escreva uma atualização para o cidadão e para o histórico do processo."
                    {{ $statusExigeJustificativa ? 'required' : '' }}
                >{{ old('observacao') }}</textarea>
                <p class="acao-modal-ajuda">A mensagem ficará no histórico. Use-a também para registrar vistoria, parecer ou outras atualizações sem criar novos status.</p>

                <div id="box-nome-documento" style="display: {{ $solicitaComplementacao ? 'block' : 'none' }};">
                    <label for="nome_documento_solicitado" class="info-label">Documento solicitado</label>
                    <input
                        type="text"
                        name="nome_documento_solicitado"
                        id="nome_documento_solicitado"
                        class="select-status campo-documento-solicitado"
                        placeholder="Ex.: Relatório ou estudo técnico"
                        value="{{ old('nome_documento_solicitado') }}"
                        {{ $solicitaComplementacao ? 'required' : '' }}
                    >
                </div>
            </div>

            <div class="acao-modal-secao acao-modal-anexo">
                <label for="arquivos_servidor" class="info-label">
                    <span id="rotulo-arquivos">{{ $statusSelecionado === \App\Models\Requerimento::STATUS_LICENCA_EMITIDA ? 'Licença ambiental emitida' : 'Documento ou parecer' }}</span>
                    <span id="obrigatoriedade-arquivos">{{ $statusSelecionado === \App\Models\Requerimento::STATUS_LICENCA_EMITIDA ? '(obrigatório)' : '(opcional)' }}</span>
                </label>
                <input
                    type="file"
                    name="arquivos[]"
                    id="arquivos_servidor"
                    class="form-control file-input"
                    multiple
                    accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                    {{ $statusSelecionado === \App\Models\Requerimento::STATUS_LICENCA_EMITIDA ? 'required' : '' }}
                >
                <p class="acao-modal-ajuda" id="ajuda-arquivos">
                    {{ $statusSelecionado === \App\Models\Requerimento::STATUS_LICENCA_EMITIDA
                        ? 'Anexe o arquivo da licença para concluir a emissão.'
                        : 'Os arquivos enviados serão registrados no histórico com a identificação do setor.' }}
                </p>
            </div>

            <div class="acao-modal-rodape">
                <button type="button" class="btn-modal-secundario" data-modal-close>Cancelar</button>
                <button type="submit" class="btn-atualizar btn-atender">Registrar atendimento</button>
            </div>
        </form>
    </div>
</dialog>
