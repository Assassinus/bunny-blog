<?php

declare(strict_types=1);

namespace Tests\Database;

use App\Entity\ArticlePreview;
use App\Repository\ArticleRepository;
use App\Storage\ImageStorageInterface;
use App\Utils\Paginator;
use PDO;
use PHPUnit\Framework\TestCase;

final class ArticleRepositoryTest extends TestCase
{
    private PDO $pdo;
    private ArticleRepository $repo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createSchema();
        $this->repo = new ArticleRepository($this->pdo, $this->stubImageStorage());
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
                PRIMARY KEY (article_id, category_id),
                FOREIGN KEY (article_id) REFERENCES articles(id),
                FOREIGN KEY (category_id) REFERENCES categories(id)
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
        self::assertNull($this->repo->findBySlug('no-such-slug'));
    }

    public function testFindBySlugReturnsArticleWhenExists(): void
    {
        $this->pdo->exec("INSERT INTO articles (slug, title, body, views, published_at, created_at) VALUES ('hello', 'Hello', 'Body', 0, '2024-01-01 12:00:00', '2024-01-01 12:00:00')");
        $article = $this->repo->findBySlug('hello');
        self::assertNotNull($article);
        self::assertSame('hello', $article->slug);
        self::assertSame('Hello', $article->title);
        self::assertSame(0, $article->views);
    }

    public function testIncrementViews(): void
    {
        $this->pdo->exec("INSERT INTO articles (slug, title, body, views, published_at, created_at) VALUES ('v', 'V', 'B', 5, '2024-01-01 12:00:00', '2024-01-01 12:00:00')");
        $id = (int) $this->pdo->lastInsertId();
        $this->repo->incrementViews($id);
        $stmt = $this->pdo->query('SELECT views FROM articles WHERE id = ' . $id);
        self::assertSame(6, (int) $stmt->fetchColumn());
    }

    public function testCountByCategory(): void
    {
        $this->pdo->exec("INSERT INTO categories (slug, name) VALUES ('cat', 'Cat')");
        $catId = (int) $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO articles (slug, title, body, views, published_at, created_at) VALUES ('a1', 'A1', 'B', 0, '2024-01-01 12:00:00', '2024-01-01 12:00:00')");
        $a1 = (int) $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO articles (slug, title, body, views, published_at, created_at) VALUES ('a2', 'A2', 'B', 0, '2024-01-01 12:00:00', '2024-01-01 12:00:00')");
        $a2 = (int) $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO article_categories (article_id, category_id) VALUES ($a1, $catId), ($a2, $catId)");
        self::assertSame(2, $this->repo->countByCategory($catId));
    }

    public function testFindPaginatedByCategoryUsesPaginatorOffsetLimit(): void
    {
        $this->pdo->exec("INSERT INTO categories (slug, name) VALUES ('c', 'C')");
        $catId = (int) $this->pdo->lastInsertId();
        for ($i = 1; $i <= 5; $i++) {
            $this->pdo->exec("INSERT INTO articles (slug, title, body, views, published_at, created_at) VALUES ('art$i', 'Art$i', 'B', 0, '2024-01-0$i 12:00:00', '2024-01-01 12:00:00')");
            $aid = (int) $this->pdo->lastInsertId();
            $this->pdo->exec("INSERT INTO article_categories (article_id, category_id) VALUES ($aid, $catId)");
        }
        $paginator = Paginator::create(2, 2, 5);
        $items = $this->repo->findPaginatedByCategory($catId, $paginator, 'date');
        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(ArticlePreview::class, $items);
    }
}
