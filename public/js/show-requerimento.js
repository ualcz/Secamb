function toggleMensagemIndeferido() {
    const selectStatus = document.getElementById('status');
    const campoMensagem = document.getElementById('campo-mensagem');
    const textareaMensagem = document.getElementById('observacao');

    if (!selectStatus || !campoMensagem) return;

    if (selectStatus.value === 'Indeferido') {
        campoMensagem.style.display = 'block';
        if (textareaMensagem) textareaMensagem.setAttribute('required', 'required');
    } else {
        campoMensagem.style.display = 'none';
        if (textareaMensagem) textareaMensagem.removeAttribute('required');
    }
}

function toggleCampoNomeDocumento() {
    const solicitaSelect = document.getElementById('solicita_novo_documento');
    const boxNomeDocumento = document.getElementById('box-nome-documento');
    const inputNomeDocumento = document.getElementById('nome_documento_solicitado');

    if (solicitaSelect && boxNomeDocumento) {
        if (solicitaSelect.value === '1') {
            boxNomeDocumento.style.display = 'block';
            if (inputNomeDocumento) inputNomeDocumento.setAttribute('required', 'required');
        } else {
            boxNomeDocumento.style.display = 'none';
            if (inputNomeDocumento) inputNomeDocumento.removeAttribute('required');
        }
    }
}

function atualizarAssuntosDestino() {
    const selectSetor = document.getElementById('setor_destino_id');
    const boxAssunto = document.getElementById('box_assunto_destino');
    const selectAssunto = document.getElementById('assunto_destino_id');
    const infoBox = document.getElementById('info_assunto_destino');
    const catalogo = window.catalogoSetoresDestino || {};
    const oldAssuntoId = window.oldAssuntoDestinoId || null;

    if (!selectSetor || !boxAssunto || !selectAssunto) return;

    const setorId = selectSetor.value;
    selectAssunto.innerHTML = '<option value="">Selecione o tipo de serviço (opcional)</option>';
    if (infoBox) infoBox.style.display = 'none';

    if (setorId && catalogo[setorId] && catalogo[setorId].length > 0) {
        catalogo[setorId].forEach(assunto => {
            const opt = document.createElement('option');
            opt.value = assunto.id;
            opt.textContent = assunto.descricao;
            if (oldAssuntoId && String(oldAssuntoId) === String(assunto.id)) {
                opt.selected = true;
            }
            selectAssunto.appendChild(opt);
        });
        boxAssunto.style.display = 'block';
        atualizarInstrucaoAssunto();
    } else {
        boxAssunto.style.display = 'none';
    }
}

function atualizarInstrucaoAssunto() {
    const selectSetor = document.getElementById('setor_destino_id');
    const selectAssunto = document.getElementById('assunto_destino_id');
    const infoBox = document.getElementById('info_assunto_destino');
    const textoInstrucao = document.getElementById('texto_instrucao_assunto');
    const catalogo = window.catalogoSetoresDestino || {};

    if (!selectSetor || !selectAssunto || !infoBox || !textoInstrucao) return;

    const setorId = selectSetor.value;
    const assuntoId = selectAssunto.value;

    if (setorId && assuntoId && catalogo[setorId]) {
        const item = catalogo[setorId].find(a => String(a.id) === String(assuntoId));
        if (item && item.observacao) {
            textoInstrucao.textContent = item.observacao;
            infoBox.style.display = 'block';
            return;
        }
    }
    infoBox.style.display = 'none';
}

// Inicializações da página
document.addEventListener('DOMContentLoaded', function () {
    // Accordion de informações do aluno/requerente
    const accordions = document.querySelectorAll('.info-aluno-accordion');
    accordions.forEach(function (acc) {
        const header = acc.querySelector('.accordion-header');
        if (header) {
            header.addEventListener('click', function () {
                setTimeout(function () {
                    const seta = acc.querySelector('.icone-seta');
                    if (seta) {
                        seta.style.transform = acc.open ? 'rotate(180deg)' : 'rotate(0deg)';
                    }
                }, 20);
            });
        }
    });

    // Inicializa catálogo de serviços do setor de destino se existir
    atualizarAssuntosDestino();
});
