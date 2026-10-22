<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$demoPages = pages();
$page = [
    'title' => 'Social sharing with click tracking',
    'description' => 'A small PHP and MySQL demo with five social share buttons, page metadata, click tracking, and an admin analytics dashboard.',
    'image' => '/assets/img/og-home.png',
    'path' => '/',
    'nav' => 'home',
];

require __DIR__ . '/includes/header.php';
?>
<section class="relative overflow-hidden border-b border-slate-200 bg-white">
    <div class="pointer-events-none absolute inset-0 hero-grid opacity-70" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:py-20">
        <p class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-brand-800">
            <span class="h-1.5 w-1.5 rounded-full bg-brand-600"></span>
            PHP + MySQL practical demo
        </p>
        <h1 class="mx-auto mt-6 max-w-3xl font-display text-4xl font-bold leading-[1.08] tracking-tight text-slate-900 sm:text-5xl">
            Share a page. Track the click. See the data.
        </h1>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
            Five social share buttons, page-specific metadata, a click-tracking API, and an admin dashboard with charts and filters. Everything runs on PHP and MySQL.
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="<?= e(app_url('/page.php?slug=page-one')) ?>" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                Try the share flow
            </a>
            <a href="<?= e(app_url('/admin/')) ?>" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:text-brand-800">
                Open the dashboard
            </a>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
    <div class="max-w-2xl">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-700">What it does</p>
        <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-900">Three parts of the flow</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">The implementation follows the task directly: share buttons on the front end, a tracking endpoint in the middle, and an admin dashboard on top.</p>
    </div>

    <div class="mt-8 grid gap-5 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold text-brand-800">01</span>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Share the current page</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">Every page exposes its own canonical URL, title, description, and Open Graph image. The five buttons build the correct share URL for each platform.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold text-brand-800">02</span>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Record the click</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">A beacon sends the platform and page details to the API. The payload is validated, rate limited, and stored in the database with a timestamp.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold text-brand-800">03</span>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Review the analytics</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">Sign in to the admin dashboard to filter by date, platform, page, and interval, then view the charts, recent clicks, or a filtered CSV export.</p>
        </div>
    </div>
</section>

<section id="sample-pages" class="border-y border-slate-200 bg-white">
    <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-700">Sample pages</p>
        <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-900">Two pages, two different payloads</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Open either page, use a share button, then check the dashboard. The tracker groups clicks by the page URL that was shared.</p>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <?php foreach ($demoPages as $slug => $demoPage): ?>
                <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Sample page</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">
                        <a href="<?= e(app_url('/page.php?slug=' . urlencode($slug))) ?>" class="transition hover:text-brand-800">
                            <?= e($demoPage['title']) ?>
                        </a>
                    </h3>
                    <p class="mt-2 flex-1 text-sm leading-6 text-slate-600"><?= e($demoPage['description']) ?></p>
                    <a href="<?= e(app_url('/page.php?slug=' . urlencode($slug))) ?>" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 transition hover:text-brand-900">
                        Open page
                        <svg viewBox="0 0 20 20" class="h-4 w-4 fill-current" aria-hidden="true"><path d="M7.3 4.3a1 1 0 0 1 1.4 0l5 5a1 1 0 0 1 0 1.4l-5 5a1 1 0 1 1-1.4-1.4L11.6 10 7.3 5.7a1 1 0 0 1 0-1.4z"/></svg>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
    <?php require __DIR__ . '/includes/share-bar.php'; ?>
</div>
<?php
require __DIR__ . '/includes/footer.php';
