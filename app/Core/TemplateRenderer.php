<?php

declare(strict_types=1);

namespace App\Core;

use Smarty\Smarty;

/** Настраиваем Smarty и передаёи данные в шаблоны для вывода страниц. */
final class TemplateRenderer
{
    private Smarty $smarty;

    /** Создаем каталоги для кеша и скомпилированных шаблонов */
    public function __construct(string $templateDirectory, string $storageDirectory)
    {
        foreach ([$storageDirectory . '/compiled', $storageDirectory . '/cache'] as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
        }
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($templateDirectory);
        $this->smarty->setCompileDir($storageDirectory . '/compiled');
        $this->smarty->setCacheDir($storageDirectory . '/cache');
        $this->smarty->setCaching(Smarty::CACHING_OFF);
        $this->smarty->escape_html = true;
    }

    /** Передаёт переменные в Smarty и отображает указанный файл шаблона. */
    public function render(string $template, array $data = []): void
    {
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        $this->smarty->display($template);
    }
}




