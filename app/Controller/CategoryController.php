<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\TemplateRenderer;
use App\Repository\CategoryRepository;

/** Загружает данные рубрики и отображает список её статей. */
final class CategoryController
{
    /** Максимум статей на одной странице рубрики. */
    private const PAGE_SIZE = 6;

    /**
     * Создаёт контроллер рубрик.
     * @param CategoryRepository $categories Репозиторий рубрик.
     * @param TemplateRenderer $templates Рендерер шаблонов.
     */
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly TemplateRenderer $templates,
    ) {
    }

    /**
     * Загружает страницу рубрики с сортировкой и пагинацией.
     * @param string $slug Slug запрошенной рубрики.
     * @param array<string,mixed> $query GET-параметры запроса.
     * @return void
     * @throws \PDOException При ошибке чтения данных.
     */
    public function show(string $slug, array $query): void
    {
        $category = $this->categories->findBySlug($slug);
        if ($category === null) {
            http_response_code(404);
            $this->templates->render('pages/category-not-found.tpl', ['title' => 'Category not found']);
            return;
        }

        $sort = ($query['sort'] ?? 'date') === 'views' ? 'views' : 'date';
        $total = $this->categories->countPosts((int) $category['id']);
        $pages = max(1, (int) ceil($total / self::PAGE_SIZE));
        $pageValue = $query['page'] ?? 1;
        $pageNumber = is_numeric($pageValue) ? (int) $pageValue : 1;
        $page = max(1, min($pages, $pageNumber));
        $posts = $this->categories->posts(
            (int) $category['id'],
            $sort,
            self::PAGE_SIZE,
            ($page - 1) * self::PAGE_SIZE,
        );

        foreach ($posts as &$post) {
            $post['publishedLabel'] = date('F j, Y', strtotime($post['published_at']));
        }
        unset($post);

        $pagination = [];
        for ($number = 1; $number <= $pages; $number++) {
            $pagination[] = [
                'number' => $number,
                'current' => $number === $page,
                'url' => '?' . http_build_query([
                    'category' => $category['slug'],
                    'sort' => $sort,
                    'page' => $number,
                ]),
            ];
        }

        $this->templates->render('pages/category.tpl', [
            'title' => $category['name'],
            'category' => $category,
            'posts' => $posts,
            'total' => $total,
            'sort' => $sort,
            'pagination' => $pagination,
            'hasMultiplePages' => $pages > 1,
        ]);
    }
}
