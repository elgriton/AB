<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/** Загружает для главной страницы рубрики и последние статьи из базы. */
final class HomeRepository
{
    /**
     * Создаёт репозиторий главной страницы.
     *
     * @param PDO $pdo Соединение с базой данных.
     */
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Возвращает рубрики, в которых есть записи, и три последние статьи каждой.
     *
     * @return list<array{id:int|string,name:string,slug:string,description:string,posts:list<array>}> Рубрики с вложенными статьями.
     * @throws \PDOException Если запрос к базе данных завершился ошибкой.
     */
    public function sections(): array
    {
        $categories = $this->pdo->query(
            'SELECT c.id, c.name, c.slug, c.description
             FROM categories c
             WHERE EXISTS (
                 SELECT 1
                 FROM post_categories pc
                 WHERE pc.category_id = c.id
             )
             ORDER BY c.name'
        )->fetchAll();

        $posts = $this->pdo->prepare(
            'SELECT p.title, p.slug, p.excerpt, p.views, p.published_at
             FROM posts p
             INNER JOIN post_categories pc ON pc.post_id = p.id
             WHERE pc.category_id = ?
             ORDER BY p.published_at DESC, p.id DESC
             LIMIT 3'
        );

        foreach ($categories as &$category) {
            $posts->execute([$category['id']]);
            $category['posts'] = $posts->fetchAll();
        }
        unset($category);

        return $categories;
    }
}
