@props([
    'formId',
    'mensagens' => [
        'Enviando requisição...',
        'Validando informações...',
        'Processando anexos...',
        'Gerando dados do sistema...',
        'Finalizando solicitação...',
        'Quase lá! Só mais um instante...'
    ]
])

<style>
/* Estilos do Overlay de Carregamento */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(5px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
}

.loading-box {
    background: #ffffff;
    padding: 32px 28px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    text-align: center;
    max-width: 340px;
    width: 90%;
    animation: modalPop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalPop {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.spinner {
    width: 48px;
    height: 48px;
    border: 4px solid #e2e8f0;
    border-top: 4px solid #0284c7;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 16px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    font-size: 0.95rem;
    font-weight: 600;
    color: #334155;
    margin: 0;
    transition: opacity 0.2s ease;
}

/* Animação do "Certo" (Checkmark de Sucesso) */
.checkmark-wrap {
    width: 64px;
    height: 64px;
    margin: 0 auto 14px auto;
}

.checkmark-svg {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: block;
    stroke-width: 3.5;
    stroke: #16a34a;
    stroke-miterlimit: 10;
    animation: checkmarkScale 0.35s ease-in-out 0.8s both;
}

.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 3.5;
    stroke-miterlimit: 10;
    stroke: #16a34a;
    fill: #f0fdf4;
    animation: checkmarkStroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    stroke: #16a34a;
    stroke-width: 4.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    animation: checkmarkStroke 0.35s cubic-bezier(0.65, 0, 0.45, 1) 0.55s forwards;
}

@keyframes checkmarkStroke {
    100% { stroke-dashoffset: 0; }
}

@keyframes checkmarkScale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.12, 1.12, 1); }
}

.success-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #14532d;
    margin: 0 0 6px 0;
}

.success-protocolo {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0369a1;
    background: #e0f2fe;
    border-radius: 6px;
    padding: 5px 12px;
    display: inline-block;
    margin: 4px 0 12px 0;
}

.success-redirect {
    font-size: 0.82rem;
    color: #64748b;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.redirect-dots::after {
    content: '...';
    animation: dots 1.5s steps(4, end) infinite;
}

@keyframes dots {
    0%, 20% { content: ''; }
    40% { content: '.'; }
    60% { content: '..'; }
    80%, 100% { content: '...'; }
}
</style>

<div id="loadingOverlay" class="loading-overlay" style="display: none;" role="status" aria-hidden="true">
    <div class="loading-box">
        {{-- Estado 1: Carregamento com Spinner --}}
        <div id="loadingSpinnerState">
            <div class="spinner"></div>
            <p id="loadingText" class="loading-text">{{ $mensagens[0] }}</p>
        </div>

        {{-- Estado 2: Sucesso Visual com o "Certo" (Checkmark Animado) --}}
        <div id="loadingSuccessState" style="display: none;">
            <div class="checkmark-wrap">
                <svg class="checkmark-svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="24"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <h3 class="success-title">Requerimento Enviado!</h3>
            <div>
                <span id="loadingSuccessProtocolo" class="success-protocolo">Protocolo gerado com sucesso</span>
            </div>
            <p class="success-redirect">
                <span>Redirecionando para Meus Requerimentos</span>
                <span class="redirect-dots"></span>
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const formId = @json($formId);
        const mensagens = @json($mensagens);

        const form = document.getElementById(formId);
        const loadingOverlay = document.getElementById('loadingOverlay');
        const loadingSpinnerState = document.getElementById('loadingSpinnerState');
        const loadingSuccessState = document.getElementById('loadingSuccessState');
        const loadingText = document.getElementById('loadingText');
        const loadingSuccessProtocolo = document.getElementById('loadingSuccessProtocolo');

        if (form && loadingOverlay && loadingText) {
            let intervalId = null;

            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) {
                    return;
                }

                // Intercepta para exibir o spinner e logo após o "certo" animado de sucesso
                e.preventDefault();

                // Exibe o overlay em modo spinner
                loadingSpinnerState.style.display = 'block';
                loadingSuccessState.style.display = 'none';
                loadingOverlay.style.display = 'flex';
                loadingOverlay.setAttribute('aria-hidden', 'false');

                const btnSubmit = form.querySelector('button[type="submit"], input[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                }

                let index = 0;
                intervalId = setInterval(() => {
                    index = (index + 1) % mensagens.length;
                    loadingText.style.opacity = '0';
                    setTimeout(() => {
                        loadingText.textContent = mensagens[index];
                        loadingText.style.opacity = '1';
                    }, 200);
                }, 2000);

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async response => {
                    clearInterval(intervalId);

                    const data = await response.json().catch(() => null);

                    if (response.ok && data && data.success) {
                        // Transição suave para o estado de "Certo" (Sucesso visual)
                        loadingSpinnerState.style.display = 'none';
                        loadingSuccessState.style.display = 'block';

                        if (data.protocolo && loadingSuccessProtocolo) {
                            loadingSuccessProtocolo.textContent = `Protocolo nº ${data.protocolo}`;
                        }

                        // Aguarda 1.8 segundos exibindo o check para o usuário e redireciona
                        setTimeout(() => {
                            window.location.href = data.redirect || '{{ route("requerimentos.cidadao.meusRequerimentos") }}';
                        }, 1800);
                    } else {
                        // Se houver erro de validação ou do servidor
                        loadingOverlay.style.display = 'none';
                        if (btnSubmit) btnSubmit.disabled = false;

                        const mensagemErro = (data && (data.message || (data.errors && Object.values(data.errors).flat().join('\n')))) 
                            || 'Ocorreu um erro ao enviar o requerimento. Por favor, revise as informações.';

                        alert(mensagemErro);
                    }
                })
                .catch(() => {
                    // Fallback caso a requisição fetch falhe: envia tradicionalmente
                    clearInterval(intervalId);
                    form.submit();
                });
            });
        }
    });
</script>
