<?php

declare(strict_types=1);

namespace App\Storage;

final class LocalImageStorage implements ImageStorageInterface
{
    private readonly string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = ltrim(trim($path), '/');

        if (str_contains($path, '://')) {
            return $path;
        }

        return $this->baseUrl . '/uploads/' . $path;
    }
}
