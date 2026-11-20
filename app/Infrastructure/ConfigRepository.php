<?php

declare(strict_types=1);

namespace App\Infrastructure;

/** Загружает и сохраняет локальные параметры подключения к базе данных. */
final class ConfigRepository
{
    public function __construct(private readonly string $path)
    {
    }

    /** Возвращает настройки из config.php; при отсутствии или ошибке отдаёт пустой массив. */
    // смотрим конфиг если он есть
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

    /** Записывает настройки в PHP-файл, который не должен попадать в Git. */
    // Сохраняем настройки подключения в config.php.
    public function save(array $config): void
    {
        $contents = "<?php\n// Параметры подключения к базе данных.\nreturn " . var_export($config, true) . ";\n";
        if (file_put_contents($this->path, $contents, LOCK_EX) === false) {
            throw new \RuntimeException('Не удалось сохранить config.php. Проверьте права на запись.');
        }
    }
}



