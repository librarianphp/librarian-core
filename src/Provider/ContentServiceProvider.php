<?php

namespace Librarian\Provider;

use Librarian\Content;
use Librarian\ContentCollection;
use Librarian\Exception\ContentNotFoundException;
use Minicli\App;
use Minicli\ServiceInterface;
use Minicli\Minicache\FileCache;
use Minicli\Miniweb\Request;
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
     * @param Request $request
     * @return Content
     * @throws \Exception
     */
    public function fetch(Request $request)
    {
        $filename = $this->data_path . '/' . $request->getRoute() . '/' . $request->getSlug() . '.md';
        $content = new Content();

        try {
            $content->load($filename);
            $content->setRoute($request->getRoute());

            $parser = new ContentParser($this->parser_params);
            $content->parse($parser);

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
    public function fetchAll($start = 0, $limit = 20): ContentCollection
    {
        $list = [];
        echo $this->data_path;
        foreach (glob($this->data_path . '/*') as $route) {
            $content_type = basename($route);
            foreach (glob($route . '/*.md') as $filename) {
                $content = new Content();
                try {
                    $content->load($filename);
                    $content->parse(new ContentParser($this->parser_params));
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
            if ($article->tag_list) {
                $article_tags = explode(',', $article->tag_list);

                foreach ($article_tags as $article_tag) {
                    $tag_name = trim(str_replace('#', '', $article_tag));

                    $tags[$tag_name][] = $article;
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

        if (key_exists($tag, $full_tag_list)) {
            return $full_tag_list[$tag];
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