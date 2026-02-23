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

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\Service\ContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TaxonomyController extends AbstractController
{
    private ContentService $contentService;
    private ThemeManager $themeManager;

    public function __construct(ContentService $contentService, ThemeManager $themeManager)
    {
        $this->contentService = $contentService;
        $this->themeManager = $themeManager;
    }

    public function show(ContentTypePage $page, Taxonomy $taxonomy): Response
    {
        $this->contentService->prepare($taxonomy);

        return $this->render($this->themeManager->locateTemplate('content/taxonomy/show/'.$page->getLayout()), [
            'taxonomy' => $taxonomy,
            'page' => $page,
        ]);
    }

    public function showAction(ContentTypePage $page, Taxonomy $taxonomy): Response
    {
        return $this->show($page, $taxonomy);
    }
}
