<?php

declare(strict_types=1);

namespace Librarian;

use DateTime;
use Exception;
use Librarian\Exception\ContentNotFoundException;
use Parsed\Content as Parsed;

/**
 * Defines the Content Model
 */
class Content extends Parsed
{
    /** @var string Path to content static file */
    public string $path;

    /** @var string Content Slug */
    public string $slug;

    /** @var string Route */
    public string $route;

    /** @var ContentType Content Type */
    public ContentType $contentType;

    /** @var string Link to this content */
    public string $link;

    /** @var ?string default title based on file name */
    public ?string $default_title;

    /**
     * Sets content type / route
     */
    public function setContentType(ContentType $contentType): void
    {
        $this->contentType = $contentType;
        $this->route = $contentType->slug;
    }

    public function getLink(): string
    {
        return $this->route . '/' . $this->slug;
    }

    /**
     * @throws ContentNotFoundException
     */
    public function load(string $file): void
    {
        $this->path = $file;
        if (! file_exists($this->path)) {
            throw new ContentNotFoundException('Content not found.');
        }

        $this->raw = file_get_contents($this->path);
        $this->slug = $this->getSlug();
        $this->default_title = $this->getAlternateTitle();
    }

    /**
     * @param  ?string  $path
     */
    public function save(?string $path = null): void
    {
        if (! $path) {
            $path = $this->path;
        }

        $file = fopen($path, 'w+');
        fwrite($file, $this->raw);
        fclose($file);
    }

    /**
     * @throws Exception
     */
    public function getDate(): string
    {
        $slug = $this->getSlug();
        $parts = explode('_', $slug, 2);

        try {
            $date = new DateTime($parts[0]);
        } catch (Exception $e) {
            $date = new DateTime();
        }

        return $date->format('F d, Y');
    }

    /**
     * @return ?string
     */
    public function getAlternateTitle(): ?string
    {
        $slug = $this->getSlug();

        //remove date
        $parts = explode('_', $slug, 2);

        $title = $parts[1] ?? $slug;

        return ucfirst(str_replace('-', ' ', $title));
    }

    public function getSlug(): string
    {
        return str_replace('.md', '', basename($this->path));
    }
}
