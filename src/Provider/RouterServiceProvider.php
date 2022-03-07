<?php

namespace Librarian\Provider;

use Minicli\App;
use Librarian\Exception\RouteNotFoundException;
use Librarian\Request;
use Minicli\ServiceInterface;

class RouterServiceProvider implements ServiceInterface
{
    /** @var App */
    protected App $app;

    /** @var Request */
    protected Request $request;

    /**
     * @param App $app
     */
    public function load(App $app): void
    {
        $this->app = $app;
        $this->request = new Request($_REQUEST, $_SERVER['REQUEST_URI']);
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->request->getRoute() ?: 'index';
    }

    /**
     * @return string
     * @throws RouteNotFoundException
     */
    public function getCallableRoute(): string
    {
        $route = $this->getRoute();

        $controller = $this->app->command_registry->getCallableController('web', $route);

        if ($controller === null) {
            //no dedicated controller found. is it a static content from the data dir? if not, throw exception

            if (!$this->app->config->has('data_path')) {
                throw new \Exception("Missing Static Data Path.");
            }

            $data_path = $this->app->config->data_path;

            if (is_dir($data_path . '/' . $route)) {
                return 'content';
            }

            throw new RouteNotFoundException('Route not Found.');
        }

        return $this->getRoute();
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
