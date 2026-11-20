<?php

declare(strict_types=1);

// Точка входа текущего этапа: проверяет подключение и готовность базы данных.
use App\Controller\SetupController;
use App\Core\Database;
use App\Core\TemplateRenderer;
use App\Infrastructure\ConfigRepository;
use App\Infrastructure\DatabaseSeeder;
use App\Infrastructure\Installer;
use App\Infrastructure\SchemaManager;

require __DIR__ . '/vendor/autoload.php';

// Подготавливаем компоненты, необходимые только для настройки базы.
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

// ок
$templates->render('pages/database-ready.tpl', ['title' => 'База данных подключена']);

