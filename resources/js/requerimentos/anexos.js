/**
 * Gerenciamento Simplificado de Anexos por Assunto (SDP - IFBA)
 */
const AnexosRequerimento = (function () {
    let dadosAssuntos = [];

    function carregarDados() {
        const scriptDados = document.getElementById('dados-assuntos');
        if (scriptDados) {
            try {
                dadosAssuntos = JSON.parse(scriptDados.textContent || '[]');
            } catch (e) {
                dadosAssuntos = [];
            }
        }
    }

    function obterAssuntoAtual() {
        carregarDados();

        const outroInput = document.querySelector('input[name="objeto_outro"]');
        if (outroInput && outroInput.value.trim() !== '') {
            return {
                descricao: 'Outro: ' + outroInput.value.trim(),
                documentos_obrigatorios: []
            };
        }

        const radioSelecionado = document.querySelector('input[name="objetoDoRequerimento"]:checked');
        if (!radioSelecionado) return null;

        const descricao = radioSelecionado.value.trim();
        return dadosAssuntos.find(a => (a.descricao || '').trim().toLowerCase() === descricao.toLowerCase()) || {
            descricao: descricao,
            documentos_obrigatorios: []
        };
    }

    function formatarTiposAceitos(tipos) {
        if (!tipos) return '.pdf,.png,.jpg,.jpeg';
        return tipos.split(',')
            .map(t => t.trim().toLowerCase())
            .map(t => t.startsWith('.') ? t : '.' + t)
            .join(',');
    }

    function renderizar() {
        const container = document.getElementById('documentos-requeridos-container');
        if (!container) return;

        const assunto = obterAssuntoAtual();
        const documentos = (assunto && Array.isArray(assunto.documentos_obrigatorios)) 
            ? assunto.documentos_obrigatorios 
            : [];

        if (documentos.length === 0) {
            container.innerHTML = '<p style="color: #666; font-size: 14px; margin-bottom: 10px;">Nenhum documento obrigatório exigido para este assunto.</p>';
            return;
        }

        let html = '';
        documentos.forEach((doc, idx) => {
            const aceitos = formatarTiposAceitos(doc.tipos_aceitos);
            const isObrigatorio = doc.obrigatorio !== false;

            html += `
                <div class="campo" style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px;">
                        ${escapeHtml(doc.nome)} 
                        ${isObrigatorio ? '<span style="color: #dc2626; font-weight: normal;">(Obrigatório)</span>' : '<span style="color: #6b7280; font-weight: normal;">(Opcional)</span>'}
                    </label>
                    ${doc.descricao ? `<small style="display: block; color: #666; margin-bottom: 6px; font-size: 12px;">${escapeHtml(doc.descricao)}</small>` : ''}
                    <input 
                        type="file" 
                        name="arquivos[]" 
                        accept="${aceitos}"
                        data-doc-nome="${escapeHtml(doc.nome)}"
                        ${isObrigatorio ? 'data-obrigatorio="true"' : ''}
                    >
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function validar() {
        const inputsObrigatorios = document.querySelectorAll('input[data-obrigatorio="true"]');
        for (const input of inputsObrigatorios) {
            if (!input.files || input.files.length === 0) {
                const docNome = input.getAttribute('data-doc-nome') || 'Documento';
                alert('Por favor, anexe o documento obrigatório: ' + docNome);
                input.focus();
                return false;
            }
        }
        return true;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function init() {
        carregarDados();

        document.querySelectorAll('input[name="objetoDoRequerimento"]').forEach(radio => {
            radio.addEventListener('change', renderizar);
        });

        const outroInput = document.querySelector('input[name="objeto_outro"]');
        if (outroInput) {
            outroInput.addEventListener('input', renderizar);
        }

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!validar()) {
                    e.preventDefault();
                }
            });
        }

        renderizar();
    }

    return {
        init: init,
        renderizar: renderizar,
        validar: validar
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    AnexosRequerimento.init();
});
