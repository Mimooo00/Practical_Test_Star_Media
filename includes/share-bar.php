<?php

declare(strict_types=1);

$shareHeading = $shareHeading ?? 'Share this page';
$shareMeta = [
    'url' => app_url($page['path'] ?? '/'),
    'title' => $page['title'] ?? app_config()['app']['name'],
    'description' => $page['description'] ?? '',
];
$sharePlatforms = active_platforms();
?>
<section class="share-panel" aria-labelledby="share-heading">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 id="share-heading" class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500"><?= e($shareHeading) ?></h2>
            <p class="mt-1 text-sm text-slate-500">Choose a platform. The share opens with this page URL and metadata, and the click is recorded.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <?php foreach ($sharePlatforms as $platform): ?>
                <?php
                $href = share_url($platform, $shareMeta);
                if ($href === '') {
                    continue;
                }
                $isEmail = str_starts_with(strtolower($href), 'mailto:');
                ?>
                <a href="<?= e($href) ?>"
                   data-share-platform="<?= e($platform['slug']) ?>"
                   data-share-mode="<?= $isEmail ? 'email' : 'popup' ?>"
                   <?= $isEmail ? '' : 'target="_blank" rel="noopener noreferrer"' ?>
                   title="Share on <?= e($platform['name']) ?>"
                   class="share-btn"
                   style="--brand: <?= e($platform['color']) ?>">
                    <?= platform_icon_svg($platform['slug']) ?>
                    <span><?= e($platform['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
