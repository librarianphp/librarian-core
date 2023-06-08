<?php

use Librarian\Provider\ContentServiceProvider;
use Librarian\Provider\FeedServiceProvider;
use Lukaswhite\FeedWriter\RSS2;
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
    $app->addService('feed', new FeedServiceProvider());

    $this->app = $app;
});

it('returns correctly custom feed path', function () {
    expect($this->app->feed->getCustomFeedPath(is_static: false))->toBe('::site_url::/feed')
        ->and($this->app->feed->getCustomFeedPath(is_static: true))->toBe('::site_url::/feed.rss');
});

it('builds rss feed', function () {
    expect($this->app->feed->buildFeed())
        ->toBeInstanceOf(RSS2::class);
});
