<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $body): string
    {
        header('HTTP/1.1 200 OK');
        header('Content-Type: text/html; charset=UTF-8');
        return $body;
    }

    public static function notFound(string $body): string
    {
        header('HTTP/1.1 404 Not Found');
        header('Cache-Control: no-cache');
        header('Content-Type: text/html; charset=UTF-8');
        return $body;
    }

    public static function serverError(string $body): string
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Cache-Control: no-store');
        header('Content-Type: text/html; charset=UTF-8');
        return $body;
    }
}
