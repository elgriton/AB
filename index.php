<?php

declare(strict_types=1);

/**
 * Точка входа сайта: проверяет конфигурацию и базу данных,
 * а затем передаёт запрос контроллеру блога.
 */
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

/** Обрабатываем отправленную форму установки базы данных. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_database'])) {
    $setupController->process($_POST);
    exit;
}

/** Загружаем локальные параметры подключения, если они настроены. */
$config = $configRepository->load();
if (!isset($config['host'], $config['port'], $config['database'], $config['username'], $config['password'])) {
    $setupController->show();
    exit;
}

/** Проверяем соединение, наличие схемы и начальных записей. */
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
