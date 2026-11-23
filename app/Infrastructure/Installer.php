<?php

declare(strict_types=1);

namespace App\Infrastructure;

use PDO;

/** Проверяет, создана ли схема, и связывает миграцию с начальным заполнением. */
final class Installer
{
    /**
     * Создаёт установщик схемы и демонстрационных данных.
     * @param SchemaManager $schema Создатель таблиц.
     * @param DatabaseSeeder $seeder Начальный заполнитель данных.
     */
    public function __construct(
        private readonly SchemaManager $schema,
        private readonly DatabaseSeeder $seeder,
    ) {
    }

    /**
     * Проверяет наличие всех таблиц приложения.
     * @param PDO $pdo Соединение с выбранной базой.
     * @return bool true, если найдены все пять таблиц.
     * @throws \PDOException При ошибке запроса к information_schema.
     */
    public function isInstalled(PDO $pdo): bool
    {
        $statement = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('categories', 'tags', 'posts', 'post_categories', 'post_tags')");
        $statement->execute();
        return (int) $statement->fetchColumn() === 5;
    }

    /**
     * Создаёт схему и запускает первоначальное заполнение.
     * @param PDO $pdo Соединение с целевой базой.
     * @return int Число добавленных демонстрационных статей.
     * @throws \PDOException При ошибке создания схемы или заполнения.
     */
    public function install(PDO $pdo): int
    {
        $this->schema->create($pdo);
        return $this->seeder->seedIfEmpty($pdo);
    }

    /**
     * Создаёт отсутствующую схему и заполняет базу, если это требуется.
     * @param PDO $pdo Соединение с базой.
     * @return void
     * @throws \PDOException При ошибке установки или заполнения.
     */
    public function ensureInstalled(PDO $pdo): void
    {
        if (!$this->isInstalled($pdo)) {
            $this->install($pdo);
            return;
        }
        $this->seeder->seedIfEmpty($pdo);
    }
}




