<?php

declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
/** Создаёт таблицы блога, связи и индексы в подключённой базе данных. */
final class SchemaManager
{
    /**
     * Создаёт таблицы, если они ещё не существуют.
     *
     * @param PDO $pdo Соединение с целевой базой данных.
     * @return void
     * @throws \PDOException Если MySQL отклонит один из SQL-запросов.
     */
    public function create(PDO $pdo): void
    {
        $statements = [
            "CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                slug VARCHAR(160) NOT NULL UNIQUE,
                description TEXT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS tags (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(110) NOT NULL UNIQUE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(220) NOT NULL,
                slug VARCHAR(240) NOT NULL UNIQUE,
                excerpt TEXT NOT NULL,
                body MEDIUMTEXT NOT NULL,
                image VARCHAR(500) NOT NULL DEFAULT '',
                views INT UNSIGNED NOT NULL DEFAULT 0,
                published_at DATETIME NOT NULL,
                INDEX idx_posts_published (published_at),
                INDEX idx_posts_views (views)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS post_categories (
                post_id INT UNSIGNED NOT NULL,
                category_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (post_id, category_id),
                CONSTRAINT fk_post_categories_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_post_categories_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS post_tags (
                post_id INT UNSIGNED NOT NULL,
                tag_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (post_id, tag_id),
                CONSTRAINT fk_post_tags_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_post_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }
    }
}
