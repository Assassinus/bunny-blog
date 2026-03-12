<?php

declare(strict_types=1);

namespace App\Service;

final class CacheService
{
    private const DEFAULT_TTL = 300;

    public function __construct(
        private readonly string $cacheDir,
        private readonly int $defaultTtl = self::DEFAULT_TTL,
    ) {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key): mixed
    {
        $path = $this->path($key);
        if (!is_file($path)) {
            return null;
        }

        $data = @file_get_contents($path);
        if ($data === false) {
            return null;
        }

        $payload = @unserialize($data);
        if (!$payload instanceof CacheEntry) {
            return null;
        }

        if ($payload->expiresAt < time()) {
            @unlink($path);
            return null;
        }

        return $payload->value;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $path = $this->path($key);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, serialize(new CacheEntry($value, time() + $ttl)), LOCK_EX);
    }

    /** @param callable(): mixed $factory */
    public function remember(string $key, callable $factory, ?int $ttl = null): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }
        $value = $factory();
        $this->set($key, $value, $ttl);
        return $value;
    }

    private function path(string $key): string
    {
        $hash = md5($key);
        return $this->cacheDir . '/' . substr($hash, 0, 2) . '/' . $hash;
    }
}
