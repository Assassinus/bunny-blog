<?php

declare(strict_types=1);

/** @return array<string, mixed> */
return [
    'db' => [
        'host'     => getenv('DB_HOST') ?: 'mysql',
        'port'     => (int) (getenv('DB_PORT') ?: '3306'),
        'dbname'   => getenv('DB_NAME') ?: 'bunny_blog',
        'charset'  => 'utf8mb4',
        'user'     => getenv('DB_USER') ?: 'blog',
        'password' => getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: 'blog_secret',
    ],
    'app' => [
        'base_url' => getenv('BASE_URL') ?: '',
        'per_page' => (int) (getenv('PER_PAGE') ?: '10'),
        'debug' => (bool) (getenv('APP_DEBUG') ?: false),
    ],
];
