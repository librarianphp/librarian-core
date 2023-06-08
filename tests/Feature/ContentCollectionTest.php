<?php

declare(strict_types=1);

use Librarian\ContentCollection;
use Librarian\ContentType;
use Librarian\Provider\ContentServiceProvider;
use Minicli\App;

beforeEach(function () {
    $this->config = [
        'debug' => true,
        'templates_path' => __DIR__ . '/../resources',
        'data_path' => __DIR__ . '/../resources',
        'cache_path' => __DIR__ . '/../resources',
    ];
});

it('loads content from data path', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll();
    expect($posts)->toBeInstanceOf(ContentCollection::class)
        ->and($posts->total())->toBe(5);

});

it('orders content in alphabetical (asc) order', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll(0, 10, false, 'asc');

    expect($posts->current()->frontMatterGet('title'))->toBe('Devo Produzir Conteúdo em Português ou Inglês?');
});

it('orders content in alphabetical (desc) order', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll(0, 10, false, 'desc');

    expect($posts->current()->frontMatterGet('title'))->toBe('Second Test - Testing Markdown Front Matter');
});

it('orders content based on front matter index (index)', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll(0, 10, false, 'index');

    expect($posts->current()->frontMatterGet('title'))->toBe('Testing Markdown Front Matter');
});

it('orders content type posts based on front matter index (index)', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());
    $cType = $app->content->getContentType('posts');
    expect($cType)->toBeInstanceOf(ContentType::class);
    $posts = $app->content->fetchFrom($cType, 0, 10, false, 'index');

    expect($posts->current()->frontMatterGet('title'))->toBe('Testing Markdown Front Matter');
});

it('parses the markdown content', function () {
    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());

    $posts = $app->content->fetchAll(0, 10, true);

    expect($posts->current()->frontMatterGet('title'))->toBe('Second Test - Testing Markdown Front Matter')
        ->and($posts->current()->getSlug())->toBe('test2')
        ->and($posts->current()->body_html)->toBe("<h2>Testing</h2>\n");
});
