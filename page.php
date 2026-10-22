<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageSlug = $_GET['slug'] ?? '';
$pageSlug = is_string($pageSlug) ? $pageSlug : '';
$allPages = pages();

if (!isset($allPages[$pageSlug])) {
    http_response_code(404);
    $page = [
        'title' => 'Page not found',
        'description' => 'The page you were looking for is not available.',
        'path' => '/page.php',
        'nav' => '',
    ];

    require __DIR__ . '/includes/header.php';
    ?>
    <section class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-brand-700">404</p>
        <h1 class="mt-3 font-display text-4xl font-bold text-slate-900">That page does not exist.</h1>
        <p class="mt-4 text-slate-600">It may have been moved, or the link may be incomplete. Head back to the homepage to continue.</p>
        <a href="<?= e(app_url('/')) ?>" class="mt-8 inline-flex rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-800">Back to home</a>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageData = $allPages[$pageSlug];
$page = [
    'title' => $pageData['title'],
    'description' => $pageData['description'],
    'image' => $pageData['image'],
    'path' => '/page.php?slug=' . urlencode($pageSlug),
    'nav' => $pageSlug,
    'type' => 'website',
];

require __DIR__ . '/includes/header.php';
?>
<section class="bg-white">
    <div class="mx-auto max-w-3xl px-4 pb-8 pt-12 sm:px-6 sm:pt-16">
        <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
            <a href="<?= e(app_url('/')) ?>" class="transition hover:text-brand-700">Home</a>
            <span aria-hidden="true">/</span>
            <span class="rounded-full bg-brand-50 px-2.5 py-1 font-semibold text-brand-800">Sample page</span>
        </nav>
        <h1 class="mt-5 font-display text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl"><?= e($pageData['title']) ?></h1>
        <p class="mt-5 text-lg leading-8 text-slate-600"><?= e($pageData['description']) ?></p>
        <p class="mt-4 text-sm text-slate-500">This page has its own URL and metadata for testing the share buttons and click tracker.</p>
    </div>

    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <img src="<?= e(app_url($pageData['image'])) ?>"
             alt="Illustration for <?= e($pageData['title']) ?>"
             class="aspect-[1200/630] w-full rounded-2xl border border-slate-200 object-cover shadow-sm">
    </div>

    <div class="mx-auto max-w-3xl px-4 pb-4 pt-10 sm:px-6">
        <div class="page-body">
            <?php foreach ($pageData['body'] as $block): ?>
                <?= $block ?>
            <?php endforeach; ?>
        </div>

        <div class="mt-10">
            <?php
            $shareHeading = 'Share this page';
            require __DIR__ . '/includes/share-bar.php';
            ?>
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <div>
                <p class="text-sm font-semibold text-slate-900">More sample pages</p>
                <p class="mt-1 text-sm text-slate-600">Test sharing on a different URL and compare the results.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($allPages as $otherSlug => $otherPage): ?>
                    <?php if ($otherSlug === $pageSlug) { continue; } ?>
                    <a href="<?= e(app_url('/page.php?slug=' . urlencode($otherSlug))) ?>" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-brand-300 hover:text-brand-800">
                        <?= e($otherPage['nav_title']) ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?= e(app_url('/')) ?>" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-brand-300 hover:text-brand-800">Home</a>
            </div>
        </div>
    </div>
</section>
<?php
require __DIR__ . '/includes/footer.php';
