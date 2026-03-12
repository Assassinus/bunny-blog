<?php

declare(strict_types=1);

namespace App\Storage;

interface ImageStorageInterface
{
    /** @param string|null $path */
    public function getUrl(?string $path): ?string;
}
