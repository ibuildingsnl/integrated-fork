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

use Integrated\Bundle\ContentBundle\Document\Content\JobPosting;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\Service\ContentDocumentResolver;
use Integrated\Bundle\WebsiteBundle\Service\ContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JobPostingController extends AbstractController
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
        $jobPosting = $this->contentDocumentResolver->resolve($request, JobPosting::class);
        $this->contentService->prepare($jobPosting);

        return $this->render($this->themeManager->locateTemplate('content/jobposting/show/'.$page->getLayout()), [
            'jobPosting' => $jobPosting,
            'page' => $page,
        ]);
    }

    public function showAction(ContentTypePage $page, Request $request): Response
    {
        return $this->show($page, $request);
    }

    public function showA(ContentTypePage $page, Request $request): Response
    {
        return $this->show($page, $request);
    }
}
