<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

api_run(static function (): void {
    require_method('GET');
    api_response([
        'authenticated' => isset($_SESSION['user_id']),
        'csrfToken' => csrf_token(),
    ]);
});
