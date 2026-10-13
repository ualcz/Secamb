function verInfoAluno() {
    const divInfoAluno = document.querySelector('.info-aluno');
    divInfoAluno.classList.toggle('active');

    const imgBotao = document.querySelector('#ver-info-aluno img');

    if (divInfoAluno.classList.contains('active')) {
        imgBotao.src = "/img/icons/chevron-up.svg";
    } else {
        imgBotao.src = "/img/icons/chevron-down.svg";
    }
}

function alternarObjetoOutro(ativo) {
    const campoOutro = document.querySelector('input[name="objeto_outro"]');
    if (!campoOutro) return;

    campoOutro.disabled = !ativo;
    campoOutro.style.backgroundColor = ativo ? '#fff' : '#f1f5f9';
    campoOutro.style.borderColor = ativo ? '#ccc' : '#cbd5e1';
    campoOutro.style.color = ativo ? '#111827' : '#64748b';
    campoOutro.style.cursor = ativo ? 'text' : 'not-allowed';

    const ajudaOutro = document.getElementById('objeto-outro-ajuda');
    if (ajudaOutro) ajudaOutro.style.display = ativo ? 'none' : 'flex';

    if (ativo) {
        mostrarDocumentosAssunto('outro');
    } else {
        campoOutro.value = '';
    }
}

function salvarRascunhoRequerimento(form) {
    const chave = form?.dataset.draftKey;
    if (!chave || form.dataset.suspendDraft === 'true') return;

    const campos = {};
    Array.from(form.elements).forEach(campo => {
        if (!campo.name || campo.name === '_token' || campo.disabled ||
            ['file', 'submit', 'button', 'reset', 'password'].includes(campo.type)) return;

        if (campo.type === 'radio') {
            if (campo.checked) campos[campo.name] = campo.value;
        } else if (campo.type === 'checkbox') {
            campos[campo.name] ??= [];
            if (campo.checked) campos[campo.name].push(campo.value);
        } else {
            campos[campo.name] = campo.value;
        }
    });

    const etapaAtiva = Array.from(form.querySelectorAll('.form-step'))
        .find(etapa => window.getComputedStyle(etapa).display !== 'none');

    try {
        sessionStorage.setItem(chave, JSON.stringify({
            campos,
            etapa: etapaAtiva?.dataset.step ?? '1',
        }));
    } catch (error) {
        // O formulário continua funcionando mesmo se o armazenamento estiver indisponível.
    }
}

function restaurarRascunhoRequerimento(form) {
    const chave = form.dataset.draftKey;
    if (!chave) return null;

    try {
        const rascunho = sessionStorage.getItem(chave);
        if (!rascunho) return null;

        const dados = JSON.parse(rascunho);
        Object.entries(dados.campos ?? {}).forEach(([nome, valor]) => {
            Array.from(form.elements)
                .filter(campo => campo.name === nome)
                .forEach(campo => {
                    if (campo.type === 'radio') {
                        campo.checked = campo.value === valor;
                    } else if (campo.type === 'checkbox') {
                        campo.checked = Array.isArray(valor) && valor.includes(campo.value);
                    } else if (campo.type !== 'file') {
                        campo.value = valor;
                    }
                });
        });
        return dados;
    } catch (error) {
        sessionStorage.removeItem(chave);
        return null;
    }
}

function mudarPasso(passo) {
    document.querySelectorAll('.form-step').forEach(el => {
        el.style.display = 'none';
    });

    const etapaAlvo = document.querySelector(`.form-step[data-step="${passo}"]`);
    if (etapaAlvo) {
        etapaAlvo.style.display = 'block';
        etapaAlvo.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    salvarRascunhoRequerimento(document.getElementById('formRequerimento'));
}

function mostrarDocumentosAssunto(index) {
    document.querySelectorAll('.bloco-documentos-assunto').forEach((el, i) => {
        if (index === 'outro') {
            el.style.display = (el.id === 'bloco-doc-outro') ? 'block' : 'none';
        } else {
            el.style.display = (el.id === `bloco-doc-${index}`) ? 'block' : 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action*="enviar-email"]');
    if (!form) return;

    form.dataset.suspendDraft = 'true';
    if (form.dataset.clearDraft === 'true') {
        sessionStorage.removeItem(form.dataset.draftKey);
    }

    const rascunho = form.dataset.restoreDraft === 'true' && form.dataset.clearDraft !== 'true'
        ? restaurarRascunhoRequerimento(form)
        : null;

    const radioSelecionado = form.querySelector('input[name="objetoDoRequerimento"]:checked');
    radioSelecionado?.dispatchEvent(new Event('change', { bubbles: true }));

    mudarPasso(1);
    form.dataset.suspendDraft = 'false';

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
        if (e.target.matches('input[type="file"]')) {
            verificarDocumentosObrigatorios();
        }
        salvarRascunhoRequerimento(form);
    });
    form.addEventListener('input', () => salvarRascunhoRequerimento(form));
    window.addEventListener('pagehide', () => salvarRascunhoRequerimento(form));

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



