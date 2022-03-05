<?php

namespace Librarian;

use Librarian\Exception\ContentNotFoundException;
use Parsed\Content as Parsed;
use DateTime;

/**
 * Defines the Content Model
 * @package Miniweb
 */
class Content extends Parsed
{
    /** @var string Path to content static file */
    public $path;

    /** @var string Content Slug */
    public $slug;

    /** @var string Route for this Content */
    public $route;

    /** @var string Link to this content */
    public $link;

    /** @var string default title based on file name */
    public $default_title;

    /**
     * Sets content type / route
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->route . '/' . $this->slug;
    }

    /**
     * @param string $file
     * @throws ContentNotFoundException
     */
    public function load(string $file)
    {
        $this->path = $file;
        if (!file_exists($this->path)) {
            throw new ContentNotFoundException('Content not found.');
        }

        $this->raw = file_get_contents($this->path);
        $this->slug = $this->getSlug();
        $this->default_title = $this->getAlternateTitle();
    }

    /**
     * @param string $path
     */
    public function save(string $path = null)
    {
        if (!$path) {
            $path = $this->path;
        }

        $file = fopen($path, "w+");
        fputs($file, $this->raw);
        fclose($file);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDate(): string
    {
        $slug = $this->getSlug();
        $parts = explode('_', $slug, 2);

        try {
            $date = new DateTime($parts[0]);
        } catch (\Exception $e) {
            $date = new DateTime();
        }

        return $date->format('F d, Y');
    }

    /**
     * @return mixed|string|string[]
     */
    public function getAlternateTitle()
    {
        $slug = $this->getSlug();

        //remove date
        $parts = explode('_', $slug, 2);

        $title = isset($parts[1]) ? $parts[1] : $slug;

        $title = ucfirst(str_replace('-', ' ', $title));

        return $title;
    }

    /**
     * @return string|string[]
     */
    public function getSlug()
    {
        return str_replace('.md', '', basename($this->path));
    }
}
