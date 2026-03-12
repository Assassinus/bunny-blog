<?php

declare(strict_types=1);

use App\Controller\ArticleController;
use App\Controller\CategoryController;
use App\Controller\HealthController;
use App\Controller\HomeController;

/** @return list<array{0: string, 1: string, 2: class-string}> */
return [
    ['GET', '/', HomeController::class],
    ['GET', '/health', HealthController::class],
    ['GET', '/category/{slug}', CategoryController::class],
    ['GET', '/article/{slug}', ArticleController::class],
];
