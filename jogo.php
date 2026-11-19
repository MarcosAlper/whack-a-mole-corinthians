<?php
declare(strict_types=1);
require __DIR__ . '/api/bootstrap.php';
require_authenticated_page();
$currentPage = 'game';
$heading = 'ACERTE AS TOUPEIRAS!!!';
$logo = 'assets/img/controle.webp';
$logoAlt = 'Logo Controle';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= html_escape(csrf_token()) ?>">
    <title>Jogo - Acerte a Toupeira</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-page="game">
    <?php require __DIR__ . '/includes/nav.php'; ?>
    <main>
        <section>
            <h2><img src="assets/img/engrenagem.webp" alt="" class="icon-inline"> CONFIGURE SUA PARTIDA</h2>
            <form id="game-config" action="#">
                <label for="linhas">Linhas</label>
                <input type="number" id="linhas" name="linhas" min="2" max="8" value="4" required>

                <label for="colunas">Colunas</label>
                <input type="number" id="colunas" name="colunas" min="2" max="8" value="4" required>
                <p id="board-size-hint">Linhas e colunas devem ficar entre 2 e 8; a arena deve ter de 4 a 64 buracos.</p>

                <label for="modo">Modo de jogo</label>
                <select id="modo" name="modo">
                    <option value="classica">Clássico</option>
                    <option value="explosiva">Explosivo</option>
                </select>

                <button type="submit" id="start-game"><img src="assets/img/hammer.png" alt="" class="icon-inline"> INICIAR PARTIDA</button>
                <button type="button" id="quit-game" class="btn-danger" disabled>DESISTIR</button>
            </form>
        </section>

        <p id="game-message" role="status" aria-live="polite">Configure sua arena e inicie uma partida.</p>

        <div class="hud" aria-live="polite">
            <div>
                <img src="assets/img/relogio.png" alt="" class="icon-inline"> TEMPO DO NÍVEL: <strong id="time-left">20s</strong>
            </div>
            <div>
                <img src="assets/img/bomba.webp" alt="" class="icon-inline"> MODO: <strong id="game-mode">Clássico</strong>
            </div>
            <div>
                <img src="assets/img/toupeira3.webp" alt="" class="icon-inline"> ACERTOS: <strong id="total-hits">0</strong>
            </div>
            <div>
                <img src="assets/img/percent.png" alt="" class="icon-inline"> PRECISÃO DO NÍVEL: <strong id="accuracy">0% / 90%</strong>
            </div>
            <div>NÍVEL: <strong id="level">1</strong> | PONTUAÇÃO: <strong id="score">0</strong></div>
        </div>

        <div id="game-board" class="board-mock" role="group" aria-label="Tabuleiro do jogo"></div>

        <section id="session-history" class="session-history">
            <h2><img src="assets/img/prancheta.webp" alt="" class="icon-inline"> HISTÓRICO DE PARTIDAS</h2>
            <p class="history-note">Histórico permanente das suas partidas.</p>
            <div class="history-table-wrap">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Jogador</th>
                            <th>Arena</th>
                            <th>Modo</th>
                            <th>Pontuação</th>
                            <th>Nível</th>
                            <th>Data/Hora</th>
                        </tr>
                    </thead>
                    <tbody id="history-body">
                        <tr><td class="history-empty" colspan="6">Carregando histórico...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script src="js/jogo.js" defer></script>
</body>
</html>
