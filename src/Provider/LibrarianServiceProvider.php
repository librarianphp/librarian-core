<?php

namespace Librarian\Provider;

use Librarian\Exception\ContentNotFoundException;
use Minicli\App;
use Minicli\ServiceInterface;
use Twig\TwigFunction;

class LibrarianServiceProvider implements ServiceInterface
{
    public function boot()
    {
        //dummy method to force eager load
    }

    /**
     * @param App $app
     * @throws \Exception
     */
    public function load(App $app): void
    {
        /** @var TwigServiceProvider $twig_service */
        $twig_service = $app->twig;

        if ($twig_service === null) {
            throw new \Exception("Unable to find Twig Service Provider. Make sure it is registered first.");
        }

        $twig = $twig_service->getTwig();

        $twig->addFunction(new TwigFunction('site_title', function () use ($app) {
            return $app->config->site_name ?: null;
        }));

        $twig->addFunction(new TwigFunction('site_description', function () use ($app) {
            return $app->config->site_description ?: null;
        }));

        $twig->addFunction(new TwigFunction('site_root', function () use ($app) {
            return $app->config->site_root ?: null;
        }));

        $twig->addFunction(new TwigFunction('site_url', function () use ($app) {
            return $app->config->site_url ?: null;
        }));

        $twig->addFunction(new TwigFunction('config', function ($key) use ($app) {
            return $app->config->$key ?: null;
        }));

        $twig->addFunction(new TwigFunction('social_links', function () use ($app) {
            return $app->config->social_links ?: null;
        }));

        $twig->addFunction(new TwigFunction('site_about', function () use ($app) {
            if ($app->config->has('site_about')) {
                try {
                    $content = $app->content->fetch($app->config->site_about);
                    if ($content) {
                        return $content->frontMatterGet('description');
                    }
                } catch (ContentNotFoundException $e) {
                    return $app->config->site_description;
                }
            }

            return $app->config->site_description ?: null;
        }));

        $twig->addFunction(new TwigFunction('tag_list', function () use ($app) {
            /** @var ContentServiceProvider $content */
            $content = $app->content;
            return $content->fetchTagList();
        }));

        $twig->addFunction(new TwigFunction('content_types', function () use ($app) {
            /** @var ContentServiceProvider $content */
            $content = $app->content;
            return $content->getContentTypes();
        }));

        $twig->addFunction(new TwigFunction('request_info', function () use ($app) {
            /** @var RouterServiceProvider $router */
            $router = $app->router;
            return $router->getRequest();
        }));

        $twig->addFunction(new TwigFunction('table_of_contents', function ($content_type) use ($app) {
            /** @var ContentServiceProvider $content */
            $content = $app->content;
            return $content->fetchFrom($content_type, 0, 0, false, 'index');
        }));
    }
}
