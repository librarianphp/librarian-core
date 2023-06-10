<?php

declare(strict_types=1);

namespace Librarian\Provider;

use DateTime;
use Librarian\Content;
use Lukaswhite\FeedWriter\RSS2;
use Minicli\App;
use Minicli\Config;
use Minicli\ServiceInterface;

class FeedServiceProvider implements ServiceInterface
{
    public Config $site_config;

    public ContentServiceProvider $content_provider;

    public function load(App $app): void
    {
        $this->site_config = $app->config;
        $this->content_provider = $app->content;
    }

    /**
     * @throws \Librarian\Exception\ContentNotFoundException
     */
    public function buildFeed(bool $is_static = false): RSS2
    {
        $feed = new RSS2();

        $channel = $feed
            ->addChannel()
            ->title($this->site_config->site_name)
            ->description($this->site_config->site_description)
            ->link($this->site_config->site_url)
            ->addLink('href', $this->site_config->site_url . $this->getCustomFeedPath($is_static))
            ->language('en-US')
            ->copyright('Copyright ' . date('Y') . ', ' . $this->site_config->site_name)
            ->pubDate(new DateTime())
            ->lastBuildDate(new DateTime())
            ->ttl(60);

        $content_list = $this->content_provider->fetchAll();

        /** @var Content $content */
        foreach ($content_list as $content) {
            $channel
                ->addItem()
                ->title($content->frontMatterGet('title', $content->default_title))
                ->description('<div>' . $content->frontMatterGet('description', '') . '</div>')
                ->encodedContent('<div>' . $content->frontMatterGet('description', '') . '</div>')
                ->link($this->site_config->site_url . '/' . $content->getLink())
                ->author($this->site_config->site_author)
                ->pubDate(new DateTime($content->getDate()))
                ->guid($this->site_config->site_url . '/' . $content->getLink(), true);
        }

        return $feed;
    }

    public function getCustomFeedPath(bool $is_static = false): string
    {
        return $is_static ? '/feed.rss' : '/feed';
    }
}
