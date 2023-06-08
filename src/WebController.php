<?php

declare(strict_types=1);

namespace Librarian;

use Librarian\Provider\RouterServiceProvider;
use Minicli\App;
use Minicli\Command\CommandCall;
use Minicli\ControllerInterface;

abstract class WebController implements ControllerInterface
{
    protected App $app;

    protected CommandCall $input;

    /**
     * Optional method called when `run` is successfully finished.
     */
    public function teardown(): void
    {
        //
    }

    /**
     * Command Logic.
     */
    abstract public function handle(): void;

    /**
     * Called before `run`.
     */
    public function boot(App $app, CommandCall $input): void
    {
        $this->app = $app;
        $this->input = $input;
    }

    public function run(CommandCall $input): void
    {
        $this->input = $input;
        $this->handle();
    }

    public function getApp(): App
    {
        return $this->app;
    }

    public function getRequest(): Request
    {
        /** @var RouterServiceProvider $request */
        $router = $this->getApp()->router;

        return $router->getRequest();
    }

    public function getResponse(): Response
    {
        return new Response();
    }

    public function required(): array
    {
        return [];
    }
}
