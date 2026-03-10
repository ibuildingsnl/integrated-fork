<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\PageEditDraft;
use Integrated\Bundle\PageBundle\Grid\GridFactory;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\EventListener\WebsiteToolbarListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends AbstractController
{
    private const PREVIEW_EXPIRES_PARAM = 'preview_expires';
    private const PREVIEW_DRAFT_PARAM = 'page_draft_preview';
    private const DRAFT_NOTICE_TEXT = 'This item is currently unpublished';

    private ThemeManager $themeManager;
    private WebsiteToolbarListener $websiteToolbarListener;
    private UriSigner $uriSigner;
    private DocumentManager $documentManager;
    private GridFactory $gridFactory;

    public function __construct(ThemeManager $themeManager, WebsiteToolbarListener $websiteToolbarListener, UriSigner $uriSigner, DocumentManager $documentManager, GridFactory $gridFactory)
    {
        $this->themeManager = $themeManager;
        $this->websiteToolbarListener = $websiteToolbarListener;
        $this->uriSigner = $uriSigner;
        $this->documentManager = $documentManager;
        $this->gridFactory = $gridFactory;
    }

    public function show(Request $request, Page $page): Response
    {
        $canPreviewDraft = $this->isGranted('ROLE_WEBSITE_MANAGER') || $this->isGranted('ROLE_ADMIN');
        $hasValidPreviewLink = $this->hasValidDraftPreviewLink($request, $page);

        if ($page->isDisabled() && !$canPreviewDraft && !$hasValidPreviewLink) {
            throw new NotFoundHttpException();
        }

        if ($page->isDisabled()) {
            $this->websiteToolbarListener->setToolbarMessage(self::DRAFT_NOTICE_TEXT);
        }

        $this->applyDraftPreviewIfRequested($request, $page);

        $response = $this->render($this->themeManager->locateTemplate($page->getLayout()), [
            'page' => $page,
        ]);

        if ($page->isDisabled()) {
            $response->setPrivate();
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('max-age', '0');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    private function hasValidDraftPreviewLink(Request $request, Page $page): bool
    {
        $expires = $request->query->get(self::PREVIEW_EXPIRES_PARAM);
        if (!is_numeric($expires) || (int) $expires < time()) {
            return false;
        }

        if (!$this->uriSigner->checkRequest($request)) {
            return false;
        }

        $pageDomain = trim((string) $page->getDomain());
        if ($pageDomain === '') {
            return true;
        }

        return 0 === strcasecmp($request->getHost(), $pageDomain);
    }

    private function applyDraftPreviewIfRequested(Request $request, Page $page): void
    {
        $draftId = trim((string) $request->query->get(self::PREVIEW_DRAFT_PARAM));
        if ($draftId === '') {
            return;
        }

        if (!$this->hasValidDraftPreviewLink($request, $page)) {
            return;
        }

        $draft = $this->documentManager->getRepository(PageEditDraft::class)->find($draftId);
        if (!$draft instanceof PageEditDraft) {
            return;
        }

        if ($draft->getPageId() !== (string) $page->getId()) {
            return;
        }

        $grids = [];
        foreach ($draft->getGridPayload() as $gridPayload) {
            if (!\is_array($gridPayload)) {
                continue;
            }

            $grid = $this->gridFactory->fromArray($gridPayload);
            if ($grid instanceof Grid) {
                $grids[] = $grid;
            }
        }

        $page->setGrids($grids);
    }
}
