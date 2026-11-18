<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
    'path' => '/',
]);

function load_environment_file(): void
{
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('Não foi possível ler o arquivo .env.');
    }

    $allowedKeys = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
    foreach ($lines as $lineNumber => $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!preg_match('/^([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $matches)
            || !in_array($matches[1], $allowedKeys, true)) {
            throw new RuntimeException('Formato inválido no arquivo .env, linha ' . ($lineNumber + 1) . '.');
        }

        $key = $matches[1];
        if (getenv($key) !== false) {
            continue;
        }

        $value = trim($matches[2]);
        if (strlen($value) >= 2 && (
            ($value[0] === '"' && $value[-1] === '"')
            || ($value[0] === "'" && $value[-1] === "'")
        )) {
            $value = substr($value, 1, -1);
        }
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

load_environment_file();
session_start();

final class ApiException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 400)
    {
        parent::__construct($message);
    }
}

function database(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'acerte_a_toupeira';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET time_zone = '+00:00'");

    return $pdo;
}

function api_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_run(callable $handler): void
{
    header('Cache-Control: no-store');
    try {
        $handler();
    } catch (ApiException $error) {
        api_response(['error' => $error->getMessage()], $error->status);
    } catch (PDOException $error) {
        error_log((string) $error);
        api_response(['error' => 'Não foi possível acessar o banco de dados.'], 500);
    } catch (Throwable $error) {
        error_log((string) $error);
        api_response(['error' => 'Ocorreu um erro interno.'], 500);
    }
}

function require_method(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        throw new ApiException('Método não permitido.', 405);
    }
}

function request_data(): array
{
    try {
        $data = json_decode((string) file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        throw new ApiException('Envie um JSON válido.');
    }

    if (!is_array($data)) {
        throw new ApiException('O corpo da requisição deve ser um objeto JSON.');
    }

    return $data;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function require_csrf(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        throw new ApiException('Sessão expirada. Atualize a página e tente novamente.', 419);
    }
}

function require_authenticated_user(): int
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!is_int($userId) || $userId < 1) {
        throw new ApiException('Faça login para continuar.', 401);
    }

    return $userId;
}

function require_authenticated_page(): void
{
    if (!isset($_SESSION['user_id']) || !is_int($_SESSION['user_id'])) {
        header('Location: index.html');
        exit;
    }
}

function html_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
