<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

function current_admin(): ?array
{
    static $admin = false;

    if ($admin !== false) {
        return $admin;
    }

    if (empty($_SESSION['admin_id'])) {
        $admin = null;
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, last_login_at, created_at FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $_SESSION['admin_id']]);
    $record = $stmt->fetch();

    if ($record === false) {
        unset($_SESSION['admin_id']);
        $admin = null;
        return null;
    }

    $admin = $record;

    return $admin;
}

function require_admin(): array
{
    $admin = current_admin();

    if ($admin === null) {
        redirect('/admin/login.php');
    }

    header('Cache-Control: no-store, private');

    return $admin;
}

function login_attempts(): array
{
    $config = app_config()['security'];
    $window = (int) $config['login_lockout_seconds'];
    $cutoff = time() - $window;
    $attempts = $_SESSION['login_attempts'] ?? [];

    if (!is_array($attempts)) {
        $attempts = [];
    }

    $attempts = array_values(array_filter(
        $attempts,
        static fn (mixed $timestamp): bool => is_int($timestamp) && $timestamp > $cutoff
    ));

    $_SESSION['login_attempts'] = $attempts;

    return $attempts;
}

function login_lockout_remaining(): int
{
    $config = app_config()['security'];
    $attempts = login_attempts();

    if (count($attempts) < (int) $config['login_max_attempts']) {
        return 0;
    }

    $oldest = min($attempts);

    return max(0, $oldest + (int) $config['login_lockout_seconds'] - time());
}

function record_login_failure(): void
{
    login_attempts();
    $_SESSION['login_attempts'][] = time();
}

function clear_login_failures(): void
{
    unset($_SESSION['login_attempts']);
}

function log_in_admin(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    unset($_SESSION['csrf_token']);
    clear_login_failures();

    $stmt = db()->prepare('UPDATE admins SET last_login_at = :last_login_at WHERE id = :id');
    $stmt->execute([
        ':last_login_at' => now()->format('Y-m-d H:i:s'),
        ':id' => (int) $admin['id'],
    ]);
}

function log_out_admin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}
