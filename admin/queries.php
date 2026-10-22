<?php

declare(strict_types=1);

function admin_string_input(array $input, string $key, string $default = ''): ?string
{
    if (!array_key_exists($key, $input)) {
        return $default;
    }

    $value = $input[$key];

    return is_string($value) ? trim($value) : null;
}

function admin_filter_values(array $input): array
{
    $timezone = new DateTimeZone(app_config()['app']['timezone']);
    $today = new DateTimeImmutable('today', $timezone);
    $errors = [];

    $to = $today;
    $from = $today->modify('-29 days');

    $inputTo = admin_string_input($input, 'to', '');
    if ($inputTo === null) {
        $errors[] = 'The end date filter was invalid, so the default range was used.';
        $inputTo = '';
    }

    if ($inputTo !== '') {
        $parsedTo = DateTimeImmutable::createFromFormat('!Y-m-d', $inputTo, $timezone);
        if ($parsedTo !== false && $parsedTo->format('Y-m-d') === $inputTo) {
            $to = $parsedTo;
        } else {
            $errors[] = 'The end date was invalid, so the default range was used.';
        }
    }

    $inputFrom = admin_string_input($input, 'from', '');
    if ($inputFrom === null) {
        $errors[] = 'The start date filter was invalid, so the default range was used.';
        $inputFrom = '';
    }

    if ($inputFrom !== '') {
        $parsedFrom = DateTimeImmutable::createFromFormat('!Y-m-d', $inputFrom, $timezone);
        if ($parsedFrom !== false && $parsedFrom->format('Y-m-d') === $inputFrom) {
            $from = $parsedFrom;
        } else {
            $errors[] = 'The start date was invalid, so the default range was used.';
        }
    }

    if ($from > $to) {
        $errors[] = 'The start date must be on or before the end date.';
        $from = $to->modify('-29 days');
    }

    if ((int) $from->diff($to)->days > 365) {
        $errors[] = 'Date ranges are limited to 366 days.';
        $from = $to->modify('-365 days');
    }

    $platformSlugs = array_column(all_platforms(), 'slug');
    $platform = 'all';
    $inputPlatform = admin_string_input($input, 'platform', 'all');

    if ($inputPlatform === null) {
        $errors[] = 'The selected social platform filter was invalid.';
        $inputPlatform = 'all';
    }

    if ($inputPlatform !== '' && $inputPlatform !== 'all') {
        if (in_array($inputPlatform, $platformSlugs, true)) {
            $platform = $inputPlatform;
        } else {
            $errors[] = 'The selected social platform is not recognised.';
        }
    }

    $page = '';
    $inputPage = admin_string_input($input, 'page', '');

    if ($inputPage === null) {
        $errors[] = 'The page filter was invalid.';
        $inputPage = '';
    }

    if ($inputPage !== '') {
        if (mb_strlen($inputPage) <= 255) {
            $page = $inputPage;
        } else {
            $errors[] = 'The page filter is too long.';
        }
    }

    $interval = 'day';
    $inputInterval = admin_string_input($input, 'interval', 'day');

    if ($inputInterval === null) {
        $errors[] = 'The selected grouping interval was invalid.';
        $inputInterval = 'day';
    }

    if (in_array($inputInterval, ['day', 'week', 'month'], true)) {
        $interval = $inputInterval;
    } elseif ($inputInterval !== '') {
        $errors[] = 'The selected grouping interval is not recognised.';
    }

    return [
        'filters' => [
            'from' => $from,
            'to' => $to,
            'platform' => $platform,
            'page' => $page,
            'interval' => $interval,
        ],
        'errors' => $errors,
    ];
}

function admin_filter_query(array $filters): array
{
    return [
        'from' => $filters['from']->format('Y-m-d'),
        'to' => $filters['to']->format('Y-m-d'),
        'platform' => $filters['platform'],
        'page' => $filters['page'],
        'interval' => $filters['interval'],
    ];
}

