<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\CategoryWithArticles;
use App\Repository\CategoryRepository;
use Exception;

final class CategoryService
{
    private const CACHE_TTL = 300;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CacheService $cache,
    ) {}

    public function getBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    /**
     * @return list<CategoryWithArticles>
     * @throws Exception
     */
    public function getCategoriesWithLatestArticles(int $perCategory = 3): array
    {
        return $this->cache->remember(
            'home_categories_' . $perCategory,
            fn() => $this->categoryRepository->findCategoriesWithLatestArticles($perCategory),
            self::CACHE_TTL
        );
    }
}
