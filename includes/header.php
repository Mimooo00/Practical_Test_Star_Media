<?php

declare(strict_types=1);

$pageTitle = $page['title'] ?? app_config()['app']['name'];
$pageDescription = $page['description'] ?? 'A PHP and MySQL demo for social sharing and click tracking.';
$pageImage = app_url($page['image'] ?? '/assets/img/og-home.png');
$pagePath = $page['path'] ?? '/';
$canonical = app_url($pagePath);
$pageType = $page['type'] ?? 'website';
$siteName = app_config()['app']['name'];
$navPages = pages();
$activeNav = $page['nav'] ?? '';

$shareConfig = [
    'trackUrl' => app_url('/api/track.php'),
    'popupWidth' => (int) app_config()['sharing']['popup_width'],
    'popupHeight' => (int) app_config()['sharing']['popup_height'],
    'page' => [
        'url' => $canonical,
        'title' => $pageTitle,
        'description' => $pageDescription,
    ],
];
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e($siteName) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <link rel="icon" type="image/svg+xml" href="<?= e(app_url('/assets/img/favicon.svg')) ?>">

    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:type" content="<?= e($pageType) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($pageImage) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($pageImage) ?>">

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
                        sans: ['Inter', 'Segoe UI', 'system-ui', 'sans-serif'],
                        display: ['Inter', 'Segoe UI', 'system-ui', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="<?= e(app_url('/assets/css/style.css')) ?>">
    <script>
        window.SHARE_TRACKER = <?= json_for_script($shareConfig) ?>;
    </script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
        <a href="<?= e(app_url('/')) ?>" class="group flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-700 text-white shadow-sm">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current" aria-hidden="true">
                    <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>
                </svg>
            </span>
            <span class="flex flex-col leading-tight">
                <span class="font-display text-lg font-bold tracking-tight text-slate-900 group-hover:text-brand-700"><?= e($siteName) ?></span>
                <span class="text-[11px] font-medium uppercase tracking-[0.16em] text-slate-500"><?= e(app_config()['app']['tagline']) ?></span>
            </span>
        </a>

        <nav class="flex w-full items-center gap-1 overflow-x-auto pb-1 text-sm font-medium text-slate-600 sm:w-auto sm:overflow-visible sm:pb-0" aria-label="Main navigation">
            <a href="<?= e(app_url('/')) ?>"
               class="rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900 <?= $activeNav === 'home' ? 'bg-brand-50 text-brand-800' : '' ?>">
                Home
            </a>
            <?php foreach ($navPages as $navSlug => $navPage): ?>
                <a href="<?= e(app_url('/page.php?slug=' . urlencode($navSlug))) ?>"
                   class="shrink-0 rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900 <?= $activeNav === $navSlug ? 'bg-brand-50 text-brand-800' : '' ?>">
                    <?= e($navPage['nav_title']) ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= e(app_url('/admin/')) ?>"
               class="ml-1 rounded-lg border border-slate-200 px-3 py-2 text-slate-700 transition hover:border-brand-300 hover:text-brand-700">
                Admin
            </a>
        </nav>
    </div>
</header>

<main>
