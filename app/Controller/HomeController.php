<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\TemplateRenderer;
use App\Repository\HomeRepository;

/** Подготавливает данные из базы и передаёт их шаблону главной страницы. */
final class HomeController
{
    public function __construct(
        private readonly HomeRepository $homeRepository,
        private readonly TemplateRenderer $templates,
    ) {
    }

    public function show(): void
    {
        $sections = $this->homeRepository->sections();

        foreach ($sections as &$section) {
            foreach ($section['posts'] as &$post) {
                $post['publishedLabel'] = date('F j, Y', strtotime($post['published_at']));
            }
            unset($post);
        }
        unset($section);

        $this->templates->render('pages/home.tpl', [
            'title' => 'Home',
            'sections' => $sections,
        ]);
    }
}