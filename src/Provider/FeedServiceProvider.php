<?php

namespace Librarian\Provider;

use DateTime;
use Lukaswhite\FeedWriter\RSS2;
use Minicli\App;
use Minicli\Config;
use Minicli\ServiceInterface;

class FeedServiceProvider implements ServiceInterface
{
    /** @var Config */
    public Config $site_config;

    /** @var ContentServiceProvider */
    public ContentServiceProvider $content_provider;

    /**
     * @param App $app
     */
    public function load(App $app): void
    {
        $this->site_config = $app->config;
        $this->content_provider = $app->content;
    }

    /**
     * @param bool $is_static
     */
    public function buildFeed(bool $is_static = false): RSS2
    {
        $feed = new RSS2();

        $channel = $feed
            ->addChannel()
            ->title($this->site_config->site_name)
            ->description($this->site_config->site_description)
            ->link($this->site_config->site_url)
            ->addLink('href', $this->getCustomFeedPath($is_static))
            ->language('en-US')
            ->copyright('Copyright ' . date('Y') . ', '. $this->site_config->site_name)
            ->pubDate(new DateTime())
            ->lastBuildDate(new DateTime())
            ->ttl(60);

        $content_list = $this->content_provider->fetchAll();

        foreach ($content_list as $content) {
            $channel
                ->addItem()
                ->title($content->frontMatterGet('title') ?? $content->getAlternateTitle())
                ->description('<div>' . $content->frontMatterGet('description', '') . '</div>')
                ->encodedContent('<div>' . $content->frontMatterGet('description', '') . '</div>')
                ->link($this->site_config->site_url . '/' . $content->getLink())
                ->author($this->site_config->site_author)
                ->pubDate(new DateTime($content->getDate()))
                ->guid($this->site_config->site_url . '/' . $content->getLink(), true);
        }

        return $feed;
    }

    /**
     * @param bool $is_static
     * @return string
     */
    public function getCustomFeedPath(bool $is_static = false): string
    {
        return $is_static
            ? $this->site_config->site_url . '/feed.rss'
            : $this->site_config->site_url . '/feed';
    }
}
