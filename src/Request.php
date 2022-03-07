<?php

namespace Librarian;

class Request
{
    protected array $params;

    protected string $request_uri;

    protected ?array $request_info;

    protected string $path;

    /**
     * @var string Requested route, such as "home", "index", "blog", etc
     * only 1 level is supported
     */
    protected string $route;

    /**
     * @var string Slug if present (request path minus route)
     */
    protected string $slug;

    public function __construct(array $params, string $request_uri)
    {
        $this->params = $params;
        $this->request_uri = $request_uri;

        $this->request_info = parse_url($this->request_uri);
        $this->path = $this->request_info['path'];

        //make sure to get the first part only
        $parts = explode('/', $this->path);

        $this->route = $parts[1];
        $this->slug = str_replace('/' . $this->route . '/', '', $this->path);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->request_uri;
    }

    /**
     * @return array
     */
    public function getRequestInfo(): ?array
    {
        return $this->request_info;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
}
