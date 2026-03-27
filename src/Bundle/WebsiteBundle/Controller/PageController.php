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

use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\EventListener\WebsiteToolbarListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends AbstractController
{
    private const PREVIEW_EXPIRES_PARAM = 'preview_expires';
    private const DRAFT_NOTICE_TEXT = 'This item is currently unpublished';

    private ThemeManager $themeManager;
    private WebsiteToolbarListener $websiteToolbarListener;
    private UriSigner $uriSigner;

    public function __construct(ThemeManager $themeManager, WebsiteToolbarListener $websiteToolbarListener, UriSigner $uriSigner)
    {
        $this->themeManager = $themeManager;
        $this->websiteToolbarListener = $websiteToolbarListener;
        $this->uriSigner = $uriSigner;
    }

    public function show(Request $request, Page $page): Response
    {
        $now = new \DateTimeImmutable();
        $canPreviewDraft = $this->isGranted('ROLE_WEBSITE_MANAGER') || $this->isGranted('ROLE_ADMIN');
        $hasValidPreviewLink = $this->hasValidDraftPreviewLink($request, $page);
        $canPreview = $canPreviewDraft || $hasValidPreviewLink;
        $isPublic = $this->isPublicPage($page, $now);

        if ($page->isDisabled()) {
            if (!$canPreview) {
                throw new NotFoundHttpException();
            }
        } elseif (!$isPublic) {
            if (!$canPreview && $this->isExpired($page, $now)) {
                $redirectUrl = $this->getExpireRedirectUrl($page);
                if (null !== $redirectUrl) {
                    return new RedirectResponse($redirectUrl);
                }
            }

            if (!$canPreview) {
                throw new NotFoundHttpException();
            }
        }

        if (!$isPublic) {
            $this->websiteToolbarListener->setToolbarMessage(self::DRAFT_NOTICE_TEXT);
        }

        $response = $this->render($this->themeManager->locateTemplate($page->getLayout()), [
            'page' => $page,
        ]);

        if (!$isPublic) {
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

    private function isPublicPage(Page $page, \DateTimeInterface $now): bool
    {
        if ($page->isDisabled()) {
            return false;
        }

        $publishAt = $page->getPublishAt();
        if (null !== $publishAt && $publishAt > $now) {
            return false;
        }

        $expireAt = $page->getExpireAt();
        if (null !== $expireAt && $expireAt <= $now) {
            return false;
        }

        return true;
    }

    private function isExpired(Page $page, \DateTimeInterface $now): bool
    {
        $expireAt = $page->getExpireAt();

        return null !== $expireAt && $expireAt <= $now;
    }

    private function getExpireRedirectUrl(Page $page): ?string
    {
        $redirectUrl = trim((string) $page->getExpireRedirectUrl());

        return '' === $redirectUrl ? null : $redirectUrl;
    }
}
