const profileForm = document.querySelector("#profile-form");
const profileMessage = document.querySelector("#profile-message");
const phoneInput = document.querySelector("#profile-phone");
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function loadProfile() {
    const response = await fetch("api/profile.php");
    const data = await response.json();
    if (response.status === 401) {
        window.location.href = "index.html";
        return;
    }
    if (!response.ok) throw new Error(data.error || "Não foi possível carregar o perfil.");

    const user = data.user;
    document.querySelector("#profile-name").value = user.full_name;
    document.querySelector("#profile-email").value = user.email;
    document.querySelector("#profile-phone").value = user.phone;
    document.querySelector("#profile-birth").value = user.birth_date;
    document.querySelector("#profile-cpf").value = user.cpf;
    document.querySelector("#profile-username").value = user.username;
    document.querySelector("#profile-display-name").textContent = user.full_name;
    document.querySelector("#profile-display-email").textContent = user.email;
    document.querySelector("#profile-display-phone").textContent = user.phone;
    document.querySelector("#profile-display-birth").textContent = user.birth_date;
    document.querySelector("#profile-display-cpf").textContent = user.cpf;
    document.querySelector("#profile-display-username").textContent = user.username;
}

loadProfile().catch((error) => {
    profileMessage.textContent = error.message;
    profileMessage.classList.add("message-error");
});

phoneInput?.addEventListener("input", () => {
    phoneInput.value = phoneInput.value.replace(/\D/g, "").slice(0, 11);
});

profileForm?.addEventListener("submit", async (event) => {
    event.preventDefault();
    const button = profileForm.querySelector('button[type="submit"]');
    button.disabled = true;
    profileMessage.textContent = "";
    try {
        const response = await fetch("api/profile.php", {
            method: "PATCH",
            headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
            body: JSON.stringify({
                nome: profileForm.elements.nome.value,
                email: profileForm.elements.email.value,
                telefone: profileForm.elements.telefone.value
            })
        });
        const data = await response.json();
        if (response.status === 401) {
            window.location.href = "index.html";
            return;
        }
        if (!response.ok) throw new Error(data.error || "Não foi possível salvar as alterações.");
        await loadProfile();
        profileMessage.textContent = data.message;
    } catch (error) {
        profileMessage.textContent = error.message;
        profileMessage.classList.add("message-error");
    } finally {
        button.disabled = false;
    }
});
