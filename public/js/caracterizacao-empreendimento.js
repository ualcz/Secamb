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

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-caracterizacao-form]').forEach(form => {
            atualizarCampos(form);
            form.addEventListener('change', event => {
                if (event.target.matches('[data-conditional-trigger]')) {
                    atualizarCampos(form, true);
                }
            });
        });
    });
})();
