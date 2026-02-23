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
use Integrated\Bundle\WebsiteBundle\Service\ContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class JobPostingController extends AbstractController
{
    private ContentService $contentService;
    private ThemeManager $themeManager;

    public function __construct(ContentService $contentService, ThemeManager $themeManager)
    {
        $this->contentService = $contentService;
        $this->themeManager = $themeManager;
    }

    public function show(ContentTypePage $page, JobPosting $jobPosting): Response
    {
        $this->contentService->prepare($jobPosting);

        return $this->render($this->themeManager->locateTemplate('content/jobposting/show/'.$page->getLayout()), [
            'jobPosting' => $jobPosting,
            'page' => $page,
        ]);
    }

    public function showAction(ContentTypePage $page, JobPosting $jobPosting): Response
    {
        return $this->show($page, $jobPosting);
    }

    public function showA(ContentTypePage $page, JobPosting $jobPosting): Response
    {
        return $this->show($page, $jobPosting);
    }
}
