<?php

declare(strict_types=1);

namespace App\Controller;

final class HealthController implements ControllerInterface
{
    /** @param array<string, mixed> $params */
    public function __invoke(array $params = []): string
    {
        return 'OK';
    }
}
