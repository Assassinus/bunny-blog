<?php

declare(strict_types=1);

namespace App\Service;

/** @internal */
final class CacheEntry
{
    public function __construct(
        public readonly mixed $value,
        public readonly int $expiresAt,
    ) {}
}
