<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

api_run(static function (): void {
    $userId = require_authenticated_user();
    $pdo = database();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        $statement = $pdo->prepare(
            'SELECT full_name, birth_date, cpf, phone, email, username FROM users WHERE id = :id'
        );
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();
        if (!$user) {
            throw new ApiException('Conta não encontrada.', 404);
        }
        api_response(['user' => $user]);
    }

    require_method('PATCH');
    require_csrf();
    $data = request_data();
    $name = trim((string) ($data['nome'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $phone = preg_replace('/\D/', '', (string) ($data['telefone'] ?? ''));
    if ($name === '' || preg_match_all('/./us', $name) > 150) {
        throw new ApiException('Informe um nome válido de até 150 caracteres.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        throw new ApiException('Informe um e-mail válido.');
    }
    if (strlen($phone) < 10 || strlen($phone) > 11) {
        throw new ApiException('O telefone deve conter 10 ou 11 números, incluindo o DDD.');
    }

    $statement = $pdo->prepare(
        'UPDATE users SET full_name = :full_name, email = :email, phone = :phone WHERE id = :id'
    );
    $statement->execute([
        'full_name' => $name,
        'email' => $email,
        'phone' => $phone,
        'id' => $userId,
    ]);
    api_response(['message' => 'Informações atualizadas.']);
});
