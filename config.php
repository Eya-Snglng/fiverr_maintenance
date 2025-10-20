<?php

declare(strict_types=1);

// Global app setup: sessions, timezone, and multibyte settings
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (PHP_SAPI !== 'cli') {
    // Let PHP decide secure flag based on HTTPS
    if (!headers_sent()) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

mb_internal_encoding('UTF-8');
@date_default_timezone_set('UTC');

// Database configuration via environment variables
// Expected for MySQL: DB_DRIVER=mysql, DB_HOST, DB_PORT (optional), DB_NAME, DB_USER, DB_PASS
// You can also set DB_DRIVER=sqlite and DB_PATH to use SQLite for quick testing
function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $driver = getenv('DB_DRIVER') ?: 'mysql';

    if ($driver === 'sqlite') {
        $dbPath = getenv('DB_PATH') ?: __DIR__ . '/app.sqlite';
        $dsn = 'sqlite:' . $dbPath;
        $pdo = new PDO($dsn);
    } else {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $db   = getenv('DB_NAME') ?: 'app_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $pdo = new PDO($dsn, $user, $pass);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}

function read_request_body(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    // Fallback to form-encoded
    return $_POST ?: [];
}

function json_response(array $data, int $status = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
    }
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function require_login(): array {
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        json_response(['ok' => false, 'error' => 'AUTH_REQUIRED'], 401);
    }
    return $_SESSION['user'];
}

function require_admin(): array {
    $user = require_login();
    if (empty($user['is_admin'])) {
        json_response(['ok' => false, 'error' => 'ADMIN_ONLY'], 403);
    }
    return $user;
}

function sanitize_string(?string $value): string {
    return trim((string)($value ?? ''));
}

function to_bool($value): bool {
    if (is_bool($value)) return $value;
    if (is_int($value)) return $value === 1;
    $v = strtolower((string)$value);
    return in_array($v, ['1', 'true', 'on', 'yes'], true);
}

function fetch_user_by_username(PDO $pdo, string $username): ?array {
    $stmt = $pdo->prepare('SELECT id, username, firstname, lastname, is_admin, password, date_added FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function create_user(PDO $pdo, array $fields): array {
    $stmt = $pdo->prepare('INSERT INTO users (username, firstname, lastname, is_admin, password) VALUES (:username, :firstname, :lastname, :is_admin, :password)');
    $stmt->execute([
        ':username' => $fields['username'],
        ':firstname' => $fields['firstname'],
        ':lastname' => $fields['lastname'],
        ':is_admin' => $fields['is_admin'] ? 1 : 0,
        ':password' => $fields['password'], // stores password hash
    ]);
    $id = (int)$pdo->lastInsertId();
    $stmt2 = $pdo->prepare('SELECT id, username, firstname, lastname, is_admin, date_added FROM users WHERE id = :id');
    $stmt2->execute([':id' => $id]);
    $user = $stmt2->fetch();
    if (!$user) {
        throw new RuntimeException('Failed to load created user');
    }
    return $user;
}

function session_set_user(array $user): void {
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => (string)$user['username'],
        'firstname' => (string)$user['firstname'],
        'lastname' => (string)$user['lastname'],
        'is_admin' => (bool)$user['is_admin'],
    ];
}

