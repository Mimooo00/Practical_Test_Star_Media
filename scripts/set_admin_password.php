<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require_once __DIR__ . '/../config/database.php';

$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set($config['app']['timezone']);

$username = $argv[1] ?? 'admin';
$password = $argv[2] ?? '';

if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
    fwrite(STDERR, "Username must be 3-50 characters using letters, numbers, dot, underscore or hyphen.\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    fwrite(STDERR, "Usage: php scripts/set_admin_password.php <username> <new-password>\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$pdo = db();

$stmt = $pdo->prepare('SELECT id FROM admins WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $username]);
$existing = $stmt->fetchColumn();

if ($existing !== false) {
    $stmt = $pdo->prepare('UPDATE admins SET password_hash = :password_hash WHERE id = :id');
    $stmt->execute([
        ':password_hash' => $hash,
        ':id' => (int) $existing,
    ]);
    echo "Password updated for '{$username}'.\n";
    exit(0);
}

$stmt = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)');
$stmt->execute([
    ':username' => $username,
    ':password_hash' => $hash,
]);

echo "Admin '{$username}' created.\n";
