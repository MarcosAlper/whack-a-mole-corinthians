<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

api_run(static function (): void {
    require_method('POST');
    require_csrf();
    $data = request_data();
    $name = trim((string) ($data['nome'] ?? ''));
    $birthDate = (string) ($data['nascimento'] ?? '');
    $cpf = preg_replace('/\D/', '', (string) ($data['cpf'] ?? ''));
    $phone = preg_replace('/\D/', '', (string) ($data['telefone'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $username = trim((string) ($data['username'] ?? ''));
    $password = (string) ($data['senha'] ?? '');

    $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate);
    if ($name === '' || preg_match_all('/./us', $name) > 150) {
        throw new ApiException('Informe um nome válido de até 150 caracteres.');
    }
    if (!$birth || $birth->format('Y-m-d') !== $birthDate || $birth > new DateTimeImmutable('today')) {
        throw new ApiException('Informe uma data de nascimento válida.');
    }
    if (strlen($cpf) !== 11) {
        throw new ApiException('O CPF deve conter 11 números.');
    }
    if (strlen($phone) < 10 || strlen($phone) > 11) {
        throw new ApiException('O telefone deve conter 10 ou 11 números, incluindo o DDD.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        throw new ApiException('Informe um e-mail válido.');
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        throw new ApiException('O username deve ter de 3 a 30 caracteres: letras, números ou sublinhado.');
    }
    if (preg_match_all('/./us', $password) < 8) {
        throw new ApiException('A senha deve conter pelo menos 8 caracteres.');
    }
    if (strlen($password) > 72) {
        throw new ApiException('A senha deve ter no máximo 72 bytes.');
    }

    try {
        $statement = database()->prepare(
            'INSERT INTO users (full_name, birth_date, cpf, phone, email, username, password_hash)
             VALUES (:full_name, :birth_date, :cpf, :phone, :email, :username, :password_hash)'
        );
        $statement->execute([
            'full_name' => $name,
            'birth_date' => $birthDate,
            'cpf' => $cpf,
            'phone' => $phone,
            'email' => $email,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            throw new ApiException('Username ou CPF já cadastrado.', 409);
        }
        throw $error;
    }

    api_response(['message' => 'Cadastro realizado. Agora entre com seu username e senha.'], 201);
});
