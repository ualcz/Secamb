<!-- Etapa 1: Identificação e Objeto do Requerimento -->
<div class="form-step" data-step="1">
    <!-- 1. Identificação do Requerente -->
    <fieldset>
        <legend>Identificação do Requerente</legend>
        <div class="form-linha identificacao-linha">
            <div class="campo identificacao-nome">
                <label>Nome:</label>
                <input type="text" value="{{ auth()->user()->nome }}" readonly>
            </div>
            <div class="campo identificacao-documento">
                <label>{{ auth()->user()->isPessoaJuridica() ? 'CNPJ' : 'CPF' }}:</label>
                <input type="text" value="{{ auth()->user()->documento_identificacao ?? '—' }}" readonly>
            </div>
        </div>
    </fieldset>

    <!-- 2. Objeto do Requerimento -->
    <fieldset>
        <legend>Objeto do Requerimento</legend>
        <div class="opcoes-objeto">
            @php
                $assuntosMap = collect($modeloAtivo['assuntos_detalhes'] ?? [])->keyBy('descricao');
            @endphp
            @foreach($modeloAtivo['objetos'] ?? [] as $codigo => $descricao)
                @php
                    $assuntoItem = $assuntosMap[$descricao] ?? null;
                    $obs = $assuntoItem['observacao'] ?? null;
                    $indexAssunto = $loop->index;
                    $objetoAtual = old('objetoDoRequerimento', $objetoSelecionado);
                    $isChecked = $objetoAtual !== null ? $objetoAtual === $descricao : $loop->first;
                    $objetoTemRascunho = in_array($descricao, $objetosComRascunho ?? [], true);
                @endphp
                <div class="objeto-opcao">
                    <label class="card-objeto-requerimento">
                    <div class="card-objeto-header">
                        <div class="card-objeto-radio-wrap">
                            <input type="radio"
                                   name="objetoDoRequerimento"
                                   value="{{ $descricao }}"
                                   data-tem-rascunho="{{ $objetoTemRascunho ? 'true' : 'false' }}"
                                   onchange="mostrarDocumentosAssunto({{ $indexAssunto }})"
                                   {{ $isChecked ? 'checked' : '' }}>
                            <span class="card-objeto-titulo">{{ $descricao }}</span>
                        </div>
                        @if(!empty($assuntoItem['link_norma']))
                            <a href="{{ $assuntoItem['link_norma'] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               aria-label="Consultar norma de {{ $descricao }}"
                               title="Consultar legislação e norma regulamentadora"
                               class="btn-ajuda-norma"
                               onclick="event.stopPropagation();">
                                ?
                            </a>
                        @endif
                    </div>
                    @if($objetoTemRascunho)
                        <span class="objeto-rascunho-badge" role="status">Este objeto tem um rascunho</span>
                    @endif
                    @if(!empty($obs))
                        <div class="card-objeto-obs">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="card-objeto-obs-texto">{{ $obs }}</span>
                        </div>
                    @endif
                    <button
                        type="button"
                        class="btn-ver-documentacao"
                        onclick="abrirModalDocumentacao({{ $indexAssunto }}, '{{ addslashes($descricao) }}')">
                        <svg width="15" height="15" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="8" y1="13" x2="16" y2="13"/>
                            <line x1="8" y1="17" x2="16" y2="17"/>
                        </svg>
                        Ver documentação necessária
                    </button>
                    </label>
                    
                </div>
            @endforeach
        </div>
    </fieldset>

    <button type="button" class="btn-enviar" onclick="mudarPasso(2)" style="margin-top: 10px;">Próximo</button>
</div>


<!-- MODAL DE DOCUMENTAÇÃO NECESSÁRIA -->
<div id="modalDocumentacao" class="modal-documentacao"
     aria-hidden="true">

    <div class="modal-documentacao-conteudo"
         role="dialog"
         aria-modal="true"
         aria-labelledby="tituloModalDocumentacao">

        <div class="modal-documentacao-header">
            <div>
                <span class="modal-documentacao-subtitulo">
                    REQUERIMENTO
                </span>

                <h2 id="tituloModalDocumentacao">
                    Documentação necessária
                </h2>

                <p id="nomeAssuntoModal"></p>
            </div>

            <button type="button"
                    class="modal-documentacao-fechar"
                    onclick="fecharModalDocumentacao()"
                    aria-label="Fechar modal">
                &times;
            </button>
        </div>

        <div class="modal-documentacao-body">

            <div class="modal-documentacao-aviso">
                <svg width="19" height="19" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 16v-4"/>
                    <path d="M12 8h.01"/>
                </svg>

                <span>
                    Confira os documentos exigidos antes de preencher
                    o requerimento.
                </span>
            </div>

            <div id="listaDocumentosModal"
                 class="lista-documentos-modal">
            </div>

        </div>

        <div class="modal-documentacao-footer">
            <button type="button"
                    class="btn-modal-documentacao-fechar"
                    onclick="fecharModalDocumentacao()">
                Fechar
            </button>
        </div>

    </div>
