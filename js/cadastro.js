const registrationForm = document.querySelector("#registration-form");
const registrationMessage = document.querySelector("#registration-message");
const phoneInput = document.querySelector("#telefone");
const cpfInput = document.querySelector("#cpf");
let csrfToken = "";

fetch("api/session.php")
    .then((response) => {
        if (!response.ok) throw new Error("Não foi possível iniciar a sessão.");
        return response.json();
    })
    .then((data) => {
        csrfToken = data.csrfToken;
    })
    .catch((error) => {
        registrationMessage.textContent = error.message;
        registrationMessage.classList.add("message-error");
    });

phoneInput?.addEventListener("input", () => {
    phoneInput.value = phoneInput.value.replace(/\D/g, "").slice(0, 11);
});

cpfInput?.addEventListener("input", () => {
    cpfInput.value = cpfInput.value.replace(/\D/g, "").slice(0, 11);
});

registrationForm?.addEventListener("submit", async (event) => {
    event.preventDefault();
    const button = registrationForm.querySelector('button[type="submit"]');
    button.disabled = true;
    registrationMessage.textContent = "";
    try {
        if (!csrfToken) throw new Error("Sessão indisponível. Atualize a página e tente novamente.");
        const formData = new FormData(registrationForm);
        const response = await fetch("api/auth/register.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || "Não foi possível concluir o cadastro.");
        window.location.href = "index.html?cadastro=sucesso";
    } catch (error) {
        registrationMessage.textContent = error.message;
        registrationMessage.classList.add("message-error");
    } finally {
        button.disabled = false;
    }
});
