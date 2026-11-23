<?php

declare(strict_types=1);

namespace App\Core;

use Smarty\Smarty;

/** Настраивает Smarty и отображает шаблоны сайта. */
final class TemplateRenderer
{
    private Smarty $smarty;

    /**
     * Настраивает каталоги шаблонов и создаёт директории хранения Smarty при необходимости.
     * @param string $templateDirectory Путь к исходным Smarty-шаблонам.
     * @param string $storageDirectory Корневой каталог кеша и скомпилированных шаблонов.
     * @throws \RuntimeException Если каталоги хранилища создать не удалось.
     */
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

    /**
     * Передаёт данные шаблону и выводит его.
     * @param string $template Имя шаблона относительно каталога шаблонов.
     * @param array<string,mixed> $data Данные, доступные в шаблоне.
     * @return void
     * @throws \Smarty\Exception Если Smarty не может обработать шаблон.
     */
    public function render(string $template, array $data = []): void
    {
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        $this->smarty->display($template);
    }
}




