const rankingMessage = document.querySelector("#ranking-message");

function renderRanking(tbody, games) {
    tbody.replaceChildren();
    if (games.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = 7;
        cell.textContent = "Sem resultados registrados.";
        row.append(cell);
        tbody.append(row);
        return;
    }

    games.forEach((game, index) => {
        const row = document.createElement("tr");
        const playedAt = new Date(game.played_at).toLocaleString("pt-BR");
        [
            index + 1,
            game.username,
            `${game.rows_count} x ${game.columns_count}`,
            game.total_hits,
            game.level_reached,
            game.score,
            playedAt
        ].forEach((value) => {
            const cell = document.createElement("td");
            cell.textContent = value;
            row.append(cell);
        });
        tbody.append(row);
    });
}

fetch("api/ranking.php")
    .then(async (response) => {
        const data = await response.json();
        if (response.status === 401) {
            window.location.href = "index.html";
            return null;
        }
        if (!response.ok) throw new Error(data.error || "Não foi possível carregar o ranking.");
        return data.ranking;
    })
    .then((ranking) => {
        if (!ranking) return;
        renderRanking(document.querySelector("#ranking-classica"), ranking.classica);
        renderRanking(document.querySelector("#ranking-explosiva"), ranking.explosiva);
        rankingMessage.textContent = "Melhor resultado de cada jogador por modalidade; desempate por nível, acertos e data. Exibindo os 10 primeiros.";
    })
    .catch((error) => {
        rankingMessage.textContent = error.message;
        rankingMessage.classList.add("message-error");
    });
