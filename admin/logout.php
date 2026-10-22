<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect(current_admin() === null ? '/admin/login.php' : '/admin/');
}

$postedToken = $_POST['csrf_token'] ?? null;

if (!csrf_verify(is_string($postedToken) ? $postedToken : null)) {
    redirect('/admin/');
}

log_out_admin();
redirect('/admin/login.php');
