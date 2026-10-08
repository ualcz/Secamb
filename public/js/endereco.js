document.addEventListener('DOMContentLoaded', function () {
    console.log('Script de endereço carregado.');
    const cepInput = document.getElementById('cep');
    const oldCidade = document.getElementById('cidade')?.value || '';
    const oldUf = document.getElementById('uf')?.value || '';

    if (!cepInput) return;

    cepInput.addEventListener('blur', function () {
        // Remove caracteres não numéricos
        let cep = this.value.replace(/\D/g, '');

        // Verifica se o CEP possui 8 dígitos
        if (cep.length === 8) {
            // Preenche os campos com indicador de carregamento (caso existam)
            setInputValue('logradouro', 'Carregando...');
            setInputValue('bairro', 'Carregando...');
            setInputValue('cidade', 'Carregando...');
            setInputValue('uf', '...');

            // Consulta a API do ViaCEP
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        // alert('CEP não encontrado!');
                        limparFormulario();
                    } else {
                        // Preenche com o retorno da API
                        setInputValue('logradouro', data.logradouro);
                        setInputValue('bairro', data.bairro);
                        setInputValue('cidade', data.localidade);
                        setInputValue('uf', data.uf);

                        // Foco no campo de número se ele existir
                        document.getElementById('numero')?.focus();
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar o CEP:', error);
                    // alert('Erro ao consultar o CEP. Tente novamente.');
                    limparFormulario();
                });
        } else if (cep.length > 0) {
            alert('Formato de CEP inválido.');
            limparFormulario();
        }
    });

    function setInputValue(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val;
    }

    function limparFormulario() {
        setInputValue('logradouro', '');
        setInputValue('bairro', '');
        setInputValue('cidade', oldCidade);
        setInputValue('uf', oldUf);
    }
});
