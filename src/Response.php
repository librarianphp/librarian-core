<?php

namespace Librarian;

class Response
{
    protected $content;

    public function __construct($content = null)
    {
        $this->content = $content;
    }

    public function output()
    {
        echo $this->content;
    }

    /**
     * @return null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param null $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    public static function redirect($url, $statusCode = 303)
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    public static function notfound()
    {
        header("HTTP/1.0 404 Not Found");
        exit;
    }
}
