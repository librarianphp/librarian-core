<?php

namespace Librarian;

use Iterator;

/**
 * Class ContentList
 * Facilitates lazy loading of content items in a Content collection
 * @package Librarian
 */
class ContentCollection implements Iterator
{
    /** @var array */
    protected $content_list = [];

    /** @var int */
    protected $current_position;

    public function __construct($content_list = [])
    {
        $this->current_position = 0;

        foreach ($content_list as $content_item) {
            $this->add($content_item);
        }
    }

    public function add(Content $content)
    {
        $this->content_list[] = $content;
    }

    public function current()
    {
        if (!isset($this->content_list[$this->current_position])) {
            return null;
        }

        return $this->content_list[$this->current_position];
    }

    public function next()
    {
        ++$this->current_position;
    }

    public function key()
    {
        return $this->current_position;
    }

    public function valid()
    {
        return isset($this->content_list[$this->current_position]);
    }

    public function rewind()
    {
        $this->current_position = 0;
    }

    public function total()
    {
        return count($this->content_list);
    }

    /**
     * Returns a new collection with a subset of this collection's items
     * @param $start
     * @param $limit
     * @return ContentCollection
     */
    public function slice($start, $limit): ContentCollection
    {
        return new ContentCollection(array_slice($this->content_list, $start, $limit));
    }
}
