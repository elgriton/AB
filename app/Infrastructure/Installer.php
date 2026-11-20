<?php

declare(strict_types=1);

namespace App\Infrastructure;

use PDO;

/** Проверяет, создана ли схема, и связывает миграцию с начальным заполнением. */
final class Installer
{
    public function __construct(
        private readonly SchemaManager $schema,
        private readonly DatabaseSeeder $seeder,
    ) {
    }

    /** Считает установку готовой, только если в базе присутствуют все пять таблиц. */
    public function isInstalled(PDO $pdo): bool
    {
        $statement = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('categories', 'tags', 'posts', 'post_categories', 'post_tags')");
        $statement->execute();
        return (int) $statement->fetchColumn() === 5;
    }

    public function install(PDO $pdo): int
    {
        // Создаем таблицы заполняем данными
        $this->schema->create($pdo);
        return $this->seeder->seedIfEmpty($pdo);
    }

    /** Создаёт отсутствующую схему и при необходимости заполняет пустую базу. */
    public function ensureInstalled(PDO $pdo): void
    {
        if (!$this->isInstalled($pdo)) {
            $this->install($pdo);
            return;
        }
        $this->seeder->seedIfEmpty($pdo);
    }
}




