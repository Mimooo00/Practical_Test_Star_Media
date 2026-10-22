<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$admin = require_admin();
$adminTitle = $title ?? 'Dashboard';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($adminTitle) ?> | <?= e(app_config()['app']['name']) ?> Admin</title>
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
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
        <div class="flex items-center gap-3">
            <a href="<?= e(app_url('/admin/')) ?>" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current" aria-hidden="true">
                        <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-sm font-bold text-slate-900"><?= e(app_config()['app']['name']) ?></span>
                    <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Admin console</span>
                </span>
            </a>
        </div>

        <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Admin navigation">
            <a href="<?= e(app_url('/admin/')) ?>" class="rounded-lg bg-slate-900 px-3 py-2 font-medium text-white">Dashboard</a>
            <a href="<?= e(app_url('/')) ?>" target="_blank" rel="noopener noreferrer" class="rounded-lg px-3 py-2 font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">View site</a>
            <span class="hidden h-6 w-px bg-slate-200 sm:block" aria-hidden="true"></span>
            <span class="px-1 text-slate-500">Signed in as <strong class="font-semibold text-slate-800"><?= e($admin['username']) ?></strong></span>
            <form method="post" action="<?= e(app_url('/admin/logout.php')) ?>" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 font-medium text-slate-700 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700">
                    Log out
                </button>
            </form>
        </nav>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
