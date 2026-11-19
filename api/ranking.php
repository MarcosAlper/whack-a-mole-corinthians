<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

api_run(static function (): void {
    require_method('GET');
    require_authenticated_user();
    $statement = database()->prepare(
        "SELECT username, rows_count, columns_count, score, level_reached, total_hits, played_at
         FROM (
             SELECT u.username, g.rows_count, g.columns_count, g.score, g.level_reached,
                    g.total_hits,
                    DATE_FORMAT(g.played_at, '%Y-%m-%dT%H:%i:%sZ') AS played_at,
                    ROW_NUMBER() OVER (
                        PARTITION BY g.user_id
                        ORDER BY g.score DESC, g.level_reached DESC, g.total_hits DESC,
                                 g.played_at ASC, g.id ASC
                    ) AS player_position
             FROM games g
             JOIN users u ON u.id = g.user_id
             WHERE g.mode = :mode
         ) AS player_results
         WHERE player_position = 1
         ORDER BY score DESC, level_reached DESC, total_hits DESC, played_at ASC, username ASC
         LIMIT 10"
    );
    $ranking = ['classica' => [], 'explosiva' => []];
    foreach (array_keys($ranking) as $mode) {
        $statement->execute(['mode' => $mode]);
        $ranking[$mode] = $statement->fetchAll();
    }
    api_response(['ranking' => $ranking]);
});
