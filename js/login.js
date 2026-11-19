const loginForm = document.querySelector("#login-form");
const loginMessage = document.querySelector("#login-message");
let csrfToken = "";

if (new URLSearchParams(window.location.search).get("cadastro") === "sucesso") {
    loginMessage.textContent = "Cadastro concluído. Entre com seu username e senha.";
}

fetch("api/session.php")
    .then((response) => {
        if (!response.ok) throw new Error("Não foi possível iniciar a sessão.");
        return response.json();
    })
    .then((data) => {
        csrfToken = data.csrfToken;
        if (data.authenticated) window.location.href = "jogo.php";
    })
    .catch((error) => {
        loginMessage.textContent = error.message;
        loginMessage.classList.add("message-error");
    });

loginForm?.addEventListener("submit", async (event) => {
    event.preventDefault();
    const button = loginForm.querySelector('button[type="submit"]');
    button.disabled = true;
    loginMessage.textContent = "";
    try {
        if (!csrfToken) throw new Error("Sessão indisponível. Atualize a página e tente novamente.");
        const response = await fetch("api/auth/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
            body: JSON.stringify({
                username: loginForm.elements.username.value,
                password: loginForm.elements.senha.value
            })
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || "Não foi possível entrar.");
        window.location.href = data.redirect;
    } catch (error) {
        loginMessage.textContent = error.message;
        loginMessage.classList.add("message-error");
    } finally {
        button.disabled = false;
    }
});
