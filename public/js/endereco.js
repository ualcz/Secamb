document.addEventListener('DOMContentLoaded', function () {
    const cepInput = document.getElementById('cep');
    if (!cepInput) return;

    cepInput.addEventListener('blur', function () {
        const cep = this.value.replace(/\D/g, '');
        if (!cep) return;
        if (cep.length !== 8) {
            setFeedback('Informe um CEP válido com 8 números.', true);
            return;
        }

        setFeedback('Consultando CEP...');

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => {
                if (!response.ok) throw new Error('Falha na consulta ao ViaCEP.');
                return response.json();
            })
            .then(data => {
                if (data.erro) {
                    setFeedback('CEP não encontrado.', true);
                    return;
                }

                setInputValue('logradouro', data.logradouro);
                setInputValue('endereco', data.logradouro);
                setInputValue('bairro', data.bairro);
                setInputValue('cidade', data.localidade);
                setInputValue('uf', data.uf);
                setInputValue('estado', data.uf);
                setFeedback('Endereço preenchido pelo CEP.');
            })
            .catch(error => {
                console.error('Erro ao buscar o CEP:', error);
                setFeedback('Não foi possível consultar o CEP. Tente novamente.', true);
            });
    });

    function setInputValue(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val;
    }

    function setFeedback(message, isError = false) {
        const feedback = document.getElementById('cepFeedback');
        if (!feedback) return;
        feedback.textContent = message;
        feedback.setAttribute('role', isError ? 'alert' : 'status');
    }
});
