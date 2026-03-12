<?php

declare(strict_types=1);

namespace App\Entity;

final class CategoryWithArticles
{
    /**
     * @param list<ArticlePreview> $articles
     */
    public function __construct(
        public readonly Category $category,
        public readonly array $articles,
    ) {}
}
