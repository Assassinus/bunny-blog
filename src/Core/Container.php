<?php

declare(strict_types=1);

namespace App\Core;

use App\Controller\ArticleController;
use App\Controller\CategoryController;
use App\Controller\HealthController;
use App\Controller\HomeController;
use App\Database\Connection;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\ArticleService;
use App\Service\CacheService;
use App\Service\CategoryService;
use App\Storage\ImageStorageInterface;
use App\Storage\LocalImageStorage;
use Closure;
use PDO;
use Psr\Log\LoggerInterface;
use Smarty\Smarty;

final class Container
{
    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, Closure> */
    private array $factories = [];

    public function __construct(
        private readonly string $projectRoot,
        private readonly array $config,
    ) {
        $this->registerFactories();
    }

    private function registerFactories(): void
    {
        $this->factories[PDO::class] = fn() => Connection::create($this->config['db']);

        $this->factories[Smarty::class] = function () {
            $smarty = new Smarty();
            $smarty->setTemplateDir($this->projectRoot . '/templates');
            $smarty->setCompileDir($this->projectRoot . '/var/smarty/compile');
            $smarty->setCacheDir($this->projectRoot . '/var/smarty/cache');
            $smarty->setConfigDir($this->projectRoot . '/config/smarty');
            $smarty->escape_html = true;
            foreach ([$smarty->getCompileDir(), $smarty->getCacheDir()] as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
            return $smarty;
        };

        $this->factories[LoggerInterface::class] = fn() => LoggerFactory::create(
            $this->projectRoot . '/var/log/app.log',
            'app'
        );

        $this->factories[CacheService::class] = fn() => new CacheService(
            $this->projectRoot . '/var/cache',
            300
        );

        $this->factories[ImageStorageInterface::class] = fn() => new LocalImageStorage(
            $this->config['app']['base_url'] ?? ''
        );

        $this->factories[ArticleRepository::class] = fn() => new ArticleRepository(
            $this->resolve(PDO::class),
            $this->resolve(ImageStorageInterface::class),
        );

        $this->factories[CategoryRepository::class] = fn() => new CategoryRepository(
            $this->resolve(PDO::class),
            $this->resolve(ImageStorageInterface::class),
        );

        $this->factories[ArticleService::class] = fn() => new ArticleService(
            $this->resolve(ArticleRepository::class),
            $this->resolve(CacheService::class),
        );

        $this->factories[CategoryService::class] = fn() => new CategoryService(
            $this->resolve(CategoryRepository::class),
            $this->resolve(CacheService::class),
        );

        $this->factories[HomeController::class] = fn() => new HomeController(
            $this->resolve(CategoryService::class),
            $this->resolve(Smarty::class),
        );

        $this->factories[CategoryController::class] = fn() => new CategoryController(
            $this->resolve(CategoryService::class),
            $this->resolve(ArticleService::class),
            $this->resolve(Smarty::class),
            $this->config['app']['per_page'],
        );

        $this->factories[ArticleController::class] = fn() => new ArticleController(
            $this->resolve(ArticleService::class),
            $this->resolve(Smarty::class),
        );

        $this->factories[HealthController::class] = fn() => new HealthController();
    }

    public function resolve(string $id): object
    {
        if (!isset($this->instances[$id])) {
            if (!isset($this->factories[$id])) {
                throw new \InvalidArgumentException('Unknown service: ' . $id);
            }
            $this->instances[$id] = $this->factories[$id]();
        }
        return $this->instances[$id];
    }

    public function getConfig(): array
    {
        return $this->config;
    }
    public function getSmarty(): Smarty
    {
        return $this->resolve(Smarty::class);
    }
    public function getLogger(): LoggerInterface
    {
        return $this->resolve(LoggerInterface::class);
    }
}
