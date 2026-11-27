const toggleSenha = document.getElementById("toggleSenha");
const senha = document.getElementById("senha");

toggleSenha.addEventListener("click", () => {

    if (senha.type === "password") {

        senha.type = "text";

        toggleSenha.classList.remove("fa-eye");
        toggleSenha.classList.add("fa-eye-slash");

    } else {

        senha.type = "password";

        toggleSenha.classList.remove("fa-eye-slash");
        toggleSenha.classList.add("fa-eye");

    }

});