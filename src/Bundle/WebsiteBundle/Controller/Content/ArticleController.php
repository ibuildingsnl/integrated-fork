<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Controller\Content;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\Service\ContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends AbstractController
{
    private ContentService $contentService;
    private ThemeManager $themeManager;

    public function __construct(ContentService $contentService, ThemeManager $themeManager)
    {
        $this->contentService = $contentService;
        $this->themeManager = $themeManager;
    }

    public function show(ContentTypePage $page, Article $article): Response
    {
        $this->contentService->prepare($article);

        return $this->render($this->themeManager->locateTemplate('content/article/show/'.$page->getLayout()), [
            'article' => $article,
            'page' => $page,
        ]);
    }

    public function showAction(ContentTypePage $page, Article $article): Response
    {
        return $this->show($page, $article);
    }
}
