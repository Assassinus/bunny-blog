<?php

declare(strict_types=1);

namespace App\Entity;

final class ArticlePreview
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly ?string $image,
        public readonly string $title,
        public readonly ?string $description,
        public readonly int $views,
        public readonly \DateTimeImmutable $publishedAt,
    ) {}
}
