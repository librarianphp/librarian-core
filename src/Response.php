<?php

declare(strict_types=1);

namespace Librarian;

class Response
{
    protected ?string $content;

    public function __construct(?string $content = null)
    {
        $this->content = $content;
    }

    public static function redirect($url, $statusCode = 303): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    public static function notfound(): void
    {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    public function output(): void
    {
        echo $this->content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param  null  $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }
}
