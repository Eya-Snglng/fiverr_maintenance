<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo = get_pdo();
$body = read_request_body();
$action = sanitize_string($_GET['action'] ?? $body['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($action === '') {
    json_response(['ok' => false, 'error' => 'NO_ACTION'], 400);
}

try {
    switch ($action) {
        case 'me': {
            $user = $_SESSION['user'] ?? null;
            if (!$user) {
                json_response(['ok' => false, 'error' => 'AUTH_REQUIRED'], 401);
            }
            json_response(['ok' => true, 'user' => $user]);
            break;
        }
        case 'logout': {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION = [];
                if (ini_get('session.use_cookies')) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params['path'], $params['domain'], $params['secure'] ?? false, $params['httponly'] ?? true);
                }
                session_destroy();
            }
            json_response(['ok' => true]);
            break;
        }
        case 'check_username': {
            $username = sanitize_string($body['username'] ?? $_GET['username'] ?? '');
            if ($username === '') {
                json_response(['ok' => false, 'error' => 'USERNAME_REQUIRED'], 400);
            }
            $existing = fetch_user_by_username($pdo, $username);
            json_response(['ok' => true, 'available' => $existing ? false : true]);
            break;
        }
        case 'register': {
            if ($method !== 'POST') {
                json_response(['ok' => false, 'error' => 'METHOD_NOT_ALLOWED'], 405);
            }
            $username = sanitize_string($body['username'] ?? '');
            $firstname = sanitize_string($body['firstname'] ?? '');
            $lastname = sanitize_string($body['lastname'] ?? '');
            $password = (string)($body['password'] ?? '');

            if ($username === '' || $firstname === '' || $lastname === '' || $password === '') {
                json_response(['ok' => false, 'error' => 'EMPTY_FIELDS'], 400);
            }
            if (strlen($password) < 8) {
                json_response(['ok' => false, 'error' => 'WEAK_PASSWORD'], 400);
            }
            if (fetch_user_by_username($pdo, $username)) {
                json_response(['ok' => false, 'error' => 'USERNAME_TAKEN'], 409);
            }
            $isAdmin = to_bool($body['is_admin'] ?? false);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $user = create_user($pdo, [
                'username' => $username,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'is_admin' => $isAdmin,
                'password' => $hash,
            ]);
            json_response(['ok' => true, 'user' => $user]);
            break;
        }
        case 'login': {
            if ($method !== 'POST') {
                json_response(['ok' => false, 'error' => 'METHOD_NOT_ALLOWED'], 405);
            }
            $username = sanitize_string($body['username'] ?? '');
            $password = (string)($body['password'] ?? '');
            if ($username === '' || $password === '') {
                json_response(['ok' => false, 'error' => 'EMPTY_FIELDS'], 400);
            }
            $row = fetch_user_by_username($pdo, $username);
            if (!$row || !password_verify($password, (string)$row['password'])) {
                json_response(['ok' => false, 'error' => 'INVALID_CREDENTIALS'], 401);
            }
            // Build user object without password
            $user = [
                'id' => (int)$row['id'],
                'username' => (string)$row['username'],
                'firstname' => (string)$row['firstname'],
                'lastname' => (string)$row['lastname'],
                'is_admin' => (bool)$row['is_admin'],
            ];
            session_set_user($user);
            json_response(['ok' => true, 'user' => $user]);
            break;
        }
        case 'list_users': {
            require_admin();
            $q = sanitize_string($_GET['q'] ?? $body['q'] ?? '');
            $limit = 500;
            if ($q === '') {
                $stmt = $pdo->query('SELECT id, username, firstname, lastname, is_admin, date_added FROM users ORDER BY date_added DESC LIMIT ' . $limit);
                $users = $stmt->fetchAll();
                json_response(['ok' => true, 'users' => $users]);
            } else {
                $stmt = $pdo->prepare('SELECT id, username, firstname, lastname, is_admin, date_added FROM users WHERE username LIKE :q OR firstname LIKE :q OR lastname LIKE :q ORDER BY date_added DESC LIMIT ' . $limit);
                $stmt->execute([':q' => '%' . $q . '%']);
                $users = $stmt->fetchAll();
                json_response(['ok' => true, 'users' => $users]);
            }
            break;
        }
        case 'add_user': {
            require_admin();
            if ($method !== 'POST') {
                json_response(['ok' => false, 'error' => 'METHOD_NOT_ALLOWED'], 405);
            }
            $username = sanitize_string($body['username'] ?? '');
            $firstname = sanitize_string($body['firstname'] ?? '');
            $lastname = sanitize_string($body['lastname'] ?? '');
            $password = (string)($body['password'] ?? '');
            $isAdmin = to_bool($body['is_admin'] ?? false);

            if ($username === '' || $firstname === '' || $lastname === '' || $password === '') {
                json_response(['ok' => false, 'error' => 'EMPTY_FIELDS'], 400);
            }
            if (strlen($password) < 8) {
                json_response(['ok' => false, 'error' => 'WEAK_PASSWORD'], 400);
            }
            if (fetch_user_by_username($pdo, $username)) {
                json_response(['ok' => false, 'error' => 'USERNAME_TAKEN'], 409);
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $user = create_user($pdo, [
                'username' => $username,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'is_admin' => $isAdmin,
                'password' => $hash,
            ]);
            json_response(['ok' => true, 'user' => $user]);
            break;
        }
        case 'delete_user': {
            require_admin();
            if ($method !== 'POST') {
                json_response(['ok' => false, 'error' => 'METHOD_NOT_ALLOWED'], 405);
            }
            $id = (int)($body['id'] ?? 0);
            if ($id <= 0) {
                json_response(['ok' => false, 'error' => 'INVALID_ID'], 400);
            }
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            json_response(['ok' => true]);
            break;
        }
        default: {
            json_response(['ok' => false, 'error' => 'UNKNOWN_ACTION'], 400);
        }
    }
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'SERVER_ERROR', 'message' => $e->getMessage()], 500);
}
