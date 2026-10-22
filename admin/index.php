<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/queries.php';

require_admin();

$filterState = admin_filter_values($_GET);
$filters = $filterState['filters'];
$filterErrors = $filterState['errors'];
$options = admin_filter_options();
$data = admin_dashboard_data($filters);
$exportQuery = http_build_query(admin_filter_query($filters));

$pageOptions = $options['pages'];
$knownPages = array_column($pageOptions, 'page_path');

if ($filters['page'] !== '' && !in_array($filters['page'], $knownPages, true)) {
    array_unshift($pageOptions, [
        'page_path' => $filters['page'],
        'page_title' => $filters['page'],
        'total' => null,
    ]);
}

$title = 'Dashboard';
$footerScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" defer></script>'
    . '<script id="admin-chart-data" type="application/json">' . json_for_script($data['charts']) . '</script>'
    . '<script src="' . e(app_url('/assets/js/admin-charts.js')) . '" defer></script>';

require __DIR__ . '/_header.php';
?>
<div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-700">Share analytics</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Social share dashboard</h1>
        <p class="mt-1 text-sm text-slate-500">
            <?= e($filters['from']->format('d M Y')) ?> &ndash; <?= e($filters['to']->format('d M Y')) ?>
            &middot; grouped by <?= e($filters['interval']) ?>
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="<?= e(app_url('/admin/')) ?>" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400">Reset</a>
        <a href="<?= e(app_url('/admin/export.php') . '?' . $exportQuery) ?>" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
            Export CSV
        </a>
    </div>
</div>

<?php if ($filterErrors !== []): ?>
    <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert" aria-live="polite">
        <ul class="list-disc space-y-1 pl-5">
            <?php foreach ($filterErrors as $filterError): ?>
                <li><?= e($filterError) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="get" action="<?= e(app_url('/admin/')) ?>" class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
        <div>
            <label for="from" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">From</label>
            <input id="from" type="date" name="from" value="<?= e($filters['from']->format('Y-m-d')) ?>"
                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>
        <div>
            <label for="to" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">To</label>
            <input id="to" type="date" name="to" value="<?= e($filters['to']->format('Y-m-d')) ?>"
                   class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
        </div>
        <div>
            <label for="platform" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Platform</label>
            <select id="platform" name="platform"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                <option value="all" <?= $filters['platform'] === 'all' ? 'selected' : '' ?>>All platforms</option>
                <?php foreach ($options['platforms'] as $platform): ?>
                    <option value="<?= e($platform['slug']) ?>" <?= $filters['platform'] === $platform['slug'] ? 'selected' : '' ?>>
                        <?= e($platform['name']) ?><?= (int) $platform['is_active'] === 1 ? '' : ' (inactive)' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="page" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Page</label>
            <select id="page" name="page"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                <option value="" <?= $filters['page'] === '' ? 'selected' : '' ?>>All pages</option>
                <?php foreach ($pageOptions as $pageOption): ?>
                    <option value="<?= e($pageOption['page_path']) ?>" <?= $filters['page'] === $pageOption['page_path'] ? 'selected' : '' ?>>
                        <?= e(truncate_path((string) $pageOption['page_path'], 48)) ?>
                        <?= $pageOption['total'] !== null ? ' (' . (int) $pageOption['total'] . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="interval" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Group by</label>
            <select id="interval" name="interval"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200">
                <option value="day" <?= $filters['interval'] === 'day' ? 'selected' : '' ?>>Day</option>
                <option value="week" <?= $filters['interval'] === 'week' ? 'selected' : '' ?>>Week</option>
                <option value="month" <?= $filters['interval'] === 'month' ? 'selected' : '' ?>>Month</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                Apply filters
            </button>
        </div>
    </div>
</form>

