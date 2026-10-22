</main>

<footer class="mt-20 border-t border-slate-200 bg-white">
    <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-10 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <p class="font-display text-lg font-bold text-slate-900"><?= e(app_config()['app']['name']) ?></p>
            <p class="mt-1 text-sm text-slate-500">Front-end sharing with back-end click analytics.</p>
        </div>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500">
            <a class="transition hover:text-brand-700" href="<?= e(app_url('/')) ?>">Home</a>
            <?php foreach (pages() as $footerSlug => $footerPage): ?>
                <a class="transition hover:text-brand-700" href="<?= e(app_url('/page.php?slug=' . urlencode($footerSlug))) ?>"><?= e($footerPage['nav_title']) ?></a>
            <?php endforeach; ?>
            <span>&copy; <?= e(date('Y')) ?> <?= e(app_config()['app']['name']) ?></span>
        </div>
    </div>
</footer>

<script src="<?= e(app_url('/assets/js/share.js')) ?>" defer></script>
</body>
</html>
