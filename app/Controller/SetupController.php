<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\TemplateRenderer;
use App\Infrastructure\ConfigRepository;
use App\Infrastructure\Installer;

/** Показывает форму настройки и выполняет первоначальное создание базы. */
final class SetupController
{
    /**
     * Создаёт контроллер первичной настройки базы данных.
     * @param TemplateRenderer $templates Рендерер страницы настройки.
     * @param ConfigRepository $configs Хранилище параметров подключения.
     * @param Installer $installer Установщик схемы и начальных данных.
     */
    public function __construct(
        private readonly TemplateRenderer $templates,
        private readonly ConfigRepository $configs,
        private readonly Installer $installer,
    ) {
    }

    /**
     * Выводит форму настройки с сохранёнными или стандартными значениями.
     * @param string $error Сообщение об ошибке для показа пользователю.
     * @param array<string,mixed> $values Значения полей, введённые в форме.
     * @return void
     */
    public function show(string $error = '', array $values = []): void
    {
        $saved = $this->configs->load();
        $defaults = [
            'host' => $saved['host'] ?? '127.0.0.1',
            'port' => $saved['port'] ?? 3306,
            'database' => $saved['database'] ?? 'ab_test',
            'username' => $saved['username'] ?? 'root',
            'password' => $saved['password'] ?? '',
        ];

        $this->templates->render('pages/setup.tpl', [
            'title' => 'Настройка базы данных',
            'error' => $error,
            'values' => array_merge($defaults, $values),
        ]);
    }

    /**
     * Валидирует POST-данные, устанавливает базу и сохраняет конфигурацию.
     * При ошибке возвращает пользователя к форме; при успехе отправляет redirect и завершает запрос.
     * @param array<string,mixed> $input Данные отправленной формы.
     * @return void
     */
    public function process(array $input): void
    {
        $settings = [
            'host' => trim((string) ($input['host'] ?? '')),
            'port' => trim((string) ($input['port'] ?? '')),
            'database' => trim((string) ($input['database'] ?? '')),
            'username' => trim((string) ($input['username'] ?? '')),
            'password' => (string) ($input['password'] ?? ''),
        ];

        $validation = !preg_match('/^[a-zA-Z0-9.:-]+$/', $settings['host'])
            || !ctype_digit($settings['port'])
            || (int) $settings['port'] < 1
            || (int) $settings['port'] > 65535
            || !preg_match('/^[a-zA-Z0-9_]+$/', $settings['database'])
            || $settings['username'] === '';

        if ($validation) {
            $this->show(
                'Проверьте адрес, порт, имя базы и пользователя. В имени базы разрешены латинские буквы, цифры и подчёркивание.',
                $settings,
            );
            return;
        }

        $settings['port'] = (int) $settings['port'];

        try {
            $server = Database::connect($settings, false);
            $server->exec(
                'CREATE DATABASE IF NOT EXISTS `' . $settings['database'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );

            $pdo = Database::connect($settings);
            $seededCount = $this->installer->install($pdo);
            $this->configs->save($settings);

            header('Location: index.php?database=ready&seeded=' . $seededCount);
            exit;
        } catch (\Throwable $exception) {
            $this->show('Не удалось подготовить базу данных: ' . $exception->getMessage(), $settings);
        }
    }
}
