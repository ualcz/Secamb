@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/show-requerimento.css') }}?v={{ filemtime(public_path('css/show-requerimento.css')) }}">

@section('content')
<div class="detalhes-container">
    <x-btn-voltar style="grid-column: span 2;"/>
    <div class="historico">
        <h3>HistÃ³rico da TramitaÃ§Ã£o</h3>

        <ul class="timeline">
            @forelse($requerimento->historicos as $historico)
                <li class="timeline-item status-{{ Str::slug($historico->status) }}">
                    <div class="timeline-badge"></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <span class="badge-status">{{ $historico->status }}</span>
                            <span class="timeline-date">
                                {{ $historico->created_at->format('d/m/Y \Ã \s H:i') }}
                            </span>
                        </div>
                        <div class="timeline-body">
                            <p><strong>ResponsÃ¡vel:</strong> {{ $historico->usuario->nome ?? 'Sistema' }}</p>
                            @if($historico->observacao)
                                @php
                                    $rotuloObservacao = 'ObservaÃ§Ã£o';
                                    $textoObservacao = $historico->observacao;

                                    if (preg_match('/^(OrientaÃ§Ã£o para|Resposta para):\s*(.+?)\R(.*)$/s', $textoObservacao, $partes)) {
                                        $rotuloObservacao = $partes[1] . ': ' . $partes[2];
                                        $textoObservacao = $partes[3];
                                    } elseif (preg_match('/^Encaminhado de .+ para (.+?)\.\s*\R+\s*(.*)$/s', $textoObservacao, $partes)) {
                                        $rotuloObservacao = 'OrientaÃ§Ã£o para: ' . $partes[1];
                                        $textoObservacao = $partes[2];
                                    } elseif (preg_match('/^Resposta de .+ para (.+?)\.\s*\R+\s*(.*)$/s', $textoObservacao, $partes)) {
                                        $rotuloObservacao = 'Resposta para: ' . $partes[1];
                                        $textoObservacao = $partes[2];
                                    }
                                @endphp
                                <div class="timeline-observacao">
                                    <strong>{{ $rotuloObservacao }}</strong><br>{{ $textoObservacao }}
                                </div>
                            @endif
                            @if($historico->nome_documento_solicitado)
                                <div class="timeline-observacao">
                                    <strong>Documento Solicitado:</strong> {{ $historico->nome_documento_solicitado }}
                                </div>
                            @endif

                            {{-- DOCUMENTOS ANEXADOS NESTA TRAMITAÃ‡ÃƒO --}}
                            @include('requerimentos.partials.historico-documentos', [
                                'documentos' => $historico->documentos,
                                'historico' => $historico,
                                'requerimento' => $requerimento,
                            ])
                        </div>
                    </div>
                </li>
            @empty
                <p>Nenhum registro no histÃ³rico atÃ© o momento.</p>
            @endforelse
        </ul>
    </div>

    <div class="protocolo-info">
        @if(session('sucesso'))
            <div class="alert-sucesso">
                {{ session('sucesso') }}
            </div>
        @endif
            <x-loading-overlay
            form-id="formCorrecao"
            :mensagens="[
                'Atualizando status...',
                'Enviando email para o aluno...',
                'Atualizando sistema...',
                'SÃ³ mais um instante...'
            ]"
        />

        <details class="card-painel info-aluno-accordion">
            <summary class="accordion-header">
                <div class="accordion-titulo-wrapper">
                    <h2 class="card-titulo">
                        <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        InformaÃ§Ãµes do Aluno
                    </h2>

                    <!-- Resumo visÃ­vel mesmo quando colapsado -->
                    <div class="grid-3 preview-aluno">
                        <div class="info-grupo">
                            <span class="info-label">Nome Completo</span>
                            <span class="info-valor">{{ $requerimento->usuario->nome }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">MatrÃ­cula</span>
                            <span class="info-valor">{{ $requerimento->usuario->matricula }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">Telefone / WhatsApp</span>
                            <span class="info-valor">{{ $requerimento->usuario->telefone ?? 'NÃ£o informado' }}</span>
                        </div>
                    </div>
                </div>

                <svg class="icone-seta" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>

            <!-- ConteÃºdo expandido (Oculto atÃ© o clique) -->
            <div class="accordion-body">
                <!-- Demais InformaÃ§Ãµes do Aluno -->
                <div class="grid-3 grid-complementar">
                    <div class="info-grupo">
                        <span class="info-label">CPF</span>
                        <span class="info-valor">{{ $requerimento->usuario->cpf }}</span>
                    </div>
                    <div class="info-grupo">
                        <span class="info-label">Turma</span>
                        <span class="info-valor">{{ $requerimento->usuario->tipo_processo_formatado ?? 'NÃ£o informada' }}</span>
                    </div>
                    <div class="info-grupo">
                        <span class="info-label">E-mail Institucional</span>
                        <span class="info-valor">{{ $requerimento->usuario->email }}</span>
                    </div>
                    <div class="info-grupo">
                        <span class="info-label">E-mail Pessoal</span>
                        <span class="info-valor">{{ $requerimento->usuario->email ?? 'NÃ£o informado' }}</span>
                    </div>
                </div>

                <hr class="divisor-secao">

                <!-- SeÃ§Ã£o de EndereÃ§o Residencial -->
                <h3 class="subtitulo-secao">EndereÃ§o Residencial</h3>
                @if($requerimento->usuario->endereco)
                    <div class="grid-3">
                        <div class="info-grupo">
                            <span class="info-label">Rua / Logradouro</span>
                            <span class="info-valor">{{ $requerimento->usuario->endereco->rua }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">NÃºmero</span>
                            <span class="info-valor">{{ $requerimento->usuario->endereco->numero }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">Bairro</span>
                            <span class="info-valor">{{ $requerimento->usuario->endereco->bairro }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">Cidade / UF</span>
                            <span class="info-valor">{{ $requerimento->usuario->endereco->cidade }} / {{ $requerimento->usuario->endereco->estado }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">CEP</span>
                            <span class="info-valor">{{ $requerimento->usuario->endereco->cep }}</span>
                        </div>
                    </div>
                @else
                    <p class="texto-vazio">Nenhum endereÃ§o cadastrado para este usuÃ¡rio.</p>
                @endif
            </div>
        </details>
        <div class="card-painel">
            <div class="card-header-flex">
                <div>
                    <span class="info-label">NÂº Protocolo</span>
                    <h1 class="card-titulo" style="font-size: 1.5rem; color: #2563eb;">
                        {{ $requerimento->numero_protocolo }}
                    </h1>
                </div>
                <div>
                    @php
                        $statusClass = match($requerimento->status) {
                            'Aberto' => 'badge-Aberto',
                            'Em AnÃ¡lise' => 'badge-analise',
                            'ConcluÃ­do' => 'badge-concluido',
                            'Indeferido' => 'badge-indeferido',
                            'Despacho' => 'badge-despacho',
                            default => 'badge-analise'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                        {{ $requerimento->status }}
                    </span>
                </div>
            </div>

            <div class="grid-2">
                <div class="info-grupo">
                    <span class="info-label">Objeto do Requerimento</span>
                    <span class="info-valor">{{ $requerimento->objetoDoRequerimento }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Data e Hora de Envio</span>
                    <span class="info-valor">{{ $requerimento->created_at->format('d/m/Y \Ã \s H:i') }}</span>
                </div>
            </div>

            <div class="info-grupo" style="margin-top: 0.75rem;">
                <span class="info-label">Motivo / SolicitaÃ§Ã£o</span>
                <div class="box-motivo">
                    <span class="info-valor">{{ $requerimento->motivo }}</span>
                </div>
            </div>

            @if(session('success'))
                <div style="background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-size: 0.875rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 0.875rem;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <section class="card-painel painel-acoes" aria-labelledby="titulo-acoes-requerimento">
            <h2 id="titulo-acoes-requerimento" class="painel-acoes-titulo">AÃ§Ãµes do requerimento</h2>
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
                        <label for="resposta_encaminhamento" class="info-label">Resposta</label>
                        <textarea name="observacao" id="resposta_encaminhamento" class="form-control" rows="4" required>{{ old('observacao') }}</textarea>
                        <div class="acao-arquivos">
                            <label for="arquivos_resposta" class="info-label">Anexar documentos (opcional)</label>
                            <input type="file" name="arquivos[]" id="arquivos_resposta" class="form-control file-input" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                        </div>
                        <button type="submit" class="btn-atualizar">Enviar resposta e devolver</button>
                    </form>
                    </div>
                </details>
            @else
                <details class="acao-item" name="acao-requerimento" {{ old('acao') === 'atualizar_status' ? 'open' : '' }}>
                    <summary class="acao-resumo">
                        <span class="acao-textos">
                            <span class="acao-titulo">Atualizar status</span>
                            <span class="acao-descricao">Em anÃ¡lise, indeferido ou concluÃ­do</span>
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
                                    <option value="Em AnÃ¡lise" {{ old('status', $requerimento->status) == 'Em AnÃ¡lise' ? 'selected' : '' }}>Em AnÃ¡lise</option>
                                    <option value="Indeferido" {{ old('status', $requerimento->status) == 'Indeferido' ? 'selected' : '' }}>Indeferido</option>
                                    <option value="ConcluÃ­do" {{ old('status', $requerimento->status) == 'ConcluÃ­do' ? 'selected' : '' }}>ConcluÃ­do</option>
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
                                        <option value="0" {{ old('solicita_novo_documento') == '0' ? 'selected' : '' }}>NÃ£o</option>
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
                                        placeholder="Ex.: Atestado mÃ©dico"
                                        value="{{ old('nome_documento_solicitado') }}"
                                    >
                                </div>
                            </div>
                        </form>
                    </div>
                </details>

                <details class="acao-item" name="acao-requerimento" {{ old('acao') === 'encaminhar' ? 'open' : '' }}>
                    <summary class="acao-resumo">
                        <span class="acao-textos">
                            <span class="acao-titulo">Encaminhar para outro setor</span>
                            <span class="acao-descricao">Selecionar destino e registrar orientaÃ§Ã£o</span>
                        </span>
                    </summary>
                    <div class="acao-conteudo">
                        <form action="{{ route('setor.requerimentos.encaminhar', [$setor->id, $requerimento->id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="acao" value="encaminhar">
                            <label for="setor_destino_id" class="info-label">Setor de destino</label>
                            <select name="setor_destino_id" id="setor_destino_id" class="select-status" required>
                                <option value="">Selecione um setor</option>
                                @foreach($setoresDestino as $setorDestino)
                                    <option value="{{ $setorDestino->id }}" {{ old('setor_destino_id') == $setorDestino->id ? 'selected' : '' }}>{{ $setorDestino->setor_nome }} ({{ $setorDestino->setor_sigla }})</option>
                                @endforeach
                            </select>
                            <label for="observacao_encaminhamento" class="info-label acao-label-secundario">OrientaÃ§Ã£o</label>
                            <textarea name="observacao" id="observacao_encaminhamento" class="form-control" rows="4" required>{{ old('observacao') }}</textarea>
                            <div class="acao-arquivos">
                                <label for="arquivos_encaminhamento" class="info-label">Anexar documentos (opcional)</label>
                                <input type="file" name="arquivos[]" id="arquivos_encaminhamento" class="form-control file-input" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                            </div>
                            <button type="submit" class="btn-atualizar">Encaminhar</button>
                        </form>
                    </div>
                </details>
            @endif
        </section>

        {{-- DADOS DO ALUNO --}}


        {{-- ENDEREÃ‡O DO ALUNO --}}


    </div>
</div>

<script src="{{ asset('js/show-requerimento.js') }}"></script>
@endsection

