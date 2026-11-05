const profileForm = document.querySelector("#profile-form");
const profileMessage = document.querySelector("#profile-message");
const phoneInput = document.querySelector("#profile-phone");

phoneInput?.addEventListener("input", () => {
    phoneInput.value = phoneInput.value.replace(/\D/g, "").slice(0, 11);
});

profileForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    profileMessage.textContent = "Alterações demonstrativas: nenhum dado foi enviado ou salvo.";
});
