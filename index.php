<?php

declare(strict_types=1);

// Точка входа: проверяет базу и передаёт запрос общему контроллеру страниц.
use App\Controller\BlogController;
use App\Controller\SetupController;
use App\Core\Database;
use App\Core\TemplateRenderer;
use App\Infrastructure\ConfigRepository;
use App\Infrastructure\DatabaseSeeder;
use App\Infrastructure\Installer;
use App\Infrastructure\SchemaManager;
use App\Repository\BlogRepository;

require __DIR__ . '/vendor/autoload.php';

$configRepository = new ConfigRepository(__DIR__ . '/config.php');
$templates = new TemplateRenderer(__DIR__ . '/templates', __DIR__ . '/storage/smarty');
$installer = new Installer(new SchemaManager(), new DatabaseSeeder());
$setupController = new SetupController($templates, $configRepository, $installer);

// Обрабатываем форму создания базы данных.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_database'])) {
    $setupController->process($_POST);
    exit;
}

// смотрим конфиг если он есть
$config = $configRepository->load();
if (!isset($config['host'], $config['port'], $config['database'], $config['username'], $config['password'])) {
    $setupController->show();
    exit;
}

// Проверяем подключение, таблицы и начальные записи.
try {
    $pdo = Database::connect($config);
    $installer->ensureInstalled($pdo);
} catch (Throwable $exception) {
    $setupController->show('Подключение или подготовка базы не удались. ' . $exception->getMessage());
    exit;
}

$blog = new BlogRepository($pdo);
$controller = new BlogController($blog, $templates);
$controller->dispatch($_GET);