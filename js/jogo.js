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
    const sessionHistory = [];

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


    config.addEventListener("submit", (event) => {
        event.preventDefault();
        const rows = Number(rowsInput.value);
        const columns = Number(columnsInput.value);
        const holes = rows * columns;
        if (!Number.isInteger(rows) || !Number.isInteger(columns) || holes < 4 || holes > 64) {
            setMessage(message, "Escolha dimensões cujo produto fique entre 4 e 64 buracos.", true);
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
    })

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
}