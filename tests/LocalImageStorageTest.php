<?php

declare(strict_types=1);

namespace Tests;

use App\Storage\LocalImageStorage;
use PHPUnit\Framework\TestCase;

final class LocalImageStorageTest extends TestCase
{
    private LocalImageStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new LocalImageStorage('http://localhost:8080');
    }

    public function testNullPath(): void
    {
        $this->assertNull($this->storage->getUrl(null));
    }

    public function testEmptyPath(): void
    {
        $this->assertNull($this->storage->getUrl(''));
        $this->assertNull($this->storage->getUrl('  '));
    }

    public function testRelativePath(): void
    {
        $this->assertSame(
            'http://localhost:8080/uploads/images/sample.jpg',
            $this->storage->getUrl('images/sample.jpg')
        );
    }

    public function testLeadingSlashStripped(): void
    {
        $this->assertSame(
            'http://localhost:8080/uploads/article/img.png',
            $this->storage->getUrl('/article/img.png')
        );
    }

    public function testFullUrlPassedThrough(): void
    {
        $url = 'https://cdn.example.com/images/photo.jpg';
        $this->assertSame($url, $this->storage->getUrl($url));
    }

    public function testTrailingSlashOnBaseUrl(): void
    {
        $storage = new LocalImageStorage('http://example.com/');
        $this->assertSame(
            'http://example.com/uploads/test.jpg',
            $storage->getUrl('test.jpg')
        );
    }
}
