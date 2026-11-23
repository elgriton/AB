<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\TemplateRenderer;
use App\Repository\BlogRepository;

/** Обрабатывает запросы главной страницы, рубрик и отдельных статей. */
final class BlogController
{
    // Число статей на одной странице рубрики.
    private const PAGE_SIZE = 6;

    public function __construct(
        private readonly BlogRepository $blog,
        private readonly TemplateRenderer $templates,
    ) {
    }

    /** Выбирает страницу по GET-параметрам и получает общие данные навигации. */
    public function dispatch(array $query): void
    {
        $navigation = $this->blog->navigationCategories();
        if (!empty($query['post'])) {
            $this->showPost((string) $query['post'], $navigation);
            return;
        }
        if (!empty($query['category'])) {
            $this->showCategory((string) $query['category'], $query, $navigation);
            return;
        }
        $this->showHome($navigation);
    }

    /** Строит блоки главной: каждая рубрика и три последних статьи в ней. */
    private function showHome(array $navigation): void
    {
        $sections = $this->blog->homeCategories();
        foreach ($sections as &$section) {
            $section['posts'] = $this->decoratePosts($section['posts']);
        }
        unset($section);
        $this->templates->render('pages/home.tpl', [
            'title' => 'Главная',
            'metaTitle' => 'Easy recipes for every day',
            'metaDescription' => 'Tested recipes, seasonal ingredients, and ideas for delicious home cooking.',
            'canonicalUrl' => $this->absoluteUrl('/index.php'),
            'ogType' => 'website',
            'ogImageUrl' => $this->absoluteUrl('/assets/images/food.svg'),
            'navigation' => $navigation,
            'sections' => $sections,
        ]);
    }

    /** Проверяет рубрику, применяет сортировку и рассчитывает данные пагинации. */
    private function showCategory(string $slug, array $query, array $navigation): void
    {
        $category = $this->blog->findCategory($slug);
        if (!$category) {
            $this->notFound($navigation);
            return;
        }

        $sort = ($query['sort'] ?? 'date') === 'views' ? 'views' : 'date';
        $total = $this->blog->countCategoryPosts((int) $category['id']);
        $pages = max(1, (int) ceil($total / self::PAGE_SIZE));
        $pageValue = $query['page'] ?? 1;
        $pageNumber = is_numeric($pageValue) ? (int) $pageValue : 1;
        $page = max(1, min($pages, $pageNumber));
        $posts = $this->decoratePosts($this->blog->categoryPosts(
            (int) $category['id'],
            $sort,
            self::PAGE_SIZE,
            ($page - 1) * self::PAGE_SIZE,
        ));

        $pagination = [];
        $firstPage = max(1, min($page - 2, $pages - 4));
        $lastPage = min($pages, $firstPage + 4);

        for ($number = $firstPage; $number <= $lastPage; $number++) {
            $pagination[] = [
                'number' => $number,
                'current' => $number === $page,
                'url' => '?' . http_build_query(['category' => $category['slug'], 'sort' => $sort, 'page' => $number]),
            ];
        }

        $previousPageUrl = '?' . http_build_query([
            'category' => $category['slug'],
            'sort' => $sort,
            'page' => max(1, $page - 1),
        ]);
        $nextPageUrl = '?' . http_build_query([
            'category' => $category['slug'],
            'sort' => $sort,
            'page' => min($pages, $page + 1),
        ]);

        $this->templates->render('pages/category.tpl', [
            'title' => $category['name'],
            'metaTitle' => $category['name'] . ' recipes',
            'metaDescription' => mb_substr((string) $category['description'], 0, 155),
            'canonicalUrl' => $this->absoluteUrl('/index.php?' . http_build_query(array_filter([
                'category' => $category['slug'],
                'page' => $page > 1 ? $page : null,
            ]))),
            'ogType' => 'website',
            'ogImageUrl' => $this->absoluteUrl('/assets/images/food.svg'),
            'navigation' => $navigation,
            'category' => $category,
            'posts' => $posts,
            'total' => $total,
            'sort' => $sort,
            'page' => $page,
            'pages' => $pages,
            'pagination' => $pagination,
            'previousPageUrl' => $previousPageUrl,
            'nextPageUrl' => $nextPageUrl,
            'hasMultiplePages' => $pages > 1,
            'hasPosts' => $total > 0,
        ]);
    }

    /** Загружает статью, увеличивает просмотры и подбирает похожие публикации. */
    private function showPost(string $slug, array $navigation): void
    {
        $post = $this->blog->findPost($slug);
        if (!$post) {
            $this->notFound($navigation);
            return;
        }

        $this->blog->incrementViews((int) $post['id']);
        $post['views'] = (int) $post['views'] + 1;
        $post['publishedLabel'] = date('d.m.Y', strtotime($post['published_at']));
        $post['imageUrl'] = $this->safeImageUrl((string) $post['image']);
        $similar = $this->decoratePosts($this->blog->similarPosts($post));

        $this->templates->render('pages/post.tpl', [
            'title' => $post['title'],
            'metaTitle' => $post['title'],
            'metaDescription' => mb_substr((string) $post['excerpt'], 0, 155),
            'canonicalUrl' => $this->absoluteUrl('/index.php?post=' . rawurlencode($post['slug'])),
            'ogType' => 'article',
            'ogImageUrl' => $post['imageUrl'] !== '' ? $this->absoluteUrl($post['imageUrl']) : '',
            'navigation' => $navigation,
            'post' => $post,
            'similarPosts' => $similar,
        ]);
    }

    private function notFound(array $navigation): void
    {
        http_response_code(404);
        $this->templates->render('pages/not-found.tpl', [
            'title' => 'Страница не найдена',
            'navigation' => $navigation,
        ]);
    }

    private function decoratePosts(array $posts): array
    {
        foreach ($posts as &$post) {
            $post['publishedLabel'] = date('d.m.Y', strtotime($post['published_at']));
            $post['imageUrl'] = $this->safeImageUrl((string) ($post['image'] ?? ''));
        }
        unset($post);
        return $posts;
    }

    /** Разрешает только безопасные веб-адреса и пути к изображениям из проекта. */
    private function safeImageUrl(string $image): string
    {
        $image = trim($image);
        if (!empty($image) && (preg_match('#^https?://#i', $image) || (str_starts_with($image, '/') && !str_starts_with($image, '//')) || preg_match('#^assets/(?!.*\.\.)[a-zA-Z0-9_./-]+$', $image) )) {
            return $image;
        }
        return '/assets/images/recipe-placeholder.svg';
    }

    /** Собирает абсолютный адрес страницы для canonical и Open Graph. */
    private function absoluteUrl(string $path): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $path;
    }
}










