<?php

namespace Librarian\Provider;

use Librarian\Content;
use Librarian\ContentCollection;
use Librarian\Exception\ContentNotFoundException;
use Minicli\App;
use Minicli\ServiceInterface;
use Minicli\Minicache\FileCache;
use Librarian\Request;
use Parsed\ContentParser;
use Parsed\CustomTagParserInterface;

class ContentServiceProvider implements ServiceInterface
{
    /** @var string */
    protected $data_path;

    /** @var string */
    protected $cache_path;

    /** @var array */
    protected $parser_params = [];

    /** @var ContentParser */
    protected $parser;

    /**
     * @param App $app
     * @throws \Exception
     */
    public function load(App $app)
    {
        if (!$app->config->has('data_path')) {
            throw new \Exception("Missing Data Path.");
        }

        if (!$app->config->has('cache_path')) {
            throw new \Exception("Missing Cache Path.");
        }

        $this->data_path = $app->config->data_path;
        $this->cache_path = $app->config->cache_path;

        //optional render parameters for the parsers, should be provided in the apps config
        if ($app->config->has('parser_params')) {
            $this->parser_params = $app->config->parser_params;
        }

        $this->parser = new ContentParser($this->parser_params);
    }

    public function registerTagParser(string $name, CustomTagParserInterface $tag_parser)
    {
        $this->parser->addCustomTagParser($name, $tag_parser);
    }

    /**
     * @param string $route
     * @return Content
     * @throws \Exception
     */
    public function fetch(string $route, $parse_markdown = true)
    {
        $request = new Request([], '/' . $route);
        $filename = $this->data_path . '/' . $request->getRoute() . '/' . $request->getSlug() . '.md';
        $content = new Content();

        try {
            $content->load($filename);
            $content->setRoute($request->getRoute());

            $parser = new ContentParser($this->parser_params);
            $content->parse($parser, $parse_markdown);
        } catch (ContentNotFoundException $e) {
            return null;
        }

        return $content;
    }

    /**
     * @param int $start
     * @param int $limit
     * @return ContentCollection
     */
    public function fetchAll(int $start = 0, int $limit = 20, bool $parse_markdown = false): ContentCollection
    {
        $list = [];
        foreach (glob($this->data_path . '/*') as $route) {
            $content_type = basename($route);
            foreach (glob($route . '/*.md') as $filename) {
                $content = new Content();
                try {
                    $content->load($filename);
                    $content->parse(new ContentParser($this->parser_params), $parse_markdown);
                    $content->setRoute($content_type);
                    $list[] = $content;
                } catch (ContentNotFoundException $e) {
                    continue;
                } catch (\Exception $e) {
                }
            }
        }

        $ordered_content = array_reverse($list);
        if (!$limit) {
            return new ContentCollection($ordered_content);
        }

        return new ContentCollection(array_slice($ordered_content, $start, $limit));
    }

    public function fetchTotalPages($per_page = 20)
    {
        $cache = new FileCache($this->cache_path);
        $cache_id = "full_pagination";

        $cached_content = $cache->getCachedUnlessExpired($cache_id);

        if ($cached_content !== null) {
            return json_decode($cached_content, true);
        }

        $content = $this->fetchAll(0, 0);

        return (int) ceil($content->total() / $per_page);
    }

    /**
     * @return array|mixed
     */
    public function fetchTagList()
    {
        $cache = new FileCache($this->cache_path);
        $cache_id = "full_tag_list";

        $cached_content = $cache->getCachedUnlessExpired($cache_id);

        if ($cached_content !== null) {
            return json_decode($cached_content, true);
        }

        $content = $this->fetchAll(0, 0);
        $tags = [];

        /** @var Content $article */
        foreach ($content as $article) {
            if ($article->frontMatterHas('tag_list')) {
                $article_tags = explode(',', $article->frontMatterGet('tag_list'));

                foreach ($article_tags as $article_tag) {
                    $tag_name = trim(str_replace('#', '', $article_tag));

                    $tags[$tag_name][] = $article->getLink();
                }
            }
        }

        //write to cache file
        $cache->save(json_encode($tags), $cache_id);

        return $tags;
    }

    /**
     * @param $tag
     * @return mixed|null
     */
    public function fetchFromTag($tag)
    {
        $full_tag_list = $this->fetchTagList();
        $collection = new ContentCollection();
        if (key_exists($tag, $full_tag_list)) {
            foreach ($full_tag_list[$tag] as $route) {
                $article = $this->fetch($route);
                $collection->add($article);
            }

            return $collection;
        }

        return null;
    }

    /**
     * @param $route
     * @return ContentCollection
     * @throws ContentNotFoundException
     */
    public function fetchFrom($route)
    {
        $feed = [];

        foreach (glob($this->data_path . '/' . $route . '/*.md') as $filename) {
            $content = new Content();
            $content->load($filename);
            $content->setRoute($route);
            $parser = new ContentParser($this->parser_params);
            $content->parse($parser);
            $feed[] = $content;
        }

        return new ContentCollection(array_reverse($feed));
    }
}
