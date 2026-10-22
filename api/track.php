<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function track_fail(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    track_fail(405, 'Method not allowed.');
}

$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
$rawBody = file_get_contents('php://input');

if ($rawBody === false || trim($rawBody) === '') {
    track_fail(422, 'A JSON payload is required.');
}

if (strlen($rawBody) > 4096) {
    track_fail(413, 'Payload too large.');
}

if (str_contains($contentType, 'application/json')) {
    try {
        $payload = json_decode($rawBody, true, 8, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        track_fail(422, 'Malformed JSON payload.');
    }
} else {
    $payload = [];
    parse_str($rawBody, $payload);
}

if (!is_array($payload)) {
    track_fail(422, 'Malformed payload.');
}

$appHost = parse_url(app_url('/'), PHP_URL_HOST);
$requestHost = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
$allowedHosts = [];

if (is_string($appHost) && $appHost !== '') {
    $allowedHosts[] = strtolower($appHost);
}

if (is_string($requestHost) && $requestHost !== '') {
    $allowedHosts[] = strtolower($requestHost);
}

foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $headerName) {
    $value = $_SERVER[$headerName] ?? '';
    if ($value === '') {
        continue;
    }

    $originHost = parse_url($value, PHP_URL_HOST);

    if (!is_string($originHost) || !in_array(strtolower($originHost), $allowedHosts, true)) {
        track_fail(403, 'Origin not allowed.');
    }
}

$platformSlug = $payload['platform'] ?? null;
if (!is_string($platformSlug) || !preg_match('/^[a-z0-9_-]{1,32}$/', $platformSlug)) {
    track_fail(422, 'Invalid platform.');
}

$platform = find_active_platform($platformSlug);
if ($platform === null) {
    track_fail(422, 'Unknown platform.');
}

$pageUrl = $payload['url'] ?? null;
if (!is_string($pageUrl)) {
    track_fail(422, 'Invalid page URL.');
}

$pageUrl = trim($pageUrl);
if (strlen($pageUrl) < 10 || strlen($pageUrl) > 1000) {
    track_fail(422, 'Page URL length is invalid.');
}

if (preg_match('/[\x00-\x1F\x7F]/', $pageUrl) === 1) {
    track_fail(422, 'Page URL contains invalid characters.');
}

if (filter_var($pageUrl, FILTER_VALIDATE_URL) === false) {
    track_fail(422, 'Page URL is not valid.');
}

$urlParts = parse_url($pageUrl);
if (!is_array($urlParts)) {
    track_fail(422, 'Page URL could not be parsed.');
}

$scheme = strtolower((string) ($urlParts['scheme'] ?? ''));
$urlHost = (string) ($urlParts['host'] ?? '');

if (!in_array($scheme, ['http', 'https'], true)) {
    track_fail(422, 'Page URL scheme is not allowed.');
}

if ($urlHost === '' || !is_string($appHost) || strcasecmp(rtrim($urlHost, '.'), rtrim($appHost, '.')) !== 0) {
    track_fail(422, 'Page URL host is not allowed.');
}

if (isset($urlParts['user']) || isset($urlParts['pass'])) {
    track_fail(422, 'Page URL must not contain credentials.');
}

$rawTitle = $payload['title'] ?? '';
if (!is_string($rawTitle)) {
    track_fail(422, 'Invalid page title.');
}

$rawTitle = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $rawTitle) ?? '';
$rawTitle = trim(preg_replace('/\s+/u', ' ', $rawTitle) ?? '');
$title = $rawTitle === '' ? app_config()['app']['name'] : $rawTitle;

if (mb_strlen($title) > 255) {
    track_fail(422, 'Page title is too long.');
}

$path = (string) ($urlParts['path'] ?? '/');
if ($path === '') {
    $path = '/';
}

if (isset($urlParts['query']) && $urlParts['query'] !== '') {
    $path .= '?' . $urlParts['query'];
}

if (mb_strlen($path) > 255) {
    $path = mb_substr($path, 0, 255);
}

$security = app_config()['security'];
$hashedIp = ip_hash();
$platformId = (int) $platform['id'];
$clickedAt = now();

$windowStart = $clickedAt
    ->modify('-' . (int) $security['track_window_seconds'] . ' seconds')
    ->format('Y-m-d H:i:s');

$rateStmt = db()->prepare(
    'SELECT COUNT(*) FROM share_clicks
     WHERE ip_hash = :ip_hash AND platform_id = :platform_id AND clicked_at >= :window_start'
);
$rateStmt->execute([
    ':ip_hash' => $hashedIp,
    ':platform_id' => $platformId,
    ':window_start' => $windowStart,
]);

if ((int) $rateStmt->fetchColumn() >= (int) $security['track_rate_limit']) {
    track_fail(429, 'Too many share events. Please slow down.');
}

$duplicateStart = $clickedAt
    ->modify('-' . (int) $security['track_duplicate_seconds'] . ' seconds')
    ->format('Y-m-d H:i:s');

$duplicateStmt = db()->prepare(
    'SELECT COUNT(*) FROM share_clicks
     WHERE ip_hash = :ip_hash AND platform_id = :platform_id
       AND page_path = :page_path AND clicked_at >= :duplicate_start'
);
$duplicateStmt->execute([
    ':ip_hash' => $hashedIp,
    ':platform_id' => $platformId,
    ':page_path' => $path,
    ':duplicate_start' => $duplicateStart,
]);

if ((int) $duplicateStmt->fetchColumn() > 0) {
    http_response_code(204);
    exit;
}

try {
    $insert = db()->prepare(
        'INSERT INTO share_clicks
            (platform_id, page_url, page_title, page_path, ip_hash, clicked_at)
         VALUES
            (:platform_id, :page_url, :page_title, :page_path, :ip_hash, :clicked_at)'
    );

    $insert->execute([
        ':platform_id' => $platformId,
        ':page_url' => $pageUrl,
        ':page_title' => $title,
        ':page_path' => $path,
        ':ip_hash' => $hashedIp,
        ':clicked_at' => $clickedAt->format('Y-m-d H:i:s'),
    ]);
} catch (PDOException $exception) {
    error_log('[share-tracker] Failed to record share click: ' . $exception->getMessage());
    track_fail(500, 'The share could not be recorded.');
}

http_response_code(204);
exit;
