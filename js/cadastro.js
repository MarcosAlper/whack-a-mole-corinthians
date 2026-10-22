const registrationForm = document.querySelector("#registration-form");
const registrationMessage = document.querySelector("#registration-message");

registrationForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    registrationMessage.textContent = "Cadastro demonstrativo : nenhum dado foi salvo. Jogue sem criar conta.";
});
