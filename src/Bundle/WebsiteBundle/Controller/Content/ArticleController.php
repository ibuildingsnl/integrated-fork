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
use Integrated\Bundle\WebsiteBundle\Service\ContentDocumentResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends AbstractController
{
    private ContentService $contentService;
    private ThemeManager $themeManager;
    private ContentDocumentResolver $contentDocumentResolver;

    public function __construct(ContentService $contentService, ThemeManager $themeManager, ContentDocumentResolver $contentDocumentResolver)
    {
        $this->contentService = $contentService;
        $this->themeManager = $themeManager;
        $this->contentDocumentResolver = $contentDocumentResolver;
    }

    public function show(ContentTypePage $page, Request $request): Response
    {
        $article = $this->contentDocumentResolver->resolve($request, Article::class);
        $this->contentService->prepare($article);

        return $this->render($this->themeManager->locateTemplate('content/article/show/'.$page->getLayout()), [
            'article' => $article,
            'page' => $page,
        ]);
    }

    public function showAction(ContentTypePage $page, Request $request): Response
    {
        return $this->show($page, $request);
    }
}
