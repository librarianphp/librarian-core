<?php

namespace Librarian\Provider;;

use Minicli\App;
use Minicli\Config;
use Twig\Loader\FilesystemLoader;
use Minicli\ServiceInterface;
use Twig\Environment as TwigEnvironment;

class TwigServiceProvider implements ServiceInterface
{
    /** @var string */
    protected $templates_path;
    /** @var TwigEnvironment */
    protected $twig;

    /**
     * @param App $app
     * @throws \Exception
     */
    public function load(App $app)
    {
        /** @var Config $config */
        $config = $app->config;

        if (!$config->has('templates_path')) {
            throw new \Exception("Missing Templates Path.");
        }

        $this->setTemplatesPath($config->templates_path);
        $loader = new FilesystemLoader($this->getTemplatesPath());

        $params = [];

        if ($config->has('cache_path')) {
            $params['cache_path'] = $config->cache_path . '/twig_cache';
        }

        $this->twig = new TwigEnvironment($loader, $params);
    }

    /**
     * @return TwigEnvironment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * @param string $path
     */
    public function setTemplatesPath($path)
    {
        $this->templates_path = $path;
    }

    /**
     * @return string
     */
    public function getTemplatesPath()
    {
        return $this->templates_path;
    }

    /**
     * @param $template_file
     * @param array $data
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render($template_file, array $data)
    {
        $template = $this->twig->load($template_file);
        return $template->render($data);
    }
}