<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

api_run(static function (): void {
    $userId = require_authenticated_user();
    $pdo = database();
    $method = $_SERVER['REQUEST_METHOD'] ?? '';

    if ($method === 'GET') {
        $statement = $pdo->prepare(
            "SELECT u.full_name AS player_name, g.rows_count, g.columns_count, g.mode,
                    g.score, g.level_reached, g.total_hits,
                    DATE_FORMAT(g.played_at, '%Y-%m-%dT%H:%i:%sZ') AS played_at
             FROM games g
             JOIN users u ON u.id = g.user_id
             WHERE g.user_id = :user_id
             ORDER BY g.played_at DESC, g.id DESC"
        );
        $statement->execute(['user_id' => $userId]);
        api_response(['games' => $statement->fetchAll()]);
    }

    require_method('POST');
    require_csrf();
    $data = request_data();
    $rows = filter_var($data['rows'] ?? null, FILTER_VALIDATE_INT);
    $columns = filter_var($data['columns'] ?? null, FILTER_VALIDATE_INT);
    $score = filter_var($data['score'] ?? null, FILTER_VALIDATE_INT);
    $level = filter_var($data['level'] ?? null, FILTER_VALIDATE_INT);
    $hits = filter_var($data['totalHits'] ?? null, FILTER_VALIDATE_INT);
    $mode = $data['mode'] ?? null;

    if ($rows === false || $columns === false || $rows < 2 || $rows > 8
        || $columns < 2 || $columns > 8 || $rows * $columns < 4 || $rows * $columns > 64) {
        throw new ApiException('Dimensões de tabuleiro inválidas.');
    }
    if (!in_array($mode, ['classica', 'explosiva'], true)) {
        throw new ApiException('Modo de jogo inválido.');
    }
    if ($score === false || $score < -2147483648 || $score > 2147483647
        || $level === false || $level < 1 || $level > 4294967295
        || $hits === false || $hits < 0 || $hits > 4294967295
        || ($score !== false && $hits !== false && $score > $hits)) {
        throw new ApiException('Resultado de partida inválido.');
    }

    $statement = $pdo->prepare(
        'INSERT INTO games (user_id, rows_count, columns_count, mode, score, level_reached, total_hits)
         VALUES (:user_id, :rows_count, :columns_count, :mode, :score, :level_reached, :total_hits)'
    );
    $statement->execute([
        'user_id' => $userId,
        'rows_count' => $rows,
        'columns_count' => $columns,
        'mode' => $mode,
        'score' => $score,
        'level_reached' => $level,
        'total_hits' => $hits,
    ]);
    api_response(['message' => 'Partida registrada.'], 201);
});
