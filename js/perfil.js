const profileForm = document.querySelector("#profile-form");
const profileMessage = document.querySelector("#profile-message");

profileForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    profileMessage.textContent = "Alterações demonstrativas: nenhum dado foi enviado ou salvo.";
});
