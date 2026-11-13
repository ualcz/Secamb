function salvarRascunhoRequerimento(form, ativarSePreenchido = false, objetoOverride = null) {
    return window.rascunhoRequerimento.salvarCampos(form, objetoOverride, ativarSePreenchido);
}

function salvarArquivosRascunho(form, inputAlterado = null) {
    return window.rascunhoRequerimento.salvarArquivos(form, inputAlterado);
}

function mudarPasso(passo) {
    const form = document.getElementById('formRequerimento');
    const descricao = form?.querySelector('textarea[name="motivo"]');
    const descricaoInvalida = Number(passo) === 3 && descricao && !descricao.value.trim();

    if (descricaoInvalida) {
        passo = 2;
        descricao.setCustomValidity('Preencha a descrição da solicitação para continuar.');
    }

    document.querySelectorAll('.form-step').forEach(el => {
        el.style.display = 'none';
    });

    const etapaAlvo = document.querySelector(`.form-step[data-step="${passo}"]`);
    if (etapaAlvo) {
        etapaAlvo.style.display = 'block';
        etapaAlvo.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    if (descricao) {
        descricao.required = Number(passo) === 2;
        if (descricaoInvalida) {
            descricao.reportValidity();
            descricao.focus();
        }
    }

    salvarRascunhoRequerimento(
        form,
        Number(passo) >= 2
    );
}

function mostrarDocumentosAssunto(index) {
    document.querySelectorAll('.bloco-documentos-assunto').forEach((el, i) => {
        el.style.display = (el.id === `bloco-doc-${index}`) ? 'block' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    const form = document.querySelector('form[action*="enviar-email"]');
    if (!form) return;

    form.dataset.suspendDraft = 'true';
    if (form.dataset.clearDraft === 'true') {
        try {
            await window.rascunhoRequerimento.limpar(form);
        } catch (error) {
            console.error('Falha ao remover o rascunho enviado:', error);
        }
    }

    const btnEnviar = form.querySelector('button[type="submit"]');

    // Verifica se todos os documentos obrigatórios do bloco visível estão preenchidos
    function verificarDocumentosObrigatorios() {
        const blocoAtivo = Array.from(
            document.querySelectorAll('.bloco-documentos-assunto')
        ).find(b => b.style.display !== 'none' && window.getComputedStyle(b).display !== 'none');

        if (!blocoAtivo) {
            habilitarBotao(true);
            return;
        }

        const inputsObrigatorios = blocoAtivo.querySelectorAll('input[type="file"][data-obrigatorio="true"]');
        const todosInputs = blocoAtivo.querySelectorAll('input[type="file"]');

        const todoPreenchido = inputsObrigatorios.length === 0 || Array.from(inputsObrigatorios).every(
            input => input.files && input.files.length > 0
        );

        habilitarBotao(todoPreenchido);

        // Atualiza o estado visual de cada card individualmente
        todosInputs.forEach(input => {
            const card = input.closest('.doc-card') || input.closest('.campo') || input.parentElement;
            if (!card) return;
            const hasFiles = input.files && input.files.length > 0;
            if (hasFiles) {
                card.classList.add('doc-card-preenchido');
                card.classList.remove('doc-card-obrigatorio');
            } else {
                card.classList.remove('doc-card-preenchido');
                if (input.dataset.obrigatorio === 'true') {
                    card.classList.add('doc-card-obrigatorio');
                }
            }
        });
    }

    let dadosRascunho = null;
    let arquivosRascunho = [];
    let falhaAoLerRascunho = false;
    const objetoInicial = form.querySelector('input[name="objetoDoRequerimento"]:checked')?.value;
    if (form.dataset.restoreDraft === 'true' && objetoInicial && form.dataset.clearDraft !== 'true') {
        try {
            const dados = await window.rascunhoRequerimento.ler(form, objetoInicial);
            dadosRascunho = dados.rascunho;
            arquivosRascunho = dados.arquivos;
        } catch (error) {
            falhaAoLerRascunho = true;
            console.error('Falha ao recuperar o rascunho do requerimento:', error);
        }
    }

    if (dadosRascunho) {
        form.dataset.draftActive = 'true';
        form.dataset.draftObject = dadosRascunho.objeto || objetoInicial;
        const camposRestaurar = { ...dadosRascunho.campos };
        if (form.dataset.motivoInvalido === 'true') delete camposRestaurar.motivo;
        window.rascunhoRequerimento.restaurarCampos(form, camposRestaurar);
        try {
            await window.rascunhoRequerimento.restaurarArquivos(form, arquivosRascunho);
        } catch (error) {
            console.error('Falha ao restaurar anexos do rascunho:', error);
        }
    } else if (!falhaAoLerRascunho && form.dataset.clearDraft !== 'true') {
        form.dataset.draftActive = 'false';
        form.dataset.draftObject = '';
    }

    const radioSelecionado = form.querySelector('input[name="objetoDoRequerimento"]:checked');
    radioSelecionado?.dispatchEvent(new Event('change', { bubbles: true }));
    form.querySelector('#empreendimento_id')?.dispatchEvent(new Event('change', { bubbles: true }));

    mudarPasso(form.dataset.stepOnError || dadosRascunho?.etapa || '1');
    form.dataset.suspendDraft = 'false';

    function habilitarBotao(habilitar) {
        if (!btnEnviar) return;
        btnEnviar.disabled = !habilitar;

        if (habilitar) {
            btnEnviar.style.opacity = '1';
            btnEnviar.style.cursor = 'pointer';
            btnEnviar.title = '';
        } else {
            btnEnviar.style.opacity = '0.45';
            btnEnviar.style.cursor = 'not-allowed';
            btnEnviar.title = 'Anexe todos os documentos obrigatórios para enviar';
        }
    }

    // Reage a qualquer mudança de arquivo no formulário
    form.addEventListener('change', (e) => {
        if (form.dataset.suspendDraft === 'true') return;

        if (e.target.name === 'objetoDoRequerimento') {
            form.dataset.objetoEscolhidoUsuario = 'true';
            const objeto = e.target.value;
            const temRascunho = e.target.dataset.temRascunho === 'true';
            if ((form.dataset.draftActive === 'true' && objeto !== form.dataset.draftObject) ||
                (form.dataset.draftActive !== 'true' && temRascunho)) {
                window.rascunhoRequerimento.mudarObjeto(form, objeto);
                return;
            }
        }

        if (e.target.matches('input[type="file"]')) {
            verificarDocumentosObrigatorios();
            salvarArquivosRascunho(form, e.target);
        }

        const etapaDoFormulario = Array.from(form.querySelectorAll('.form-step'))
            .find(etapa => window.getComputedStyle(etapa).display !== 'none')?.dataset.step;
        salvarRascunhoRequerimento(form, Number(etapaDoFormulario) >= 2);
    });
    let temporizadorRascunho;
    form.addEventListener('input', (event) => {
        if (form.dataset.suspendDraft === 'true') return;
        if (event.target.name === 'motivo') event.target.setCustomValidity('');
        window.clearTimeout(temporizadorRascunho);
        const etapaDoFormulario = Array.from(form.querySelectorAll('.form-step'))
            .find(etapa => window.getComputedStyle(etapa).display !== 'none')?.dataset.step;
        temporizadorRascunho = window.setTimeout(
            () => salvarRascunhoRequerimento(form, Number(etapaDoFormulario) >= 2),
            400
        );
    });
    form.addEventListener('submit', (event) => {
        const descricao = form.querySelector('textarea[name="motivo"]');
        if (descricao && !descricao.value.trim()) {
            event.preventDefault();
            mudarPasso(2);
            descricao.reportValidity();
            return;
        }
        salvarRascunhoRequerimento(form, true);
    });
    window.addEventListener('pagehide', () => {
        salvarRascunhoRequerimento(form);
    });
    // Reage à mudança de assunto (troca de bloco visível)
    const observer = new MutationObserver(() => {
        verificarDocumentosObrigatorios();
    });

    document.querySelectorAll('.bloco-documentos-assunto').forEach(bloco => {
        observer.observe(bloco, { attributes: true, attributeFilter: ['style'] });
    });

    // Estado inicial
    verificarDocumentosObrigatorios();
});



