<?php

declare(strict_types=1);

use App\Controller\ControllerInterface;
use App\Controller\HealthController;
use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

/** @var Container $container */
$container = require dirname(__DIR__) . '/bootstrap.php';

$config = $container->getConfig();
$smarty = $container->getSmarty();
$smarty->assign('base_url', rtrim($config['app']['base_url'], '/'));

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;");

$request = Request::fromGlobals();
$router = new Router();
$routes = require dirname(__DIR__) . '/config/routes.php';
foreach ($routes as [$method, $path, $handler]) {
    if ($method === 'GET') {
        $router->get($path, $handler);
    }
}

try {
    $match = $router->match($request->method, $request->path);

    if ($match === null) {
        $smarty->assign('error_code', 404);
        $smarty->assign('message', 'Страница не найдена');
        echo Response::notFound($smarty->fetch('error.tpl'));
        exit;
    }

    $controller = $container->resolve($match->handler);
    $params = $match->params;
    $params['_query'] = $request->query;
    /** @var ControllerInterface $controller */
    $html = $controller($params);

    if ($match->handler === HealthController::class) {
        echo Response::html($html);
        exit;
    }

    $etag = '"' . md5($html) . '"';
    header('Cache-Control: public, max-age=60, stale-while-revalidate=300');
    header('ETag: ' . $etag);

    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
        header('HTTP/1.1 304 Not Modified');
        exit;
    }

    echo Response::html($html);
} catch (Throwable $e) {
    $container->getLogger()->error('Error: ' . $e->getMessage(), ['exception' => $e]);
    $smarty->assign('error_code', 500);
    $smarty->assign('message', ($config['app']['debug'] ?? false)
        ? $e->getMessage()
        : 'Внутренняя ошибка сервера.');
    echo Response::serverError($smarty->fetch('error.tpl'));
    exit;
}
