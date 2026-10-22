</main>

<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <p>Share click analytics</p>
        <p>Times shown in <?= e(app_config()['app']['timezone']) ?></p>
    </div>
</footer>

<?= $footerScripts ?? '' ?>
</body>
</html>
