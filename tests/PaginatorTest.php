<?php

declare(strict_types=1);

namespace Tests;

use App\Utils\Paginator;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    public function testSinglePage(): void
    {
        $p = Paginator::create(1, 10, 5);
        $this->assertSame(1, $p->currentPage);
        $this->assertSame(5, $p->totalItems);
        $this->assertSame(1, $p->getTotalPages());
        $this->assertFalse($p->hasPrev());
        $this->assertFalse($p->hasNext());
    }

    public function testMultiplePages(): void
    {
        $p = Paginator::create(2, 10, 25);
        $this->assertSame(2, $p->currentPage);
        $this->assertSame(3, $p->getTotalPages());
        $this->assertTrue($p->hasPrev());
        $this->assertTrue($p->hasNext());
    }

    public function testLastPage(): void
    {
        $p = Paginator::create(3, 10, 25);
        $this->assertTrue($p->hasPrev());
        $this->assertFalse($p->hasNext());
    }

    public function testPageClampedToMax(): void
    {
        $p = Paginator::create(99, 10, 25);
        $this->assertSame(3, $p->currentPage);
    }

    public function testZeroItems(): void
    {
        $p = Paginator::create(1, 10, 0);
        $this->assertSame(1, $p->getTotalPages());
        $this->assertFalse($p->hasPrev());
        $this->assertFalse($p->hasNext());
    }

    public function testGetPagesSmall(): void
    {
        $p = Paginator::create(2, 10, 30);
        $this->assertSame([1, 2, 3], $p->getPages());
    }

    public function testGetPagesWithEllipsis(): void
    {
        $p = Paginator::create(5, 10, 100);
        $this->assertSame([1, null, 4, 5, 6, null, 10], $p->getPages());
    }

    public function testGetPagesFirstPage(): void
    {
        $p = Paginator::create(1, 10, 100);
        $this->assertSame([1, 2, null, 10], $p->getPages());
    }

    public function testGetPagesLastPage(): void
    {
        $p = Paginator::create(10, 10, 100);
        $this->assertSame([1, null, 9, 10], $p->getPages());
    }
}
