const registrationForm = document.querySelector("#registration-form");
const registrationMessage = document.querySelector("#registration-message");
const phoneInput = document.querySelector("#telefone");

phoneInput?.addEventListener("input", () => {
    phoneInput.value = phoneInput.value.replace(/\D/g, "").slice(0, 11);
});

registrationForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    registrationMessage.textContent = "Cadastro demonstrativo : nenhum dado foi salvo. Jogue sem criar conta.";
});
