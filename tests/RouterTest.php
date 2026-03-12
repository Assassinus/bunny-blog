<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testMatchRoot(): void
    {
        $router = new Router();
        $router->get('/', 'HomeController');

        $match = $router->match('GET', '/');
        $this->assertNotNull($match);
        $this->assertSame('HomeController', $match->handler);
        $this->assertSame([], $match->params);
    }

    public function testMatchWithSlug(): void
    {
        $router = new Router();
        $router->get('/article/{slug}', 'ArticleController');

        $match = $router->match('GET', '/article/hello-world');
        $this->assertNotNull($match);
        $this->assertSame('ArticleController', $match->handler);
        $this->assertSame(['slug' => 'hello-world'], $match->params);
    }

    public function testNoMatch(): void
    {
        $router = new Router();
        $router->get('/', 'HomeController');

        $this->assertNull($router->match('GET', '/unknown'));
    }

    public function testMethodNotAllowed(): void
    {
        $router = new Router();
        $router->get('/', 'HomeController');

        $this->assertNull($router->match('POST', '/'));
    }

    public function testQueryStringIgnored(): void
    {
        $router = new Router();
        $router->get('/category/{slug}', 'CategoryController');

        $match = $router->match('GET', '/category/php?page=2&sort=views');
        $this->assertNotNull($match);
        $this->assertSame(['slug' => 'php'], $match->params);
    }
}
