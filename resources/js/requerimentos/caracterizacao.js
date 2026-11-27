(() => {
    function valorSelecionado(form, nome) {
        const campoMarcado = form.querySelector(`[name="${nome}"]:checked`);
        if (campoMarcado) return campoMarcado.value;

        return form.querySelector(`[name="${nome}"]`)?.value || '';
    }

    function atualizarCampos(form, limparOcultos = false) {
        form.querySelectorAll('[data-conditional-container]').forEach(container => {
            const [nome, valor] = container.dataset.conditionalContainer.split(':');
            const containerPai = container.parentElement.closest('[data-conditional-container]');
            const visivel = valorSelecionado(form, nome) === valor
                && (!containerPai || !containerPai.hidden);
            container.hidden = !visivel;

            container.querySelectorAll('input, select, textarea').forEach(campo => {
                campo.disabled = !visivel;
                if (!visivel && limparOcultos) {
                    if (campo.type === 'radio' || campo.type === 'checkbox') {
                        campo.checked = false;
                    } else {
                        campo.value = '';
                    }
                }
            });
        });
    }

    function atualizarRepresentantes(form) {
        const empreendimento = form.querySelector('[name="empreendimento_id"]');
        const representante = form.querySelector('[name="representante_legal_id"]');
        const aviso = form.querySelector('[data-sem-representante]');
        if (!empreendimento || !representante) return;

        const empreendimentoId = empreendimento.value;
        const template = Array.from(form.querySelectorAll('template[data-representantes-empreendimento]'))
            .find(item => item.dataset.representantesEmpreendimento === empreendimentoId);
        const opcoes = template ? Array.from(template.content.querySelectorAll('option')) : [];
        const valorDesejado = representante.dataset.valorAntigo || representante.value;

        while (representante.options.length > 1) {
            representante.remove(1);
        }
        opcoes.forEach(opcao => representante.add(opcao.cloneNode(true)));

        representante.disabled = !empreendimentoId;
        representante.required = Boolean(empreendimentoId);
        representante.value = opcoes.some(opcao => opcao.value === valorDesejado)
            ? valorDesejado
            : opcoes.find(opcao => opcao.dataset.usuarioAtual === 'true')?.value || '';
        if (!representante.value && opcoes.length === 1) {
            representante.value = opcoes[0].value;
        }

        if (aviso) aviso.hidden = !empreendimentoId || opcoes.length > 0;
        representante.dataset.valorAntigo = '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-caracterizacao-form]').forEach(form => {
            atualizarCampos(form);
            atualizarRepresentantes(form);
            form.addEventListener('change', event => {
                if (event.target.matches('[data-conditional-trigger]')) {
                    atualizarCampos(form, true);
                }
                if (event.target.matches('[name="empreendimento_id"]')) {
                    atualizarRepresentantes(form);
                }
            });
        });
    });
})();
