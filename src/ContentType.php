<?php

declare(strict_types=1);

namespace Librarian;

use Parsed\ContentParser;

/**
 * A ContentType is a node with either children nodes or content files
 */
class ContentType
{
    public string $slug;

    public string $contentDir;

    public string $title;

    public string $description = '';

    public int $index = 100;

    public array $children = [];

    /**
     * @throws Exception\ContentNotFoundException
     */
    public function __construct(string $slug, string $contentDir)
    {
        $this->slug = $slug;
        $this->title = ucfirst($slug);
        $this->contentDir = $contentDir;
        $this->loadMetadata();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @throws Exception\ContentNotFoundException
     */
    public function loadMetadata(): void
    {
        if (is_file($this->contentDir . '/' . $this->slug . '/_index')) {
            $metadata = new Content();
            $metadata->load($this->contentDir . '/' . $this->slug . '/_index');
            $metadata->parse(new ContentParser());
            $this->title = $metadata->frontMatterGet('title') ?? ucfirst($this->slug);
            $this->description = $metadata->frontMatterGet('description') ?? '';
            $this->index = (int) $metadata->frontMatterGet('index') ?? 10;
        }
    }
}
