function toggleMensagemIndeferido() {
    const selectStatus = document.getElementById('status');
    const textareaMensagem = document.getElementById('observacao');

    if (!selectStatus) return;

    const exigeJustificativa = [
        'Aguardando complementação',
        'Indeferido',
        'Encerrado',
    ].includes(selectStatus.value);
    const exigeDocumento = selectStatus.value === 'Aguardando complementação';
    const boxNomeDocumento = document.getElementById('box-nome-documento');
    const inputNomeDocumento = document.getElementById('nome_documento_solicitado');
    const inputArquivos = document.getElementById('arquivos_servidor');
    const rotuloArquivos = document.getElementById('rotulo-arquivos');
    const obrigatoriedadeArquivos = document.getElementById('obrigatoriedade-arquivos');
    const ajudaArquivos = document.getElementById('ajuda-arquivos');
    const exigeLicenca = selectStatus.value === 'Licença emitida';

    if (textareaMensagem) {
        if (exigeJustificativa) {
            textareaMensagem.setAttribute('required', 'required');
        } else {
            textareaMensagem.removeAttribute('required');
        }
    }

    if (boxNomeDocumento) {
        boxNomeDocumento.style.display = exigeDocumento ? 'block' : 'none';
    }
    if (inputNomeDocumento) {
        if (exigeDocumento) {
            inputNomeDocumento.setAttribute('required', 'required');
        } else {
            inputNomeDocumento.removeAttribute('required');
        }
    }
    if (inputArquivos) {
        inputArquivos.required = exigeLicenca;
    }
    if (rotuloArquivos) {
        rotuloArquivos.textContent = exigeLicenca ? 'Licença ambiental emitida' : 'Documento ou parecer';
    }
    if (obrigatoriedadeArquivos) {
        obrigatoriedadeArquivos.textContent = exigeLicenca ? '(obrigatório)' : '(opcional)';
    }
    if (ajudaArquivos) {
        ajudaArquivos.textContent = exigeLicenca
            ? 'Anexe o arquivo da licença para concluir a emissão.'
            : 'Os arquivos enviados serão registrados no histórico com a identificação do setor.';
    }

    const labelObservacao = document.getElementById('label-observacao');
    if (labelObservacao) {
        const rotulos = {
            'Aguardando complementação': 'Orientação para complementação',
            'Indeferido': 'Justificativa do indeferimento',
            'Encerrado': 'Motivo do encerramento',
        };
        labelObservacao.textContent = rotulos[selectStatus.value] || 'Mensagem para o histórico (opcional)';
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

    const dialogs = document.querySelectorAll('.acao-modal');
    document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.modalTarget);
            if (dialog && !dialog.open) {
                dialog.showModal();
            }
        });
    });

    dialogs.forEach(dialog => {
        if (dialog.dataset.openOnLoad === 'true') {
            dialog.showModal();
        }

        dialog.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', event => {
                if (form.dataset.submitting === 'true') {
                    event.preventDefault();
                    return;
                }

                form.dataset.submitting = 'true';
                form.setAttribute('aria-busy', 'true');

                const submitButton = event.submitter || form.querySelector('[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                }

                if (dialog.open) {
                    dialog.close();
                }

                const loadingOverlay = document.getElementById('acao-loading-overlay');
                if (loadingOverlay) {
                    const loadingMessage = loadingOverlay.querySelector('[data-loading-message]');
                    if (loadingMessage) {
                        loadingMessage.textContent = form.dataset.loadingMessage || 'Processando solicitação...';
                    }

                    loadingOverlay.hidden = false;
                    loadingOverlay.setAttribute('aria-hidden', 'false');
                    loadingOverlay.focus();
                    document.body.classList.add('acao-processando');
                }
            });
        });

        dialog.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', () => dialog.close());
        });

        dialog.addEventListener('click', event => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
});

function toggleTimelineDetalhes(btn) {
    var panel = btn.closest('.timeline-panel');
    if (!panel) return;
    var conteudo = panel.querySelector('.timeline-detalhes-conteudo');
    if (!conteudo) return;

    var aberto = conteudo.style.display !== 'none';
    conteudo.style.display = aberto ? 'none' : 'block';
    btn.classList.toggle('aberto', !aberto);
    var label = btn.querySelector('span');
    if (label) {
        label.textContent = !aberto ? 'ocultar' : 'detalhes';
    }
}

Object.assign(window, {
    toggleMensagemIndeferido,
    atualizarAssuntosDestino,
    atualizarInstrucaoAssunto,
    toggleTimelineDetalhes,
});
