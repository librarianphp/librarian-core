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
    protected array $content_list = [];

    protected int $current_position;

    public function __construct(array $content_list = [])
    {
        $this->current_position = 0;

        foreach ($content_list as $content_item) {
            $this->add($content_item);
        }
    }

    public function add(Content $content): void
    {
        $this->content_list[] = $content;
    }

    public function current(): mixed
    {
        if (!isset($this->content_list[$this->current_position])) {
            return null;
        }

        return $this->content_list[$this->current_position];
    }

    public function next(): void
    {
        ++$this->current_position;
    }

    public function key(): mixed
    {
        return $this->current_position;
    }

    public function valid(): bool
    {
        return isset($this->content_list[$this->current_position]);
    }

    public function rewind(): void
    {
        $this->current_position = 0;
    }

    public function total(): int
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
