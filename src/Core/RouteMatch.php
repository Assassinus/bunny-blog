<?php

declare(strict_types=1);

namespace App\Core;

final class RouteMatch
{
    /** @param array<string, string> $params */
    public function __construct(
        public readonly string $handler,
        public readonly array $params,
    ) {}
}
