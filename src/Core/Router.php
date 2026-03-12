<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, string>> */
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * @param string $method
     * @param string $uri
     * @return RouteMatch|null
     */
    public function match(string $method, string $uri): ?RouteMatch
    {
        $uri = $this->normalizeUri($uri);
        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $path => $handler) {
            $params = $this->matchPath($path, $uri);
            if ($params !== null) {
                return new RouteMatch($handler, $params);
            }
        }
        return null;
    }

    private function normalizeUri(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $uri = $path !== null && $path !== '' ? $path : $uri;
        $uri = '/' . trim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    /** @return array<string, string>|null */
    private function matchPath(string $path, string $uri): ?array
    {
        $pattern = preg_replace_callback(
            '/\{(\w+)(?::([^}]+))?}/',
            static function (array $m): string {
                $regex = $m[2] ?? '[^/]+';
                return '(?P<' . $m[1] . '>' . $regex . ')';
            },
            $path
        );
        $pattern = '#^' . $pattern . '$#';
        if (!preg_match($pattern, $uri, $m)) {
            return null;
        }

        /** @var array<string, string> $params */
        $params = array_filter($m, '\is_string', ARRAY_FILTER_USE_KEY);
        return $params;
    }
}
