<?php

declare(strict_types=1);

namespace Librarian\Provider;

use Exception;
use Librarian\Exception\RouteNotFoundException;
use Librarian\Request;
use Minicli\App;
use Minicli\ServiceInterface;

class RouterServiceProvider implements ServiceInterface
{
    protected App $app;

    protected Request $request;

    public function load(App $app): void
    {
        $this->app = $app;
        $this->request = new Request($_REQUEST, $_SERVER['REQUEST_URI']);
    }

    public function getRoute(): string
    {
        return $this->request->getRoute() ?: 'index';
    }

    /**
     * @throws RouteNotFoundException
     */
    public function getCallableRoute(): string
    {
        $route = $this->getRoute();

        $controller = $this->app->commandRegistry->getCallableController('web', $route);

        if ($controller === null) {
            //no dedicated controller found. is it a static content from the data dir? if not, throw exception

            if (! $this->app->config->has('data_path')) {
                throw new Exception('Missing Static Data Path.');
            }

            $data_path = $this->app->config->data_path;

            if (is_dir($data_path . '/' . $route)) {
                return 'content';
            }

            throw new RouteNotFoundException('Route not Found.');
        }

        return $this->getRoute();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
