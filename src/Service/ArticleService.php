<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\ArticlePreview;
use App\Repository\ArticleRepository;
use App\Utils\Paginator;
use Exception;

final class ArticleService
{
    private const CACHE_TTL = 300;

    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly CacheService $cache,
    ) {}

    /**
     * @param array<int, true> $viewedIds
     * @throws Exception
     */
    public function getArticleBySlug(string $slug, array $viewedIds): ?Article
    {
        $article = $this->articleRepository->findBySlug($slug);
        if ($article === null) {
            return null;
        }

        if (!isset($viewedIds[$article->id])) {
            $this->articleRepository->incrementViews($article->id);
            return $article->withIncrementedViews();
        }

        return $article;
    }

    /** @return list<ArticlePreview> */
    public function findSimilarArticles(Article $article, int $limit = 3): array
    {
        return $this->cache->remember(
            'similar_' . $article->id . '_' . $limit,
            function () use ($article, $limit): array {
                $categoryIds = array_map(static fn($c) => $c->id, $article->categories);
                return $this->articleRepository->findSimilar($article->id, $categoryIds, $limit);
            },
            self::CACHE_TTL
        );
    }

    /** @return array{items: list<ArticlePreview>, paginator: Paginator} */
    public function getArticlesByCategoryPaginated(
        int $categoryId,
        int $page,
        int $perPage,
        string $sort = 'date',
    ): array {
        $cacheKey = "cat_{$categoryId}_p{$page}_s{$sort}";

        return $this->cache->remember($cacheKey, function () use ($categoryId, $page, $perPage, $sort): array {
            $total = $this->articleRepository->countByCategory($categoryId);
            $paginator = Paginator::create($page, $perPage, $total);
            $items = $this->articleRepository->findPaginatedByCategory($categoryId, $paginator, $sort);
            return [
                'items' => $items,
                'paginator' => $paginator,
            ];
        }, self::CACHE_TTL);
    }
}
