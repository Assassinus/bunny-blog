<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\ArticlePreview;
use App\Entity\Category;
use App\Storage\ImageStorageInterface;
use App\Utils\Paginator;
use Exception;
use PDO;

final class ArticleRepository
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ImageStorageInterface $imageStorage,
    ) {}

    /**
     * @throws Exception
     */
    public function findBySlug(string $slug): ?Article
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, slug, image, title, description, body, views, published_at, created_at
             FROM articles WHERE slug = :slug'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrateArticle($row, $this->findCategoriesByArticleId((int) $row['id']));
    }

    public function incrementViews(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE articles SET views = views + 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** @return list<ArticlePreview> */
    public function findSimilar(int $articleId, array $categoryIds, int $limit = 3): array
    {
        if (empty($categoryIds)) {
            return $this->findLatest($limit, $articleId);
        }

        $limit = max(1, min(100, $limit));
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $sql = "SELECT a.id, a.slug, a.image, a.title, a.description, a.views, a.published_at,
                       COUNT(ac.category_id) AS relevance
                FROM articles a
                INNER JOIN article_categories ac ON ac.article_id = a.id
                WHERE ac.category_id IN ({$placeholders}) AND a.id != ?
                GROUP BY a.id, a.slug, a.image, a.title, a.description, a.views, a.published_at
                ORDER BY relevance DESC, a.published_at DESC
                LIMIT " . $limit;

        $stmt = $this->pdo->prepare($sql);
        $pos = 1;
        foreach ($categoryIds as $cid) {
            $stmt->bindValue($pos++, $cid, PDO::PARAM_INT);
        }
        $stmt->bindValue($pos, $articleId, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'hydratePreview'], $stmt->fetchAll());
    }

    /** @return list<ArticlePreview> */
    public function findLatest(int $limit, ?int $excludeId = null): array
    {
        $limit = max(1, min(100, $limit));
        $sql = 'SELECT id, slug, image, title, description, views, published_at FROM articles';
        $params = [];

        if ($excludeId !== null) {
            $sql .= ' WHERE id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' ORDER BY published_at DESC LIMIT ' . $limit;
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmt->execute();

        return array_map([$this, 'hydratePreview'], $stmt->fetchAll());
    }

    public function countByCategory(int $categoryId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(DISTINCT a.id)
             FROM articles a
             INNER JOIN article_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = :category_id'
        );
        $stmt->execute(['category_id' => $categoryId]);
        return (int) $stmt->fetchColumn();
    }

    /** @return list<ArticlePreview> */
    public function findPaginatedByCategory(int $categoryId, Paginator $paginator, string $sort = 'date'): array
    {
        $orderBy = $sort === 'views' ? 'a.views DESC, a.published_at DESC' : 'a.published_at DESC';
        $offset = $paginator->getOffset();
        $limit = $paginator->getLimit();

        $stmt = $this->pdo->prepare(
            "SELECT a.id, a.slug, a.image, a.title, a.description, a.views, a.published_at
             FROM articles a
             INNER JOIN article_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = :category_id
             ORDER BY {$orderBy}
             LIMIT " . max(1, min(100, $limit)) . ' OFFSET ' . max(0, $offset)
        );
        $stmt->bindValue('category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'hydratePreview'], $stmt->fetchAll());
    }

    /** @return list<Category> */
    private function findCategoriesByArticleId(int $articleId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.slug, c.name, c.description
             FROM categories c
             INNER JOIN article_categories ac ON ac.category_id = c.id
             WHERE ac.article_id = :article_id'
        );
        $stmt->execute(['article_id' => $articleId]);

        return array_map(
            static fn(array $r) => new Category(
                (int) $r['id'],
                (string) $r['slug'],
                (string) $r['name'],
                $r['description'] !== null ? (string) $r['description'] : null,
            ),
            $stmt->fetchAll()
        );
    }

    /**
     * @throws Exception
     */
    private function hydrateArticle(array $row, array $categories): Article
    {
        return new Article(
            (int) $row['id'],
            (string) $row['slug'],
            $this->imageStorage->getUrl($row['image'] ?? null),
            (string) $row['title'],
            $row['description'] ?? null,
            $row['body'] ?? null,
            (int) $row['views'],
            self::parseDateTime($row['published_at']),
            isset($row['created_at']) ? self::parseDateTime($row['created_at']) : null,
            $categories,
        );
    }

    /**
     * @throws Exception
     */
    private function hydratePreview(array $row): ArticlePreview
    {
        return new ArticlePreview(
            (int) $row['id'],
            (string) $row['slug'],
            $this->imageStorage->getUrl($row['image'] ?? null),
            (string) $row['title'],
            $row['description'] ?? null,
            (int) ($row['views'] ?? 0),
            self::parseDateTime($row['published_at']),
        );
    }

    /**
     * @throws Exception
     */
    private static function parseDateTime(string $value): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value)
            ?: new \DateTimeImmutable($value);
    }
}
