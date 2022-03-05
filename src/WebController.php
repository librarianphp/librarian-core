<?php

namespace Librarian;

use Minicli\App;
use Minicli\Command\CommandCall;
use Minicli\ControllerInterface;
use Librarian\Provider\RouterServiceProvider;

abstract class WebController implements ControllerInterface
{
    /** @var  App */
    protected $app;

    /** @var  CommandCall */
    protected $input;

    /**
     * Command Logic.
     * @return void
     */
    abstract public function handle();

    /**
     * Called before `run`.
     * @param App $app
     */
    public function boot(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param CommandCall $input
     */
    public function run(CommandCall $input)
    {
        $this->input = $input;
        $this->handle();
    }

    /**
     * Optional method called when `run` is successfully finished.
     * @return void
     */
    public function teardown()
    {
        //
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        /** @var RouterServiceProvider $request */
        $router = $this->getApp()->router;

        return $router->getRequest();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return new Response();
    }
}
