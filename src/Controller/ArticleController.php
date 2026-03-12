<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ArticleService;
use App\Utils\Slug;
use Exception;
use Smarty\Smarty;

final class ArticleController extends AbstractController
{
    use NotFoundResponse;

    public function __construct(
        private readonly ArticleService $articleService,
        Smarty $smarty,
    ) {
        parent::__construct($smarty);
    }

    /** @throws Exception */
    public function __invoke(array $params): string
    {
        $slug = Slug::validate($params['slug'] ?? '');
        if ($slug === null) {
            return $this->renderNotFound('Статья не найдена');
        }

        $viewedIds = $_SESSION['viewed_articles'] ?? [];
        $article = $this->articleService->getArticleBySlug($slug, $viewedIds);
        if ($article === null) {
            return $this->renderNotFound('Статья не найдена');
        }

        $_SESSION['viewed_articles'][$article->id] = true;

        $similar = $this->articleService->findSimilarArticles($article);

        return $this->render('article.tpl', [
            'article' => $article,
            'similar' => $similar,
        ]);
    }
}
