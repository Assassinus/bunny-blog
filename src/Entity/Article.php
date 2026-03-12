<?php

declare(strict_types=1);

namespace App\Entity;

final class Article
{
    /**
     * @param list<Category> $categories
     */
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly ?string $image,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $body,
        public readonly int $views,
        public readonly \DateTimeImmutable $publishedAt,
        public readonly ?\DateTimeImmutable $createdAt,
        public readonly array $categories,
    ) {}

    public function withIncrementedViews(): self
    {
        return new self(
            $this->id,
            $this->slug,
            $this->image,
            $this->title,
            $this->description,
            $this->body,
            $this->views + 1,
            $this->publishedAt,
            $this->createdAt,
            $this->categories,
        );
    }
}
