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
        'templates_path' => __DIR__ . '/../../resources',
        'data_path' => __DIR__ . '/../../resources',
        'cache_path' => __DIR__ . '/../../resources',
        'site_name' => '::site_name::',
        'site_description' => '::site_description::',
        'site_url' => '::site_url::',
        'site_author' => '::site_author::',
    ];

    $app = new App($this->config);
    $app->addService('content', new ContentServiceProvider());
    $app->addService('twig', new TwigServiceProvider());
    $app->addService('librarian', new LibrarianServiceProvider());

    $this->app = $app;
});

it('returns table of contents when ContentType is passed', function () {
    /** @var TwigServiceProvider $twig */
    $twig = $this->app->twig;
    $toc = $twig->getTwig()->getFunction('table_of_contents');
    expect($twig)->toBeInstanceOf(TwigServiceProvider::class)
        ->and($toc)->toBeInstanceOf(\Twig\TwigFunction::class);

    $callable = $toc->getCallable();
    expect(call_user_func($callable, new ContentType('docs', __DIR__ . '/../../resources/docs')))->toBeInstanceOf(ContentCollection::class);
});

it('returns table of contents when string is passed', function () {
    /** @var TwigServiceProvider $twig */
    $twig = $this->app->twig;
    $toc = $twig->getTwig()->getFunction('table_of_contents');
    expect($twig)->toBeInstanceOf(TwigServiceProvider::class)
        ->and($toc)->toBeInstanceOf(\Twig\TwigFunction::class);

    $callable = $toc->getCallable();
    expect(call_user_func($callable, 'docs'))->toBeInstanceOf(ContentCollection::class);
});
