<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/** Получает рубрику и её статьи с заданной сортировкой и пагинацией. */
final class CategoryRepository
{
    /**
     * Создаёт репозиторий рубрик.
     *
     * @param PDO $pdo Соединение с базой данных.
     */
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Находит рубрику по slug.
     *
     * @param string $slug Адресный идентификатор рубрики.
     * @return array{id:int|string,name:string,slug:string,description:string}|null Данные рубрики либо null.
     * @throws \PDOException Если запрос к базе данных завершился ошибкой.
     */
    public function findBySlug(string $slug): ?array
    {
        $query = $this->pdo->prepare(
            'SELECT id, name, slug, description
             FROM categories
             WHERE slug = ?
             LIMIT 1'
        );
        $query->execute([$slug]);

        return $query->fetch() ?: null;
    }

    /**
     * Считает связи статей с указанной рубрикой.
     *
     * @param int $categoryId Идентификатор рубрики.
     * @return int Количество связанных статей.
     * @throws \PDOException Если запрос к базе данных завершился ошибкой.
     */
    public function countPosts(int $categoryId): int
    {
        $query = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM post_categories
             WHERE category_id = ?'
        );
        $query->execute([$categoryId]);

        return (int) $query->fetchColumn();
    }

    /**
     * Возвращает страницу статей рубрики с выбранной сортировкой.
     *
     * @param int $categoryId Идентификатор рубрики.
     * @param string $sort Порядок: `views` или дата публикации по убыванию.
     * @param int $limit Максимум статей в результате.
     * @param int $offset Число пропускаемых статей.
     * @return list<array> Статьи; при равном числе просмотров выше ставится более свежая.
     * @throws \PDOException Если запрос к базе данных завершился ошибкой.
     */
    public function posts(int $categoryId, string $sort, int $limit, int $offset): array
    {
        $orderBy = $sort === 'views'
            ? 'p.views DESC, p.published_at DESC, p.id DESC'
            : 'p.published_at DESC, p.id DESC';

        $query = $this->pdo->prepare(
            "SELECT p.title, p.slug, p.excerpt, p.views, p.published_at
             FROM posts p
             INNER JOIN post_categories pc ON pc.post_id = p.id
             WHERE pc.category_id = ?
             ORDER BY $orderBy
             LIMIT ? OFFSET ?"
        );
        $query->bindValue(1, $categoryId, PDO::PARAM_INT);
        $query->bindValue(2, $limit, PDO::PARAM_INT);
        $query->bindValue(3, $offset, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll();
    }
}
