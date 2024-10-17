<?php

declare(strict_types=1);

namespace Librarian;

class Request
{
    /**
     * Parameters from Request
     */
    protected array $params;

    /**
     * The full request string
     */
    protected string $request_uri;

    /**
     * Request information
     */
    protected ?array $request_info;

    /**
     * Full request path
     */
    protected string $path;

    /**
     * The root of the path
     */
    protected string $route;

    /**
     * Parent route, used for content types
     */
    protected string $parent;

    /**
     * Final portion of the request string, when present
     */
    protected string $slug;

    public function __construct(array $params, string $request_uri)
    {
        $this->params = $params;
        $this->request_uri = $request_uri;

        $this->request_info = parse_url($this->request_uri);
        $this->path = $this->request_info['path'];

        $parts = explode('/', $this->path);
        $this->route = $parts[1];
        $this->parent = dirname($this->path);
        $this->slug = str_replace('/' . $this->route . '/', '', $this->path);
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getRequestUri(): string
    {
        return $this->request_uri;
    }

    public function getRequestInfo(): ?array
    {
        return $this->request_info;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getParent(): string
    {
        return $this->parent;
    }
}
