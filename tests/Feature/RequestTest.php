<?php

declare(strict_types=1);

use Librarian\ContentType;
use Librarian\Provider\ContentServiceProvider;
use Librarian\Provider\LibrarianServiceProvider;
use Librarian\Provider\RouterServiceProvider;
use Librarian\Provider\TwigServiceProvider;
use Librarian\Request;
use Minicli\App;

beforeEach(function () {
    $this->config = [
        'debug' => true,
        'templates_path' => __DIR__ . '/../resources',
        'data_path' => __DIR__ . '/../resources',
        'cache_path' => __DIR__ . '/../resources',
    ];

    $request = new Request(['param1' => 'value1', 'param2' => 'value2'], '/docs/en/test0');
    $router = Mockery::mock(RouterServiceProvider::class);
    $router->shouldReceive('load');
    $router->shouldReceive('getRequest')->andReturn($request);

    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());
    $app->addService('twig', new TwigServiceProvider());
    $app->addService('librarian', new LibrarianServiceProvider());
    $app->addService('router', $router);
    $this->app = $app;
});

it('passes request through router', function () {
    $request = $this->app->router->getRequest();
    expect($request)->toBeInstanceOf(Request::class)
        ->and($request->getParams())->toBe(['param1' => 'value1', 'param2' => 'value2']);
});

it('loads content in nested structure', function () {
    $request = $this->app->router->getRequest();
    expect($request->getRoute())->toBe('docs')
        ->and($request->getParent())->toBe('/docs/en')
        ->and($request->getSlug())->toBe('test0');

    $contentType = $this->app->content->getContentType($request->getParent());
    expect($contentType)->toBeInstanceOf(ContentType::class)
        ->and($contentType->title)->toBe('English Docs');
});
