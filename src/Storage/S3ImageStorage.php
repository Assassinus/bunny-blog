<?php

declare(strict_types=1);

namespace App\Storage;

final class S3ImageStorage implements ImageStorageInterface
{
    public function getUrl(?string $path): ?string
    {
        return null;
    }
}
