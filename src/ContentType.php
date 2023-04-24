<?php

namespace Librarian;

use Parsed\ContentParser;

class ContentType
{
    public string $slug;
    public string $contentDir;
    public string $title;
    public string $description;
    public string $index;

    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @throws Exception\ContentNotFoundException
     */
    public function __construct(string $slug, string $contentDir)
    {
        $this->slug = $slug;
        $this->contentDir = $contentDir;
        $this->loadMetadata();
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
            $this->description = $metadata->frontMatterGet('description') ?? "";
            $this->index = $metadata->frontMatterGet('index') ?? 10;
        }
    }
}