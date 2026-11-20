<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/** Содержит SQL-запросы, которыми контроллер получает данные блога. */
final class BlogRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function navigationCategories(): array
    {
        return $this->pdo->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();
    }

    /** Возвращает рубрики с тремя самыми свежими статьями в каждой. */
    public function homeCategories(): array
    {
        $categories = $this->pdo->query('SELECT c.id, c.name, c.slug, c.description FROM categories c WHERE EXISTS (SELECT 1 FROM post_categories pc WHERE pc.category_id = c.id) ORDER BY c.name')->fetchAll();
        $posts = $this->pdo->prepare('SELECT p.id, p.title, p.slug, p.excerpt, p.image, p.views, p.published_at FROM posts p INNER JOIN post_categories pc ON pc.post_id = p.id WHERE pc.category_id = ? ORDER BY p.published_at DESC, p.id DESC LIMIT 3');

        foreach ($categories as &$category) {
            $posts->execute([$category['id']]);
            $category['posts'] = $posts->fetchAll();
        }
        unset($category);
        return $categories;
    }

    public function findCategory(string $slug): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, slug, description FROM categories WHERE slug = ? LIMIT 1');
        $statement->execute([$slug]);
        return $statement->fetch() ?: null;
    }

    public function countCategoryPosts(int $categoryId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(DISTINCT post_id) FROM post_categories WHERE category_id = ?');
        $statement->execute([$categoryId]);
        return (int) $statement->fetchColumn();
    }

    /** Выбирает страницу статей; порядок сортировки ограничен двумя разрешёнными вариантами. */
    public function categoryPosts(int $categoryId, string $sort, int $limit, int $offset): array
    {
        $orderBy = $sort === 'views'
            ? 'p.views DESC, p.published_at DESC, p.id DESC'
            : 'p.published_at DESC, p.id DESC';
        $statement = $this->pdo->prepare("SELECT p.id, p.title, p.slug, p.excerpt, p.image, p.views, p.published_at FROM posts p INNER JOIN post_categories pc ON pc.post_id = p.id WHERE pc.category_id = ? ORDER BY $orderBy LIMIT ? OFFSET ?");
        $statement->bindValue(1, $categoryId, PDO::PARAM_INT);
        $statement->bindValue(2, $limit, PDO::PARAM_INT);
        $statement->bindValue(3, $offset, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function findPost(string $slug): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, title, slug, excerpt, body, image, views, published_at FROM posts WHERE slug = ? LIMIT 1');
        $statement->execute([$slug]);
        $post = $statement->fetch();
        if (!$post) {
            return null;
        }

        $categories = $this->pdo->prepare('SELECT c.id, c.name, c.slug FROM categories c INNER JOIN post_categories pc ON pc.category_id = c.id WHERE pc.post_id = ? ORDER BY c.name');
        $categories->execute([$post['id']]);
        $post['categories'] = $categories->fetchAll();
        $tags = $this->pdo->prepare('SELECT t.id, t.name, t.slug FROM tags t INNER JOIN post_tags pt ON pt.tag_id = t.id WHERE pt.post_id = ? ORDER BY t.name');
        $tags->execute([$post['id']]);
        $post['tags'] = $tags->fetchAll();
        return $post;
    }

    /** Атомарно увеличивает счётчик просмотров выбранной статьи. */
    public function incrementViews(int $postId): void
    {
        $statement = $this->pdo->prepare('UPDATE posts SET views = views + 1 WHERE id = ?');
        $statement->execute([$postId]);
    }

    /** Ищет статьи по общим тегам, а при пустом наборе тегов — по рубрикам. */
    public function similarPosts(array $post): array
    {
        if ($post['tags']) {
            $tagIds = array_map(static fn(array $tag): int => (int) $tag['id'], $post['tags']);
            $marks = implode(',', array_fill(0, count($tagIds), '?'));
            $sql = "SELECT p.id, p.title, p.slug, p.excerpt, p.image, p.views, p.published_at, COUNT(DISTINCT pt.tag_id) AS relevance
                FROM posts p INNER JOIN post_tags pt ON pt.post_id = p.id
                WHERE p.id <> ? AND pt.tag_id IN ($marks)
                GROUP BY p.id ORDER BY relevance DESC, p.published_at DESC, p.id DESC LIMIT 3";
            $statement = $this->pdo->prepare($sql);
            $statement->execute(array_merge([(int) $post['id']], $tagIds));
            return $statement->fetchAll();
        }

        $categoryIds = array_map(static fn(array $category): int => (int) $category['id'], $post['categories']);
        if (!$categoryIds) {
            return [];
        }
        $marks = implode(',', array_fill(0, count($categoryIds), '?'));
        $sql = "SELECT DISTINCT p.id, p.title, p.slug, p.excerpt, p.image, p.views, p.published_at
            FROM posts p INNER JOIN post_categories pc ON pc.post_id = p.id
            WHERE p.id <> ? AND pc.category_id IN ($marks)
            ORDER BY p.published_at DESC, p.id DESC LIMIT 3";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array_merge([(int) $post['id']], $categoryIds));
        return $statement->fetchAll();
    }
}





