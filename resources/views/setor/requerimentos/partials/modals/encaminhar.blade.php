<dialog
    id="modal-encaminhar"
    class="acao-modal"
    data-open-on-load="{{ old('acao') === 'encaminhar' ? 'true' : 'false' }}"
    aria-labelledby="titulo-modal-encaminhar"
>
    <div class="acao-modal-cabecalho">
        <div>
            <p class="acao-modal-etiqueta">Tramitação entre setores</p>
            <h3 id="titulo-modal-encaminhar">Encaminhar requerimento</h3>
            <p>Defina o setor de destino e as orientações para continuidade do processo.</p>
        </div>
        <button type="button" class="acao-modal-fechar" data-modal-close aria-label="Fechar modal">&times;</button>
    </div>

    <div class="acao-modal-conteudo">
        @include('setor.requerimentos.partials.modals.erros')

        <form action="{{ route('setor.requerimentos.encaminhar', [$setor->id, $requerimento->id]) }}" method="POST" enctype="multipart/form-data" data-loading-message="Encaminhando requerimento...">
            @csrf
            <input type="hidden" name="acao" value="encaminhar">

            <fieldset class="acao-tipo-tramitacao">
                <legend>Tipo de tramitação</legend>
                <label class="acao-opcao-tramitacao">
                    <input type="radio" name="tipo_encaminhamento" value="transferencia" {{ old('tipo_encaminhamento', 'transferencia') === 'transferencia' ? 'checked' : '' }}>
                    <span>
                        <strong>Transferência de setor</strong>
                        <span>Transfere a responsabilidade do processo para o novo setor.</span>
                    </span>
                </label>
                <label class="acao-opcao-tramitacao">
                    <input type="radio" name="tipo_encaminhamento" value="despacho" {{ old('tipo_encaminhamento') === 'despacho' ? 'checked' : '' }}>
                    <span>
                        <strong>Solicitar despacho ou parecer</strong>
                        <span>Envia para manifestação, com opção de devolver ao seu setor.</span>
                    </span>
                </label>
            </fieldset>

            <div class="acao-modal-secao">
                <label for="setor_destino_id" class="info-label">Setor de destino</label>
                <select name="setor_destino_id" id="setor_destino_id" class="select-status" required onchange="atualizarAssuntosDestino()">
                    <option value="">Selecione um setor</option>
                    @foreach($setoresDestino as $setorDestino)
                        <option value="{{ $setorDestino->id }}" {{ old('setor_destino_id') == $setorDestino->id ? 'selected' : '' }}>
                            {{ $setorDestino->setor_nome }} ({{ $setorDestino->setor_sigla }}){{ $setorDestino->is_interno ? ' — [Setor Interno]' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="box_assunto_destino" class="acao-modal-secao" style="display: none;">
                <label for="assunto_destino_id" class="info-label">Tipo de serviço ou demanda</label>
                <select name="assunto_destino_id" id="assunto_destino_id" class="select-status" onchange="atualizarInstrucaoAssunto()">
                    <option value="">Selecione o tipo de serviço (opcional)</option>
                </select>
                <div id="info_assunto_destino" class="acao-info-assunto" style="display: none;">
                    <strong>Orientações deste serviço:</strong> <span id="texto_instrucao_assunto"></span>
                </div>
            </div>

            <div class="acao-modal-secao">
                <label for="observacao_encaminhamento" class="info-label">Motivo ou orientação</label>
                <textarea name="observacao" id="observacao_encaminhamento" class="form-control" rows="4" placeholder="Descreva as orientações para o setor de destino..." required>{{ old('observacao') }}</textarea>
            </div>

            <div class="acao-modal-secao acao-modal-anexo">
                <label for="arquivos_encaminhamento" class="info-label">Documentos de apoio <span>(opcional)</span></label>
                <input type="file" name="arquivos[]" id="arquivos_encaminhamento" class="form-control file-input" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
            </div>

            <div class="acao-modal-rodape">
                <button type="button" class="btn-modal-secundario" data-modal-close>Cancelar</button>
                <button type="submit" class="btn-atualizar">Encaminhar processo</button>
            </div>
        </form>
    </div>
</dialog>
