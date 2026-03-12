<?php

declare(strict_types=1);

namespace Tests\Database;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Storage\ImageStorageInterface;
use PDO;
use PHPUnit\Framework\TestCase;

final class CategoryRepositoryTest extends TestCase
{
    private PDO $pdo;
    private CategoryRepository $repo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createSchema();
        $this->repo = new CategoryRepository($this->pdo, $this->stubImageStorage());
    }

    private function createSchema(): void
    {
        $this->pdo->exec(<<<'SQL'
            CREATE TABLE categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                description TEXT
            );
            CREATE TABLE articles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT NOT NULL UNIQUE,
                image TEXT,
                title TEXT NOT NULL,
                description TEXT,
                body TEXT NOT NULL,
                views INTEGER NOT NULL DEFAULT 0,
                published_at TEXT NOT NULL,
                created_at TEXT NOT NULL
            );
            CREATE TABLE article_categories (
                article_id INTEGER NOT NULL,
                category_id INTEGER NOT NULL,
                PRIMARY KEY (article_id, category_id)
            );
SQL
        );
    }

    private function stubImageStorage(): ImageStorageInterface
    {
        return new class implements ImageStorageInterface {
            public function getUrl(?string $path): ?string
            {
                return $path !== null && $path !== '' ? 'https://example.com/' . $path : null;
            }
        };
    }

    public function testFindBySlugReturnsNullWhenNotFound(): void
    {
        self::assertNull($this->repo->findBySlug('missing'));
    }

    public function testFindBySlugReturnsCategoryWhenExists(): void
    {
        $this->pdo->exec("INSERT INTO categories (slug, name, description) VALUES ('php', 'PHP', 'Desc')");
        $cat = $this->repo->findBySlug('php');
        self::assertNotNull($cat);
        self::assertInstanceOf(Category::class, $cat);
        self::assertSame('php', $cat->slug);
        self::assertSame('PHP', $cat->name);
        self::assertSame('Desc', $cat->description);
    }

    public function testFindCategoriesWithLatestArticlesReturnsEmptyWhenNoData(): void
    {
        self::assertSame([], $this->repo->findCategoriesWithLatestArticles(3));
    }

    public function testFindCategoriesWithLatestArticlesReturnsCategoriesWithArticles(): void
    {
        $this->pdo->exec("INSERT INTO categories (slug, name) VALUES ('cat', 'Cat')");
        $catId = (int) $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO articles (slug, title, body, views, published_at, created_at) VALUES ('a1', 'A1', 'B', 0, '2024-01-01 12:00:00', '2024-01-01 12:00:00')");
        $aid = (int) $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO article_categories (article_id, category_id) VALUES ($aid, $catId)");

        $result = $this->repo->findCategoriesWithLatestArticles(3);
        self::assertCount(1, $result);
        self::assertSame('cat', $result[0]->category->slug);
        self::assertCount(1, $result[0]->articles);
        self::assertSame('a1', $result[0]->articles[0]->slug);
    }
}
