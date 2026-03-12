<?php

declare(strict_types=1);

namespace Tests;

use App\Utils\Slug;
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    public function testLatinString(): void
    {
        $this->assertSame('hello-world', Slug::fromString('Hello World'));
    }

    public function testCyrillicTransliteration(): void
    {
        $this->assertSame('porody', Slug::fromString('Породы'));
        $this->assertSame('povedenie', Slug::fromString('Поведение'));
        $this->assertSame('zdorove', Slug::fromString('Здоровье'));
    }

    public function testMixedCyrillicLatin(): void
    {
        $this->assertSame('php', Slug::fromString('PHP'));
        $this->assertSame('dom-i-byt', Slug::fromString('Дом и быт'));
    }

    public function testSpecialCharsRemoved(): void
    {
        $this->assertSame('foo-bar', Slug::fromString('  foo--bar!!  '));
    }

    public function testEmptyFallback(): void
    {
        $this->assertSame('slug', Slug::fromString(''));
    }

    public function testNumericSlug(): void
    {
        $this->assertSame('123', Slug::fromString('123'));
    }

    public function testYoTransliteration(): void
    {
        $this->assertSame('yozh', Slug::fromString('Ёж'));
    }

    public function testValidateAcceptsValidSlug(): void
    {
        $this->assertSame('hello-world', Slug::validate('hello-world'));
        $this->assertSame('article-123', Slug::validate('article-123'));
    }

    public function testValidateRejectsEmpty(): void
    {
        $this->assertNull(Slug::validate(''));
        $this->assertNull(Slug::validate('   '));
    }

    public function testValidateRejectsInvalidChars(): void
    {
        $this->assertNull(Slug::validate('hello world'));
        $this->assertNull(Slug::validate('Uppercase'));
        $this->assertNull(Slug::validate('привет'));
        $this->assertNull(Slug::validate('foo_bar'));
    }

    public function testValidateRejectsTooLong(): void
    {
        $this->assertNull(Slug::validate(str_repeat('a', 256)));
    }
}