function admin_where(array $filters): array
{
    $where = ['c.clicked_at >= :from_dt', 'c.clicked_at <= :to_dt'];
    $params = [
        ':from_dt' => $filters['from']->format('Y-m-d 00:00:00'),
        ':to_dt' => $filters['to']->format('Y-m-d 23:59:59'),
    ];

    if ($filters['platform'] !== 'all') {
        $where[] = 'p.slug = :platform_slug';
        $params[':platform_slug'] = $filters['platform'];
    }

    if ($filters['page'] !== '') {
        $where[] = 'c.page_path = :page_path';
        $params[':page_path'] = $filters['page'];
    }

    return ['WHERE ' . implode(' AND ', $where), $params];
}

function admin_buckets(DateTimeImmutable $from, DateTimeImmutable $to, string $interval): array
{
    $buckets = [];

    if ($interval === 'month') {
        $cursor = $from->modify('first day of this month');
        while ($cursor <= $to) {
            $buckets[$cursor->format('Y-m')] = $cursor->format('M Y');
            $cursor = $cursor->modify('+1 month');
        }

        return $buckets;
    }

    if ($interval === 'week') {
        $cursor = $from->modify('monday this week');
        while ($cursor <= $to) {
            $buckets[$cursor->format('Y-m-d')] = 'Wk of ' . $cursor->format('d M');
            $cursor = $cursor->modify('+7 days');
        }

        return $buckets;
    }

    $cursor = $from;
    while ($cursor <= $to) {
        $buckets[$cursor->format('Y-m-d')] = $cursor->format('d M');
        $cursor = $cursor->modify('+1 day');
    }

    return $buckets;
}

function admin_bucket_key(string $day, string $interval): string
{
    $date = new DateTimeImmutable($day, new DateTimeZone(app_config()['app']['timezone']));

    return match ($interval) {
        'month' => $date->format('Y-m'),
        'week' => $date->modify('monday this week')->format('Y-m-d'),
        default => $date->format('Y-m-d'),
    };
}

