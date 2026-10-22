<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/queries.php';

require_admin();

$filterState = admin_filter_values($_GET);
$filters = $filterState['filters'];
[$where, $params] = admin_where($filters);

$stmt = db()->prepare(
    "SELECT c.id, c.clicked_at, p.name AS platform_name,
            c.page_title, c.page_url, c.page_path
     FROM share_clicks c
     INNER JOIN platforms p ON p.id = c.platform_id
     {$where}
     ORDER BY c.clicked_at DESC, c.id DESC"
);
$stmt->execute($params);

$filename = 'star-media-share-clicks-' . now()->format('Ymd-His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function csv_safe(string $value): string
{
    if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
        return "'" . $value;
    }

    return $value;
}

$output = fopen('php://output', 'wb');
if ($output === false) {
    exit;
}

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, [
    'Click ID',
    'Clicked at (' . app_config()['app']['timezone'] . ')',
    'Platform',
    'Page title',
    'Page URL',
    'Page path',
]);

while (($row = $stmt->fetch()) !== false) {
    fputcsv($output, [
        (int) $row['id'],
        (string) $row['clicked_at'],
        csv_safe((string) $row['platform_name']),
        csv_safe((string) $row['page_title']),
        csv_safe((string) $row['page_url']),
        csv_safe((string) $row['page_path']),
    ]);
}

fclose($output);
exit;
