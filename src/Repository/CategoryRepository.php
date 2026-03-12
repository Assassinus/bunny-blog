<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArticlePreview;
use App\Entity\Category;
use App\Entity\CategoryWithArticles;
use App\Storage\ImageStorageInterface;
use Exception;
use PDO;

final class CategoryRepository
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ImageStorageInterface $imageStorage,
    ) {}

    /**
     * @return list<CategoryWithArticles>
     * @throws Exception
     */
    public function findCategoriesWithLatestArticles(int $perCategory = 3): array
    {
        $stmt = $this->pdo->query(
            'SELECT DISTINCT c.id, c.slug, c.name, c.description
             FROM categories c
             INNER JOIN article_categories ac ON ac.category_id = c.id
             ORDER BY c.name'
        );
        $categoryRows = $stmt->fetchAll();

        if ($categoryRows === []) {
            return [];
        }

        $categoryIds = array_map(static fn(array $r) => (int) $r['id'], $categoryRows);
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        $stmt = $this->pdo->prepare(
            "WITH ranked AS (
                SELECT a.id, a.slug, a.image, a.title, a.description, a.views, a.published_at,
                       ac.category_id,
                       ROW_NUMBER() OVER (PARTITION BY ac.category_id ORDER BY a.published_at DESC) AS rn
                FROM articles a
                INNER JOIN article_categories ac ON ac.article_id = a.id
                WHERE ac.category_id IN ({$placeholders})
            )
            SELECT id, slug, image, title, description, views, published_at, category_id
            FROM ranked WHERE rn <= ?"
        );

        $pos = 1;
        foreach ($categoryIds as $id) {
            $stmt->bindValue($pos++, $id, PDO::PARAM_INT);
        }
        $stmt->bindValue($pos, $perCategory, PDO::PARAM_INT);
        $stmt->execute();

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['category_id']][] = $this->hydratePreview($row);
        }

        return array_map(
            fn(array $cat) => new CategoryWithArticles(
                $this->hydrateCategory($cat),
                $grouped[(int) $cat['id']] ?? [],
            ),
            $categoryRows
        );
    }

    public function findBySlug(string $slug): ?Category
    {
        $stmt = $this->pdo->prepare('SELECT id, slug, name, description FROM categories WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? $this->hydrateCategory($row) : null;
    }

    private function hydrateCategory(array $row): Category
    {
        return new Category(
            (int) $row['id'],
            (string) $row['slug'],
            (string) $row['name'],
            $row['description'] ?? null,
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
