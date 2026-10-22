const loginForm = document.querySelector("#login-form");

loginForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    window.location.href = "jogo.html";
});
