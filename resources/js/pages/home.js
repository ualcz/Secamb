document.addEventListener('DOMContentLoaded', () => {
    // 1. Observer para revelar elementos (fade-up e fade-right) ao rolar a página
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.fade-up, .fade-right').forEach(el => {
        observer.observe(el);
    });

    // 2. Rolagem suave para os links do menu e âncoras
    const links = document.querySelectorAll("a[href^='#'], .scroll-link");
    links.forEach(link => {
        link.addEventListener("click", e => {
            const targetUrl = new URL(link.href, window.location.href);
            const target = targetUrl.hash ? document.querySelector(targetUrl.hash) : null;
            if (target && targetUrl.pathname === window.location.pathname) {
                e.preventDefault();
                target.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    });

    // 3. Destaque do menu ativo durante o scroll
    function activateLinkOnScroll() {
        let scrollY = window.scrollY;
        links.forEach(link => {
            const targetUrl = new URL(link.href, window.location.href);
            if (!targetUrl.hash || targetUrl.pathname !== window.location.pathname) return;

            const section = document.querySelector(targetUrl.hash);
            if (!section) return;

            if (
                section.offsetTop <= scrollY + 100 &&
                section.offsetTop + section.offsetHeight > scrollY + 100
            ) {
                link.classList.add("active");
            } else {
                link.classList.remove("active");
            }
        });
    }

    activateLinkOnScroll();
    window.addEventListener("scroll", activateLinkOnScroll);

    // 4. Fechar modais de erro e sucesso automaticamente após 3.5 segundos
    setTimeout(() => {
        closeModal('modal-error');
        closeModal('modal-success');

        // Esconder alert secundário caso exista na tela
        const alert = document.getElementById('alert-feedback');
        if (alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 3500);
});

// Função global para fechar modais ao clicar neles ou via timeout
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 300);
    }
}

window.closeModal = closeModal;
