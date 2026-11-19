<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

api_run(static function (): void {
    require_method('POST');
    require_csrf();
    $data = request_data();
    $username = trim((string) ($data['username'] ?? ''));
    $password = (string) ($data['password'] ?? '');
    if ($username === '' || $password === '') {
        throw new ApiException('Informe o username e a senha.');
    }

    $statement = database()->prepare('SELECT id, username, password_hash FROM users WHERE username = :username');
    $statement->execute(['username' => $username]);
    $user = $statement->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        throw new ApiException('Username ou senha incorretos.', 401);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    unset($_SESSION['csrf_token']);
    api_response(['message' => 'Login realizado.', 'redirect' => 'jogo.php']);
});
