<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use Smarty\Exception;
use Smarty\Smarty;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        Smarty $smarty,
    ) {
        parent::__construct($smarty);
    }

    /**
     * @param array<string, string> $params
     * @throws Exception
     * @throws \Exception
     */
    public function __invoke(array $params): string
    {
        $categories = $this->categoryService->getCategoriesWithLatestArticles(3);
        return $this->render('home.tpl', ['categories' => $categories]);
    }
}
