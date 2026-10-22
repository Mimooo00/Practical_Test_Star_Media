<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (current_admin() !== null) {
    redirect('/admin/');
}

header('Cache-Control: no-store, private');

$lockout = login_lockout_remaining();
$error = $lockout > 0
    ? 'Too many failed attempts. Please try again in ' . (int) ceil($lockout / 60) . ' minute(s).'
    : '';

$postedUsername = $_POST['username'] ?? '';
$usernameValue = is_string($postedUsername) ? $postedUsername : '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? null;

    if ($lockout > 0) {
        $error = 'Too many failed attempts. Please try again in ' . (int) ceil($lockout / 60) . ' minute(s).';
    } elseif (!csrf_verify(is_string($postedToken) ? $postedToken : null)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $postedPassword = $_POST['password'] ?? '';
        $username = is_string($postedUsername) ? trim($postedUsername) : '';
        $password = is_string($postedPassword) ? $postedPassword : '';

        if ($username === '' || mb_strlen($username) > 50 || $password === '' || strlen($password) > 255) {
            record_login_failure();
            $error = 'Invalid username or password.';
        } else {
            $stmt = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            $hash = $admin['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

            if (password_verify($password, $hash) && $admin !== false) {
                log_in_admin($admin);
                redirect('/admin/');
            }

            record_login_failure();
            usleep(250000);
            $error = 'Invalid username or password.';
        }

        $lockout = login_lockout_remaining();
        if ($lockout > 0) {
            $error = 'Too many failed attempts. Please try again in ' . (int) ceil($lockout / 60) . ' minute(s).';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login | <?= e(app_config()['app']['name']) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="<?= e(app_url('/assets/img/favicon.svg')) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f7f5',
                            100: '#dcece7',
                            200: '#bcdad1',
                            300: '#90c0b2',
                            400: '#62a08f',
                            500: '#438474',
                            600: '#31695d',
                            700: '#29554c',
                            800: '#23443e',
                            900: '#1f3934'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'Segoe UI', 'system-ui', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/style.css')) ?>">
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10 antialiased">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-700 text-white shadow-sm">
                <svg viewBox="0 0 24 24" class="h-6 w-6 fill-current" aria-hidden="true">
                    <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>
                </svg>
            </span>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">Admin sign in</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in to view share click analytics.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <?php if ($error !== ''): ?>
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(app_url('/admin/login.php')) ?>" class="space-y-5" novalidate>
                <?= csrf_field() ?>
                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required maxlength="50"
                           value="<?= e($usernameValue) ?>"
                           class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required maxlength="255"
                           class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                </div>
                <button type="submit" class="w-full rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                    Sign in
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">
            <a href="<?= e(app_url('/')) ?>" class="font-medium text-brand-700 transition hover:text-brand-900">&larr; Back to the website</a>
        </p>
    </div>
</body>
</html>
