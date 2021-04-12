<?php

use Minicli\App;
use Librarian\Provider\ContentServiceProvider;
use Librarian\Provider\LibrarianServiceProvider;
use Minicli\Miniweb\Provider\TwigServiceProvider;

beforeEach(function () {
    $this->config = [
        'debug' => true,
        'templates_path' => __DIR__ . '/../resources',
        'data_path' => __DIR__ . '/../resources',
        'cache_path' => __DIR__ . '/../resources'
    ];
});

it('sets up ContentServiceProvider within Minicli App', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    expect($app->content)->toBeInstanceOf(ContentServiceProvider::class);
});

it('sets up LibrarianServiceProvider within Minicli App', function () {
    $app = new App($this->config);

    $app->addService('twig', new TwigServiceProvider());
    $app->addService('librarian', new LibrarianServiceProvider());

    expect($app->librarian)->toBeInstanceOf(LibrarianServiceProvider::class);
});