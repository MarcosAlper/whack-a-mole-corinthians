const LEVEL_DURATION = 20;
const REQUIRED_ACCURACY = 90;
const board = document.querySelector("#game-board");

function setMessage(element, text, isError = false) {
    if (!element) return;
    element.textContent = text;
    element.classList.toggle("message-error", isError);
}

if (board) {
    const config = document.querySelector("#game-config");
    const rowsInput = document.querySelector("#linhas");
    const columnsInput = document.querySelector("#colunas");
    const modeInput = document.querySelector("#modo");
    const message = document.querySelector("#game-message");
    const startButton = document.querySelector("#start-game");
    const quitButton = document.querySelector("#quit-game");
    const timeOutput = document.querySelector("#time-left");
    const modeOutput = document.querySelector("#game-mode");
    const totalHitsOutput = document.querySelector("#total-hits");
    const accuracyOutput = document.querySelector("#accuracy");
    const levelOutput = document.querySelector("#level");
    const scoreOutput = document.querySelector("#score");
    const historyBody = document.querySelector("#history-body");
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const state = {
        running: false,
        level: 1,
        timeLeft: LEVEL_DURATION,
        totalHits: 0,
        score: 0,
        molesShown: 0,
        levelHits: 0,
        rows: 4,
        columns: 4,
        mode: "classica",
        activeHole: null,
        activeType: null,
        spawnTimer: null,
        clockTimer: null
    };

    function renderBoard() {
        board.replaceChildren();
        board.style.setProperty("--board-columns", state.columns);
        for (let index = 0; index < state.rows * state.columns; index += 1) {
            const hole = document.createElement("button");
            hole.type = "button";
            hole.className = "hole";
            hole.setAttribute("aria-label", `Buraco ${index + 1}, vazio`);
            const image = document.createElement("img");
            image.src = "assets/img/hole.webp";
            image.alt = "";
            image.className = "sprite-img";
            hole.append(image);
            hole.addEventListener("click", () => hitHole(hole));
            board.append(hole);
        }
    }

    function renderHistory(games) {
        historyBody.replaceChildren();
        if (games.length === 0) {
            const row = document.createElement("tr");
            const cell = document.createElement("td");
            cell.className = "history-empty";
            cell.colSpan = 6;
            cell.textContent = "Nenhuma partida jogada.";
            row.append(cell);
            historyBody.append(row);
            return;
        }

        games.forEach((game) => {
            const row = document.createElement("tr");
            const mode = game.mode === "classica" ? "Clássica" : "Explosiva";
            const playedAt = new Date(game.played_at).toLocaleString("pt-BR");
            [game.player_name, `${game.rows_count} x ${game.columns_count} (${game.rows_count * game.columns_count} buracos)`,
                mode, game.score, game.level_reached, playedAt].forEach((value) => {
                const cell = document.createElement("td");
                cell.textContent = value;
                row.append(cell);
            });
            historyBody.append(row);
        });
    }

    async function loadHistory() {
        const response = await fetch("api/games.php");
        const data = await response.json();
        if (response.status === 401) {
            window.location.href = "index.html";
            return;
        }
        if (!response.ok) throw new Error(data.error || "Não foi possível carregar o histórico.");
        renderHistory(data.games);
    }

    function clearTarget() {
        if (!state.activeHole) return;
        state.activeHole.classList.remove("hole-target", "hole-bomb");
        state.activeHole.setAttribute("aria-label", `Buraco ${Array.from(board.children).indexOf(state.activeHole) + 1}, vazio`);
        const image = state.activeHole.querySelector("img");
        image.src = "assets/img/hole.webp";
        image.alt = "";
        state.activeHole = null;
        state.activeType = null;
    }

    function updateHud() {
        const accuracy = state.molesShown ? Math.floor((state.levelHits / state.molesShown) * 100) : 0;
        timeOutput.textContent = `${state.timeLeft}s`;
        totalHitsOutput.textContent = state.totalHits;
        accuracyOutput.textContent = `${accuracy}% / ${REQUIRED_ACCURACY}%`;
        levelOutput.textContent = state.level;
        scoreOutput.textContent = state.score;
    }

    function hitHole(hole) {
        if (!state.running || hole !== state.activeHole) return;
        if (state.activeType === "mole") {
            state.totalHits += 1;
            state.levelHits += 1;
            state.score += 1;
            setMessage(message, "Toupeira acertada! +1 ponto.");
        } else {
            state.score -= 1;
            setMessage(message, "Bomba! -1 ponto.", true);
        }
        clearTimeout(state.spawnTimer);
        clearTarget();
        updateHud();
        scheduleTarget(250);
    }

    function showTarget() {
        if (!state.running) return;
        const holes = Array.from(board.children);
        const hole = holes[Math.floor(Math.random() * holes.length)];
        const isBomb = state.mode === "explosiva" && Math.random() < 0.25;
        state.activeHole = hole;
        state.activeType = isBomb ? "bomb" : "mole";
        if (!isBomb) state.molesShown += 1;
        hole.classList.add(isBomb ? "hole-bomb" : "hole-target");
        hole.setAttribute("aria-label", isBomb ? "Bomba, clique para penalidade" : "Toupeira, clique para acertar");
        const image = hole.querySelector("img");
        image.src = isBomb ? "assets/img/bomba.webp" : "assets/img/toupeira1.png";
        image.alt = isBomb ? "Bomba" : "Toupeira";
        updateHud();

        const visibleTime = Math.max(350, 1400 - ((state.level - 1) * 100));
        state.spawnTimer = window.setTimeout(() => {
            clearTarget();
            scheduleTarget();
        }, visibleTime);
    }

    function scheduleTarget(delay = 350 + Math.random() * 450) {
        if (!state.running) return;
        state.spawnTimer = window.setTimeout(showTarget, delay);
    }

    async function finishGame(reason) {
        if (!state.running) return;
        state.running = false;
        clearTimeout(state.spawnTimer);
        clearInterval(state.clockTimer);
        clearTarget();
        quitButton.disabled = true;
        startButton.disabled = true;
        const game = {
            rows: state.rows,
            columns: state.columns,
            mode: state.mode,
            score: state.score,
            level: state.level,
            totalHits: state.totalHits
        };
        const endText = reason === "quit" ? "Partida encerrada." : "Fim de jogo: mínimo de acertos não atingido.";
        const resultText = `${endText} Resultado: ${game.totalHits} toupeiras, ${game.score} pontos, nível ${game.level}.`;
        setMessage(message, `${resultText} Salvando...`);
        try {
            const response = await fetch("api/games.php", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
                body: JSON.stringify(game)
            });
            const data = await response.json();
            if (response.status === 401) {
                window.location.href = "index.html";
                return;
            }
            if (!response.ok) throw new Error(data.error || "Não foi possível registrar a partida.");
            await loadHistory();
            setMessage(message, `${resultText} Partida registrada.`);
        } catch (error) {
            setMessage(message, `${resultText} ${error.message}`, true);
        } finally {
            startButton.disabled = false;
        }
    }

    function startLevel() {
        state.timeLeft = LEVEL_DURATION;
        state.molesShown = 0;
        state.levelHits = 0;
        updateHud();
        setMessage(message, `Nível ${state.level}: acerte pelo menos ${REQUIRED_ACCURACY}% das toupeiras em ${LEVEL_DURATION} segundos.`);
        scheduleTarget(250);
        state.clockTimer = window.setInterval(() => {
            state.timeLeft -= 1;
            updateHud();
            if (state.timeLeft <= 0) {
                clearInterval(state.clockTimer);
                clearTimeout(state.spawnTimer);
                clearTarget();
                const accuracy = state.molesShown ? (state.levelHits / state.molesShown) * 100 : 0;
                if (accuracy >= REQUIRED_ACCURACY) {
                    state.level += 1;
                    updateHud();
                    startLevel();
                } else {
                    finishGame("failed");
                }
            }
        }, 1000);
    }

    config.addEventListener("submit", (event) => {
        event.preventDefault();
        const rows = Number(rowsInput.value);
        const columns = Number(columnsInput.value);
        const holes = rows * columns;
        if (!Number.isInteger(rows) || !Number.isInteger(columns)
            || rows < 2 || rows > 8 || columns < 2 || columns > 8
            || holes < 4 || holes > 64) {
            setMessage(message, "Use de 2 a 8 linhas e colunas, com uma arena de 4 a 64 buracos.", true);
            return;
        }
        if (state.running) return;

        state.rows = rows;
        state.columns = columns;
        state.mode = modeInput.value;
        state.level = 1;
        state.totalHits = 0;
        state.score = 0;
        state.running = true;
        modeOutput.textContent = state.mode === "classica" ? "Clássico" : "Explosivo";
        renderBoard();
        updateHud();
        startButton.disabled = true;
        quitButton.disabled = false;
        startLevel();
    });

    quitButton.addEventListener("click", () => finishGame("quit"));
    
    renderBoard();
    loadHistory().catch((error) => {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = 6;
        cell.textContent = error.message;
        row.append(cell);
        historyBody.replaceChildren(row);
    });
    modeOutput.textContent = "Clássico";
}
