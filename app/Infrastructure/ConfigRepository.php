<?php

declare(strict_types=1);

namespace App\Infrastructure;

/** Загружает и сохраняет локальные параметры подключения к базе данных. */
final class ConfigRepository
{
    /** @param string $path Абсолютный путь к PHP-файлу конфигурации. */
    public function __construct(private readonly string $path)
    {
    }

    /**
     * Загружает настройки подключения из PHP-файла.
     * @return array<string,mixed> Настройки либо пустой массив, если файл отсутствует, пуст или вернул не массив.
     */
    public function load(): array
    {
        if (!is_file($this->path) || filesize($this->path) === 0) {
            return [];
        }

        try {
            $config = require $this->path;
            return is_array($config) ? $config : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Сохраняет настройки как PHP-массив с блокировкой записи.
     * @param array<string,mixed> $config Параметры подключения.
     * @return void
     * @throws \RuntimeException Если не удалось записать файл.
     */
    public function save(array $config): void
    {
        $contents = "<?php\n// Параметры подключения к базе данных.\nreturn " . var_export($config, true) . ";\n";
        if (file_put_contents($this->path, $contents, LOCK_EX) === false) {
            throw new \RuntimeException('Не удалось сохранить config.php. Проверьте права на запись.');
        }
    }
}