</div>


<script>
    function atualizarPreviewEmpreendimento(select) {
        const card = document.getElementById('card-preview-empreendimento');
        if (!card) return;

        const selectedOption = select.options[select.selectedIndex];
        if (!select.value || !selectedOption) {
            card.style.display = 'none';
            return;
        }

        document.getElementById('prev-emp-nome').textContent = selectedOption.getAttribute('data-nome') || '';
        document.getElementById('prev-emp-cnpj').textContent = selectedOption.getAttribute('data-cnpj') || '';
        document.getElementById('prev-emp-porte').textContent = selectedOption.getAttribute('data-porte') || 'Não informado';
        document.getElementById('prev-emp-endereco').textContent = selectedOption.getAttribute('data-endereco') || '';
        document.getElementById('prev-emp-fase').textContent = selectedOption.getAttribute('data-fase') || '';

        card.style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const sel = document.getElementById('empreendimento_id');
        if (sel && sel.value) {
            atualizarPreviewEmpreendimento(sel);
        }
    });

function abrirModalDocumentacao(index) {

    const bloco = document.getElementById('bloco-doc-' + index);

    if (!bloco) {
        console.error('Documentação não encontrada para o assunto:', index);
        return;
    }

    const modal = document.getElementById('modalDocumentacao');
    const lista = document.getElementById('listaDocumentosModal');
    const nomeAssunto = document.getElementById('nomeAssuntoModal');

    // Recupera o nome do assunto
    const assunto = bloco.querySelector('strong');

    nomeAssunto.textContent = assunto
        ? assunto.textContent.trim()
        : '';

    // Limpa a lista anterior
    lista.innerHTML = '';

    // Recupera os documentos cadastrados
    const documentos = bloco.querySelectorAll('.doc-card');

    if (documentos.length === 0) {

        lista.innerHTML = `
            <div class="modal-doc-vazio">
                Este assunto não possui documentos obrigatórios.
            </div>
        `;

    } else {

        documentos.forEach(doc => {

            const nome = doc.querySelector('.doc-nome')?.textContent.trim() || '';

            const descricao = doc.querySelector('.doc-card-descricao span')?.textContent.trim() || '';

            const obrigatorio = doc.classList.contains('doc-card-obrigatorio');

            const linkModelo = doc.querySelector('.doc-link-modelo');

            const item = document.createElement('div');
            item.className = 'item-documento-modal';

            const informacoes = document.createElement('div');

            const titulo = document.createElement('h4');
            titulo.textContent = nome;

            informacoes.appendChild(titulo);

            if (descricao) {
                const texto = document.createElement('p');
                texto.textContent = descricao;
                informacoes.appendChild(texto);
            }

            if (linkModelo) {
                const link = document.createElement('a');

                link.href = linkModelo.href;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'documento-modelo-modal';
                link.textContent = '↓ Baixar modelo';

                informacoes.appendChild(link);
            }

            const status = document.createElement('span');

            status.className = 'documento-status-modal ' +
                (obrigatorio ? 'obrigatorio' : 'opcional');

            status.textContent = obrigatorio
                ? 'Obrigatório'
                : 'Opcional';

            item.appendChild(informacoes);
            item.appendChild(status);

            lista.appendChild(item);
        });
    }

    // Abre a modal
    modal.classList.add('ativa');
    modal.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';
}

function fecharModalDocumentacao() {

    const modal = document.getElementById('modalDocumentacao');

    modal.classList.remove('ativa');
    modal.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';
}

// Fecha ao clicar fora da modal
document.getElementById('modalDocumentacao')
    .addEventListener('click', function(event) {

        if (event.target === this) {
            fecharModalDocumentacao();
        }
    });

// Fecha com a tecla ESC
document.addEventListener('keydown', function(event) {

    if (event.key === 'Escape') {
        fecharModalDocumentacao();
    }
});
</script>

