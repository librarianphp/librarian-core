<?php

namespace Librarian;

use Minicli\App;
use Minicli\Command\CommandCall;
use Minicli\ControllerInterface;
use Librarian\Provider\RouterServiceProvider;

abstract class WebController implements ControllerInterface
{
    /** @var  App */
    protected App $app;

    /** @var  CommandCall */
    protected CommandCall $input;

    /**
     * Command Logic.
     * @return void
     */
    abstract public function handle(): void;

    /**
     * Called before `run`.
     * @param App $app
     * @param CommandCall $input
     */
    public function boot(App $app, CommandCall $input): void
    {
        $this->app = $app;
        $this->input = $input;
    }

    /**
     * @param CommandCall $input
     */
    public function run(CommandCall $input): void
    {
        $this->input = $input;
        $this->handle();
    }

    /**
     * Optional method called when `run` is successfully finished.
     * @return void
     */
    public function teardown(): void
    {
        //
    }

    /**
     * @return App
     */
    public function getApp(): App
    {
        return $this->app;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        /** @var RouterServiceProvider $request */
        $router = $this->getApp()->router;

        return $router->getRequest();
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return new Response();
    }

    public function required(): array
    {
        return [];
    }
}
