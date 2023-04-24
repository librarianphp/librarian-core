<?php

use Minicli\App;
use Librarian\Provider\ContentServiceProvider;
use Librarian\ContentCollection;

beforeEach(function () {
    $this->config = [
        'debug' => true,
        'templates_path' => __DIR__ . '/../resources',
        'data_path' => __DIR__ . '/../resources',
        'cache_path' => __DIR__ . '/../resources'
    ];
});

it('loads content from data path', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll();
    expect($posts)->toBeInstanceOf(ContentCollection::class);

    expect($posts->total())->toEqual(5);
});

it('loads content in alphabetical (asc) order', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll(0, 10, false, 'asc');

    expect($posts->current()->frontMatterGet('title'))->toEqual("Devo Produzir Conteúdo em Português ou Inglês?");
});

it('loads content in alphabetical (desc) order', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll(0, 10, false, 'desc');

    expect($posts->current()->frontMatterGet('title'))->toEqual("Second Test - Testing Markdown Front Matter");
});

it('parses the markdown content', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll(0, 10, true);

    expect($posts->current()->frontMatterGet('title'))->toEqual("Second Test - Testing Markdown Front Matter");
    expect($posts->current()->getSlug())->toEqual("test2");
    expect($posts->current()->body_html)->toEqual("<h2>Testing</h2>\n");
});
