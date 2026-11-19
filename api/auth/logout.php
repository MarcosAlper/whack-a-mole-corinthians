<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

api_run(static function (): void {
    require_method('POST');
    require_csrf();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
    header('Location: ../../index.html', true, 303);
    exit;
});
