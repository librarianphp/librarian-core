<?php

declare(strict_types=1);

use Librarian\ContentCollection;
use Librarian\ContentType;
use Librarian\Provider\ContentServiceProvider;
use Librarian\Provider\LibrarianServiceProvider;
use Librarian\Provider\TwigServiceProvider;
use Minicli\App;

beforeEach(function () {
    $this->config = [
        'debug' => true,
        'templates_path' => __DIR__ . '/../resources',
        'data_path' => __DIR__ . '/../resources',
        'cache_path' => __DIR__ . '/../resources',
    ];

    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());
    $app->addService('twig', new TwigServiceProvider());
    $app->addService('librarian', new LibrarianServiceProvider());
    $this->app = $app;
});

it('sets up ContentServiceProvider within Minicli App', function () {
    expect($this->app->content)->toBeInstanceOf(ContentServiceProvider::class);
});

it('sets up LibrarianServiceProvider within Minicli App', function () {
    expect($this->app->librarian)->toBeInstanceOf(LibrarianServiceProvider::class);
});

it('loads content from request and parses front matter', function () {
    $content = $this->app->content->fetch('posts/test0');
    expect($content->frontMatterGet('title'))->toEqual('Devo Produzir Conteúdo em Português ou Inglês?')
        ->and($content->body_markdown)->toBeString();
});

it('loads the full list of content when no limit is passed', function () {
    $content = $this->app->content->fetchAll(0, 0);
    expect($content)->toBeInstanceOf(ContentCollection::class)
        ->and($content->total())->toBeGreaterThan(2);
});

it('loads tag list', function () {
    $tags = $this->app->content->fetchTagList(false);
    expect($tags)->toBeArray()
        ->and(count($tags))->toBeGreaterThan(2);
});

it('loads content types respecting index order', function () {
    $types = $this->app->content->getContentTypes();
    $ctype = $types[0];
    expect($ctype)->toBeInstanceOf(ContentType::class)
        ->and($ctype->title)->toEqual('Blog Posts');
});
