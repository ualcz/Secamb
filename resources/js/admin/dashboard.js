document.addEventListener("DOMContentLoaded", () => {

    const botoes = document.querySelectorAll(".botao");

    botoes.forEach(botao => {

        botao.addEventListener("mouseenter", () => {

            botao.style.transform = "scale(1.02)";

        });

        botao.addEventListener("mouseleave", () => {

            botao.style.transform = "scale(1)";

        });

    });

});