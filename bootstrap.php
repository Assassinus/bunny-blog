<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$projectRoot = __DIR__;

if (file_exists($projectRoot . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable($projectRoot);
    $dotenv->safeLoad();
}

$config = require $projectRoot . '/config/config.php';
$container = new \App\Core\Container($projectRoot, $config);

return $container;
