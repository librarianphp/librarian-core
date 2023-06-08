<?php

declare(strict_types=1);

namespace Librarian\Provider;

use Exception;
use Minicli\App;
use Minicli\ServiceInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class TwigServiceProvider implements ServiceInterface
{
    protected string $templates_path;

    protected TwigEnvironment $twig;

    /**
     * @throws Exception
     */
    public function load(App $app): void
    {
        $config = $app->config;

        if (! $config->has('templates_path')) {
            throw new Exception('Missing Templates Path.');
        }

        $this->setTemplatesPath($config->templates_path);
        $loader = new FilesystemLoader($this->getTemplatesPath());

        $params = [];

        if ($config->has('cache_path')) {
            $params['cache_path'] = $config->cache_path . '/twig_cache';
        }

        $this->twig = new TwigEnvironment($loader, $params);
    }

    public function getTwig(): TwigEnvironment
    {
        return $this->twig;
    }

    public function setTemplatesPath(string $path): void
    {
        $this->templates_path = $path;
    }

    public function getTemplatesPath(): string
    {
        return $this->templates_path;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render($template_file, array $data): string
    {
        $template = $this->twig->load($template_file);

        return $template->render($data);
    }
}