function admin_recent_clicks(array $filters, int $limit = 25): array
{
    [$where, $params] = admin_where($filters);

    $stmt = db()->prepare(
        "SELECT c.id, c.page_url, c.page_title, c.page_path, c.clicked_at,
                p.name AS platform_name, p.slug AS platform_slug, p.color AS platform_color
         FROM share_clicks c
         INNER JOIN platforms p ON p.id = c.platform_id
         {$where}
         ORDER BY c.clicked_at DESC, c.id DESC
         LIMIT {$limit}"
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function admin_dashboard_data(array $filters): array
{
    [$where, $params] = admin_where($filters);
    $timezone = new DateTimeZone(app_config()['app']['timezone']);
    $pdo = db();

    $stmt = $pdo->prepare(
        "SELECT p.id, p.slug, p.name, p.color,
                DATE(c.clicked_at) AS click_day,
                COUNT(*) AS total
         FROM share_clicks c
         INNER JOIN platforms p ON p.id = c.platform_id
         {$where}
         GROUP BY p.id, p.slug, p.name, p.color, DATE(c.clicked_at)
         ORDER BY click_day ASC"
    );
    $stmt->execute($params);
    $dailyRows = $stmt->fetchAll();

    $buckets = admin_buckets($filters['from'], $filters['to'], $filters['interval']);
    $bucketKeys = array_keys($buckets);

    $series = [];
    foreach (all_platforms() as $platform) {
        if ($filters['platform'] !== 'all' && $platform['slug'] !== $filters['platform']) {
            continue;
        }

        $series[$platform['slug']] = [
            'name' => $platform['name'],
            'color' => $platform['color'],
            'data' => array_fill_keys($bucketKeys, 0),
            'total' => 0,
        ];
    }

    foreach ($dailyRows as $row) {
        $slug = $row['slug'];

        if (!isset($series[$slug])) {
            $series[$slug] = [
                'name' => $row['name'],
                'color' => $row['color'],
                'data' => array_fill_keys($bucketKeys, 0),
                'total' => 0,
            ];
        }

        $key = admin_bucket_key((string) $row['click_day'], $filters['interval']);
        if (!array_key_exists($key, $series[$slug]['data'])) {
            continue;
        }

        $count = (int) $row['total'];
        $series[$slug]['data'][$key] += $count;
        $series[$slug]['total'] += $count;
    }

    $series = array_values(array_filter(
        $series,
        static fn (array $item): bool => $item['total'] > 0 || $filters['platform'] !== 'all'
    ));

    $lineDatasets = [];
    foreach ($series as $item) {
        $lineDatasets[] = [
            'label' => $item['name'],
            'data' => array_values($item['data']),
            'borderColor' => $item['color'],
            'backgroundColor' => $item['color'],
            'borderWidth' => 2,
            'tension' => 0.35,
            'fill' => false,
            'pointRadius' => 0,
            'pointHoverRadius' => 4,
            'pointHitRadius' => 12,
        ];
    }

    $doughnutLabels = [];
    $doughnutData = [];
    $doughnutColors = [];
    $totalClicks = 0;

    usort($series, static fn (array $a, array $b): int => $b['total'] <=> $a['total']);

    foreach ($series as $item) {
        $totalClicks += $item['total'];

        if ($item['total'] > 0) {
            $doughnutLabels[] = $item['name'];
            $doughnutData[] = $item['total'];
            $doughnutColors[] = $item['color'];
        }
    }

    $stmt = $pdo->prepare(
        "SELECT c.page_path, MAX(c.page_title) AS page_title, COUNT(*) AS total
         FROM share_clicks c
         INNER JOIN platforms p ON p.id = c.platform_id
         {$where}
         GROUP BY c.page_path
         ORDER BY total DESC, c.page_path ASC
         LIMIT 8"
    );
    $stmt->execute($params);
    $topPages = $stmt->fetchAll();

    $topPageLabels = [];
    $topPageData = [];

    foreach ($topPages as $topPage) {
        $label = trim((string) $topPage['page_title']);

        if ($label === '') {
            $label = truncate_path((string) $topPage['page_path'], 44);
        } elseif (mb_strlen($label) > 44) {
            $label = mb_substr($label, 0, 43) . '…';
        }

        $topPageLabels[] = $label;
        $topPageData[] = (int) $topPage['total'];
    }

    $days = max(1, (int) $filters['from']->diff($filters['to'])->days + 1);
    $topPlatform = $doughnutLabels[0] ?? '—';
    $topPageTitle = $topPages[0]['page_title'] ?? '—';

    $recent = admin_recent_clicks($filters);

    return [
        'filters' => $filters,
        'totalClicks' => $totalClicks,
        'averagePerDay' => $totalClicks > 0 ? round($totalClicks / $days, 1) : 0.0,
        'topPlatform' => $topPlatform,
        'topPageTitle' => $topPageTitle,
        'topPages' => $topPages,
        'recent' => $recent,
        'hasData' => $totalClicks > 0,
        'charts' => [
            'line' => [
                'labels' => array_values($buckets),
                'datasets' => $lineDatasets,
            ],
            'doughnut' => [
                'labels' => $doughnutLabels,
                'data' => $doughnutData,
                'colors' => $doughnutColors,
            ],
            'topPages' => [
                'labels' => $topPageLabels,
                'data' => $topPageData,
            ],
        ],
    ];
}

function admin_filter_options(): array
{
    $pages = db()
        ->query(
            'SELECT page_path, MAX(page_title) AS page_title, COUNT(*) AS total
             FROM share_clicks
             GROUP BY page_path
             ORDER BY total DESC, page_path ASC
             LIMIT 100'
        )
        ->fetchAll();

    return [
        'platforms' => all_platforms(),
        'pages' => $pages,
    ];
}
