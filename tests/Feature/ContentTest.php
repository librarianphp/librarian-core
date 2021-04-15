<?php

use Minicli\App;
use Librarian\Provider\ContentServiceProvider;
use Librarian\Provider\LibrarianServiceProvider;
use Librarian\Provider\TwigServiceProvider;
use Librarian\Request;

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

it('loads content from request and parses devto format', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $content = $app->content->fetch('posts/test0');

    expect($content->frontMatterGet('title'))->toEqual("Devo Produzir Conteúdo em Português ou Inglês?");
    expect($content->body_markdown)->toBeString();
});
