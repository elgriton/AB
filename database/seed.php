<?php

declare(strict_types=1);

// перезалив демо данных
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Database;
use App\Infrastructure\ConfigRepository;
use App\Infrastructure\DatabaseSeeder;
use App\Infrastructure\Installer;
use App\Infrastructure\SchemaManager;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this seeder from the command line.\n");
}

$config = (new ConfigRepository(dirname(__DIR__) . '/config.php'))->load();
if (!$config) {
    fwrite(STDERR, "Настройте базу данных через главную страницу сайта.\n");
    exit(1);
}

$pdo = Database::connect($config);
$count = (new Installer(new SchemaManager(), new DatabaseSeeder()))->install($pdo);
printf("Схема базы проверена. Добавлено статей: %d\n", $count);

