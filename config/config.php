<?php

declare(strict_types=1);

$appUrl = getenv('APP_URL') ?: 'http://localhost/Practical_Test_Star_Media';
$debugEnv = getenv('APP_DEBUG');

return [
    'app' => [
        'name' => 'Social Share Tracker',
        'tagline' => 'Sharing and click analytics',
        'url' => rtrim($appUrl, '/'),
        'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Kuala_Lumpur',
        'debug' => $debugEnv === false ? true : filter_var($debugEnv, FILTER_VALIDATE_BOOL),
        'ip_salt' => getenv('APP_IP_SALT') ?: 'local-development-salt-change-in-production',
    ],

    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'social_share_tracker',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],

    'security' => [
        'session_name' => 'share_tracker_admin',
        'track_rate_limit' => 20,
        'track_window_seconds' => 60,
        'track_duplicate_seconds' => 3,
        'login_max_attempts' => 5,
        'login_lockout_seconds' => 900,
    ],

    'sharing' => [
        'popup_width' => 600,
        'popup_height' => 620,
    ],
];