<section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Summary metrics">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total shares</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900"><?= e(number_format($data['totalClicks'])) ?></p>
        <p class="mt-1 text-xs text-slate-400">Within the selected filters</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Average per day</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900"><?= e(number_format((float) $data['averagePerDay'], 1)) ?></p>
        <p class="mt-1 text-xs text-slate-400">Across the selected range</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top platform</p>
        <p class="mt-2 truncate text-2xl font-bold tracking-tight text-slate-900" title="<?= e($data['topPlatform']) ?>"><?= e($data['topPlatform']) ?></p>
        <p class="mt-1 text-xs text-slate-400">Most share clicks</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top page</p>
        <p class="mt-2 truncate text-2xl font-bold tracking-tight text-slate-900" title="<?= e($data['topPageTitle']) ?>"><?= e($data['topPageTitle']) ?></p>
        <p class="mt-1 text-xs text-slate-400">Most shared content</p>
    </div>
</section>

<section class="mt-6 grid gap-4 xl:grid-cols-3" aria-label="Charts">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Clicks over time</h2>
                <p class="text-sm text-slate-500">Share clicks per platform across the selected range.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize text-slate-600"><?= e($filters['interval']) ?></span>
        </div>
        <?php if ($data['hasData']): ?>
            <div class="admin-chart admin-chart--tall mt-4">
                <canvas id="chart-over-time" role="img" aria-label="Line chart of share clicks over time by platform"></canvas>
            </div>
        <?php else: ?>
            <div class="mt-4 flex h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-center">
                <div>
                    <p class="text-sm font-semibold text-slate-700">No share clicks in this range</p>
                    <p class="mt-1 text-sm text-slate-500">Try widening the dates or clearing the filters.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Platform breakdown</h2>
        <p class="text-sm text-slate-500">Share of clicks by social platform.</p>
        <?php if ($data['hasData']): ?>
            <div class="admin-chart mt-4">
                <canvas id="chart-platforms" role="img" aria-label="Doughnut chart of share clicks by platform"></canvas>
            </div>
        <?php else: ?>
            <div class="mt-4 flex h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-center">
                <p class="px-4 text-sm text-slate-500">Platform totals will appear once shares are recorded.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3">
        <h2 class="text-base font-semibold text-slate-900">Most shared pages</h2>
        <p class="text-sm text-slate-500">Top pages in the selected range. Full data is available in the CSV export.</p>
        <?php if ($data['hasData']): ?>
            <div class="admin-chart mt-4">
                <canvas id="chart-top-pages" role="img" aria-label="Horizontal bar chart of the most shared pages"></canvas>
            </div>
        <?php else: ?>
            <div class="mt-4 flex h-40 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50">
                <p class="text-sm text-slate-500">No page activity to chart yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Recent share clicks">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-5 py-4">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Recent share clicks</h2>
            <p class="text-sm text-slate-500">Latest 25 events matching the filters.</p>
        </div>
        <a href="<?= e(app_url('/admin/export.php') . '?' . $exportQuery) ?>" class="text-sm font-semibold text-brand-700 transition hover:text-brand-900">Download all &rarr;</a>
    </div>

    <?php if ($data['recent'] === []): ?>
        <div class="px-5 py-12 text-center">
            <p class="text-sm font-semibold text-slate-700">No clicks recorded for these filters.</p>
            <p class="mt-1 text-sm text-slate-500">Share a page from the website to see events here.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Clicked at</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Platform</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Page</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($data['recent'] as $click): ?>
                        <?php
                        $clickedAt = new DateTimeImmutable(
                            (string) $click['clicked_at'],
                            new DateTimeZone(app_config()['app']['timezone'])
                        );
                        ?>
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-3 text-slate-600">
                                <?= e($clickedAt->format('d M Y, H:i')) ?>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <span class="inline-flex items-center gap-2 font-medium text-slate-800">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: <?= e($click['platform_color']) ?>"></span>
                                    <?= e($click['platform_name']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <p class="max-w-md truncate font-medium text-slate-800" title="<?= e($click['page_title']) ?>"><?= e($click['page_title']) ?></p>
                                <a href="<?= e($click['page_url']) ?>" target="_blank" rel="noopener noreferrer"
                                   class="mt-0.5 block max-w-md truncate text-xs text-brand-700 transition hover:text-brand-900"
                                   title="<?= e($click['page_url']) ?>">
                                    <?= e(truncate_path((string) $click['page_path'], 70)) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
require __DIR__ . '/_footer.php';
