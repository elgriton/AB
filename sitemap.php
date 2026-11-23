<?php

declare(strict_types=1);

use App\Core\Database;
use App\Infrastructure\ConfigRepository;

/** Формирует XML-карту сайта из существующих рубрик и статей базы. */
require __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow, noarchive', true);

try {
    $config = (new ConfigRepository(__DIR__ . '/config.php'))->load();
    if (!$config) {
        http_response_code(503);
        exit;
    }
    $pdo = Database::connect($config);
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $base = $scheme . '://' . $host;
    $urls = [$base . '/index.php'];

    foreach ($pdo->query('SELECT slug FROM categories ORDER BY id') as $row) {
        $urls[] = $base . '/index.php?category=' . rawurlencode($row['slug']);
    }
    foreach ($pdo->query('SELECT slug FROM posts ORDER BY published_at DESC') as $row) {
        $urls[] = $base . '/index.php?post=' . rawurlencode($row['slug']);
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        echo '<url><loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
    }
    echo '</urlset>';
} catch (Throwable $exception) {
    http_response_code(503);
}
