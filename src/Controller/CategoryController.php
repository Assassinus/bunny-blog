<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ArticleService;
use App\Service\CategoryService;
use App\Utils\Slug;
use Smarty\Exception;
use Smarty\Smarty;

final class CategoryController extends AbstractController
{
    use NotFoundResponse;

    private const array ALLOWED_SORT = ['date', 'views'];

    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly ArticleService $articleService,
        Smarty $smarty,
        private readonly int $perPage,
    ) {
        parent::__construct($smarty);
    }

    /**
     * @param array<string, string> $params
     * @throws Exception
     */
    public function __invoke(array $params): string
    {
        $slug = Slug::validate($params['slug'] ?? '');
        if ($slug === null) {
            return $this->renderNotFound('Категория не найдена');
        }

        $category = $this->categoryService->getBySlug($slug);
        if ($category === null) {
            return $this->renderNotFound('Категория не найдена');
        }

        $query = $params['_query'] ?? [];
        $page = $this->parsePage($query['page'] ?? '1');
        $sort = $this->parseSort($query['sort'] ?? 'date');

        $result = $this->articleService->getArticlesByCategoryPaginated($category->id, $page, $this->perPage, $sort);

        return $this->render('category.tpl', [
            'category' => $category,
            'articles' => $result['items'],
            'paginator' => $result['paginator'],
            'sort' => $sort,
        ]);
    }

    private function parsePage(string $value): int
    {
        return ($value !== '' && ctype_digit($value)) ? max(1, (int) $value) : 1;
    }

    private function parseSort(string $value): string
    {
        return in_array($value, self::ALLOWED_SORT, true) ? $value : 'date';
    }
}
