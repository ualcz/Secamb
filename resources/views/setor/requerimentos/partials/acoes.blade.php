<section class="card-painel painel-acoes" aria-labelledby="titulo-acoes-requerimento">
    <h2 id="titulo-acoes-requerimento" class="painel-acoes-titulo">Ações do requerimento</h2>

    {{-- AÇÃO: DEVOLVER ENCAMINHAMENTO (Se o processo foi encaminhado temporariamente para este setor) --}}
    @if($requerimento->setor_retorno_id)
        <details class="acao-item" name="acao-requerimento" open>
            <summary class="acao-resumo">
                <span class="acao-textos">
                    <span class="acao-titulo">Responder encaminhamento</span>
                    <span class="acao-descricao">Devolver para {{ $requerimento->setorRetorno?->setor_sigla }}</span>
                </span>
            </summary>
            <div class="acao-conteudo">
                <form action="{{ route('setor.requerimentos.responderEncaminhamento', [$setor->id, $requerimento->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="acao" value="responder_encaminhamento">
                    <label for="resposta_encaminhamento" class="info-label">Resposta / Despacho</label>
                    <textarea name="observacao" id="resposta_encaminhamento" class="form-control" rows="4" placeholder="Informe a resposta técnica ou parecer para o setor de origem..." required>{{ old('observacao') }}</textarea>
                    <div class="acao-arquivos">
                        <label for="arquivos_resposta" class="info-label">Anexar documentos (opcional)</label>
                        <input type="file" name="arquivos[]" id="arquivos_resposta" class="form-control file-input" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                    </div>
                    <button type="submit" class="btn-atualizar">Enviar resposta e devolver</button>
                </form>
            </div>
        </details>
    @else
        {{-- AÇÃO 1: ATUALIZAR STATUS --}}
        <details class="acao-item" name="acao-requerimento" {{ old('acao') === 'atualizar_status' ? 'open' : '' }}>
            <summary class="acao-resumo">
                <span class="acao-textos">
                    <span class="acao-titulo">Atualizar status</span>
                    <span class="acao-descricao">Em análise, indeferido ou concluído</span>
                </span>
            </summary>
            <div class="acao-conteudo">
                <form action="{{ route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]) }}" method="POST" enctype="multipart/form-data" id="formCorrecao">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="acao" value="atualizar_status">

                    <label for="status" class="info-label">Novo status</label>
                    <div class="form-status">
                        <select name="status" id="status" class="select-status" required onchange="toggleMensagemIndeferido()">
                            <option value="Em Análise" {{ old('status', $requerimento->status) == 'Em Análise' || old('status', $requerimento->status) == 'Em Analise' ? 'selected' : '' }}>Em Análise</option>
                            <option value="Indeferido" {{ old('status', $requerimento->status) == 'Indeferido' ? 'selected' : '' }}>Indeferido</option>
                            <option value="Concluído" {{ old('status', $requerimento->status) == 'Concluído' || old('status', $requerimento->status) == 'Concluido' ? 'selected' : '' }}>Concluído</option>
                        </select>

                        <button type="submit" class="btn-atualizar">Salvar status</button>
                    </div>

                    <div class="acao-arquivos">
                        <label for="arquivos_servidor" class="info-label">Anexar documento ou parecer (opcional)</label>
                        <input type="file" name="arquivos[]" id="arquivos_servidor" class="form-control file-input" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                    </div>

                    <div id="campo-mensagem" style="display: {{ old('status', $requerimento->status) == 'Indeferido' ? 'block' : 'none' }};">
                        <label for="observacao" class="info-label">Motivo do indeferimento</label>
                        <textarea
                            name="observacao"
                            id="observacao"
                            class="form-control"
                            rows="3"
                            placeholder="Informe o motivo e o que precisa ser corrigido."
                            {{ old('status', $requerimento->status) == 'Indeferido' ? 'required' : '' }}
                        >{{ old('observacao') }}</textarea>
                        <div class="acao-campo-secundario">
                            <label for="solicita_novo_documento" class="info-label">Solicitar documento ao aluno?</label>
                            <select name="solicita_novo_documento" id="solicita_novo_documento" class="select-status" onchange="toggleCampoNomeDocumento()">
                                <option value="0" {{ old('solicita_novo_documento') == '0' ? 'selected' : '' }}>Não</option>
                                <option value="1" {{ old('solicita_novo_documento') == '1' ? 'selected' : '' }}>Sim</option>
                            </select>
                        </div>
                        <div id="box-nome-documento" style="display: {{ old('solicita_novo_documento') == '1' ? 'block' : 'none' }};">
                            <label for="nome_documento_solicitado" class="info-label">Documento solicitado</label>
                            <input
                                type="text"
                                name="nome_documento_solicitado"
                                id="nome_documento_solicitado"
                                class="select-status campo-documento-solicitado"
                                placeholder="Ex.: Atestado médico / Laudo"
                                value="{{ old('nome_documento_solicitado') }}"
                            >
                        </div>
                    </div>
                </form>
            </div>
        </details>

        {{-- AÇÃO 2: ENCAMINHAR PARA OUTRO SETOR --}}
        <details class="acao-item" name="acao-requerimento" {{ old('acao') === 'encaminhar' ? 'open' : '' }}>
            <summary class="acao-resumo">
                <span class="acao-textos">
                    <span class="acao-titulo">Encaminhar para outro setor</span>
                    <span class="acao-descricao">Selecionar destino e registrar orientação</span>
                </span>
            </summary>
            <div class="acao-conteudo">
                <form action="{{ route('setor.requerimentos.encaminhar', [$setor->id, $requerimento->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="acao" value="encaminhar">

                    <div style="margin-bottom: 12px;">
                        <label for="setor_destino_id" class="info-label">Setor de destino *</label>
                        <select name="setor_destino_id" id="setor_destino_id" class="select-status" required onchange="atualizarAssuntosDestino()">
                            <option value="">Selecione um setor</option>
                            @foreach($setoresDestino as $setorDestino)
                                <option value="{{ $setorDestino->id }}" {{ old('setor_destino_id') == $setorDestino->id ? 'selected' : '' }}>
                                    {{ $setorDestino->setor_nome }} ({{ $setorDestino->setor_sigla }}){{ $setorDestino->is_interno ? ' — [Setor Interno]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="box_assunto_destino" style="margin-bottom: 12px; display: none;">
                        <label for="assunto_destino_id" class="info-label">Tipo de Serviço / Demanda a Realizar</label>
                        <select name="assunto_destino_id" id="assunto_destino_id" class="select-status" onchange="atualizarInstrucaoAssunto()">
                            <option value="">Selecione o tipo de serviço (opcional)</option>
                        </select>
                        <div id="info_assunto_destino" style="display: none; margin-top: 6px; padding: 8px 12px; background: #f8fafc; border-left: 3px solid #7c3aed; border-radius: 4px; font-size: 0.8125rem; color: #475569;">
                            <strong style="color: #1e293b;">Orientações deste serviço:</strong> <span id="texto_instrucao_assunto"></span>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="observacao_encaminhamento" class="info-label acao-label-secundario">Orientação / Despacho *</label>
                        <textarea name="observacao" id="observacao_encaminhamento" class="form-control" rows="4" placeholder="Descreva as orientações para o setor de destino..." required>{{ old('observacao') }}</textarea>
                    </div>

                    <div class="acao-arquivos" style="margin-bottom: 14px;">
                        <label for="arquivos_encaminhamento" class="info-label">Anexar documentos (opcional)</label>
                        <input type="file" name="arquivos[]" id="arquivos_encaminhamento" class="form-control file-input" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                    </div>

                    <button type="submit" class="btn-atualizar">Encaminhar</button>
                </form>
            </div>
        </details>
    @endif
</section>
