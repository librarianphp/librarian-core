<?php

declare(strict_types=1);

namespace Librarian\Provider;

use Exception;
use Librarian\Content;
use Librarian\ContentCollection;
use Librarian\ContentType;
use Librarian\Exception\ContentNotFoundException;
use Librarian\Request;
use Minicli\App;
use Minicli\Minicache\FileCache;
use Minicli\ServiceInterface;
use Parsed\ContentParser;
use Parsed\CustomTagParserInterface;

class ContentServiceProvider implements ServiceInterface
{
    protected string $data_path;

    protected string $cache_path;

    protected array $parser_params = [];

    protected ContentParser $parser;

    /**
     * @throws Exception
     */
    public function load(App $app): void
    {
        if (! $app->config->has('data_path')) {
            throw new Exception('Missing Data Path.');
        }

        if (! $app->config->has('cache_path')) {
            throw new Exception('Missing Cache Path.');
        }

        $this->data_path = $app->config->data_path;
        $this->cache_path = $app->config->cache_path;

        if ($app->config->has('parser_params')) {
            $this->parser_params = $app->config->parser_params;
        }

        $this->parser = new ContentParser($this->parser_params);
    }

    public function registerTagParser(string $name, CustomTagParserInterface $tag_parser): void
    {
        $this->parser->addCustomTagParser($name, $tag_parser);
    }

    public function fetch(string $route, bool $parse_markdown = true): ?Content
    {
        $request = new Request([], '/' . $route);
        $filename = $this->data_path . '/' . $request->getRoute() . '/' . $request->getSlug() . '.md';
        $content = new Content();

        try {
            $content->load($filename);
            $content->setContentType($this->getContentType($request->getRoute()));

            $content->parse($this->parser, $parse_markdown);
        } catch (ContentNotFoundException $e) {
            return null;
        }

        return $content;
    }

    /**
     * @throws ContentNotFoundException
     */
    public function fetchAll(int $start = 0, int $limit = 20, bool $parse_markdown = false, string $orderBy = 'desc'): ContentCollection
    {
        $list = [];
        $contentTypes = $this->getContentTypes();

        /** @var ContentType $contentType */
        foreach ($contentTypes as $contentType) {
            foreach (glob($contentType->contentDir . '/' . $contentType->slug . '/*.md') as $filename) {
                $content = new Content();
                try {
                    $content->load($filename);
                    $content->parse($this->parser, $parse_markdown);
                    $content->setContentType($contentType);
                    $list[] = $content;
                } catch (ContentNotFoundException $e) {
                    continue;
                } catch (Exception $e) {
                }
            }
        }

        $list = $this->orderBy($list, $orderBy);
        $collection = new ContentCollection($list);

        if ($limit === 0) {
            return $collection;
        }

        return $collection->slice($start, $limit);
    }

    /**
     * @throws ContentNotFoundException
     */
    public function fetchTotalPages(int $per_page = 20): int
    {
        $cache = new FileCache($this->cache_path);
        $cache_id = 'full_pagination';

        $cached_content = $cache->getCachedUnlessExpired($cache_id);

        if ($cached_content !== null) {
            return json_decode($cached_content, true);
        }

        $content = $this->fetchAll(0, 0);

        return (int) ceil($content->total() / $per_page);
    }

    /**
     * @throws Exception
     */
    public function fetchTagTotalPages(string $tag, int $per_page = 20): int
    {
        $collection = $this->fetchFromTag($tag);

        return (int) ceil($collection->total() / $per_page);
    }

    /**
     * @return array|mixed
     *
     * @throws ContentNotFoundException
     */
    public function fetchTagList(bool $cached = true): ?array
    {
        if ($cached) {
            $cache = new FileCache($this->cache_path);
            $cache_id = 'full_tag_list';

            $cached_content = $cache->getCachedUnlessExpired($cache_id);

            if ($cached_content !== null) {
                return json_decode($cached_content, true);
            }
        }

        $content = $this->fetchAll(0, 0);
        $tags = [];

        /** @var Content $article */
        foreach ($content as $article) {
            if ($article->frontMatterHas('tags')) {
                $article_tags = explode(',', $article->frontMatterGet('tags'));

                foreach ($article_tags as $article_tag) {
                    $tag_name = trim(str_replace('#', '', $article_tag));

                    $tags[$tag_name][] = $article->getLink();
                }
            }
        }

        if ($cached) {
            $cache->save(json_encode($tags), $cache_id);
        }

        return $tags;
    }

    /**
     * @return mixed|null
     *
     * @throws Exception
     */
    public function fetchFromTag(string $tag, int $start = 0, int $limit = 20): ?ContentCollection
    {
        $full_tag_list = $this->fetchTagList();
        $collection = new ContentCollection();
        if (array_key_exists($tag, $full_tag_list)) {
            foreach ($full_tag_list[$tag] as $route) {
                $article = $this->fetch($route);
                $collection->add($article);
            }

            if (! $limit) {
                return $collection;
            }

            return $collection->slice($start, $limit);
        }

        return null;
    }

    /**
     * @throws ContentNotFoundException
     */
    public function getContentTypes(?string $path = null, string $parent = ''): array
    {
        $contentTypes = [];
        $order = [];
        $data_path = $path ?? $this->data_path;
        foreach (glob($data_path . '/*', GLOB_ONLYDIR) as $route) {
            $content = new ContentType($parent . basename($route), $data_path);
            $contentTypes[$content->slug] = $content;
            $order[$content->slug] = $content->index;

            $content->children = $this->getContentTypes($route, $content->slug . '/');
        }

        asort($order, SORT_NUMERIC);
        $orderedContent = [];
        foreach ($order as $slug => $index) {
            $orderedContent[] = $contentTypes[$slug];
        }

        return $orderedContent;
    }

    /**
     * @throws ContentNotFoundException
     */
    public function getContentType(string $contentType): ContentType
    {
        $content = new ContentType($contentType, $this->data_path);
        $content->children = $this->getContentTypes($content->contentDir . '/' . $content->slug, $content->slug);

        return $content;
    }

    public function fetchFrom(ContentType $contentType, int $start = 0, int $limit = 20, bool $parse_markdown = false, string $orderBy = 'desc'): ?ContentCollection
    {
        $feed = [];

        foreach (glob($this->data_path . '/' . $contentType->slug . '/*.md') as $filename) {
            $content = new Content();
            try {
                $content->load($filename);
                $content->parse($this->parser, $parse_markdown);
                $content->setContentType($contentType);
                $feed[] = $content;
            } catch (ContentNotFoundException $e) {
                continue;
            } catch (Exception $e) {
            }
        }

        $feed = $this->orderBy($feed, $orderBy);
        $collection = new ContentCollection($feed);

        if ($limit === 0) {
            return $collection;
        }

        return $collection->slice($start, $limit);
    }

    public function orderBy(array $content, string $orderBy = 'desc'): array
    {
        uasort($content, fn (Content $content1, Content $content2) => (strtolower($content1->slug) < strtolower($content2->slug)) ? -1 : 1);

        if ($orderBy === 'index') {
            $order = [];
            $contentCollection = [];
            /** @var Content $item */
            foreach ($content as $item) {
                $order[$item->slug] = $item->frontMatterGet('index') ?? 100;
                $contentCollection[$item->slug] = $item;
            }
            asort($order, SORT_NUMERIC);
            $content = [];
            foreach ($order as $slug => $index) {
                $content[] = $contentCollection[$slug];
            }
        }

        if ($orderBy === 'desc') {
            $content = array_reverse($content);
        }

        if ($orderBy === 'rand') {
            shuffle($content);
        }

        return $content;
    }
}
