<?php

declare(strict_types=1);

namespace App\Controller;

interface ControllerInterface
{
    /** @param array<string, mixed> $params */
    public function __invoke(array $params): string;
}
