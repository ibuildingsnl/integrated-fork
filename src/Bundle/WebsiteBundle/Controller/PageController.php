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
use Integrated\Bundle\WebsiteBundle\Service\FacetQueryCanonicalizer;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
    private FacetQueryCanonicalizer $facetQueryCanonicalizer;

    public function __construct(ThemeManager $themeManager, WebsiteToolbarListener $websiteToolbarListener, UriSigner $uriSigner, FacetQueryCanonicalizer $facetQueryCanonicalizer)
    {
        $this->themeManager = $themeManager;
        $this->websiteToolbarListener = $websiteToolbarListener;
        $this->uriSigner = $uriSigner;
        $this->facetQueryCanonicalizer = $facetQueryCanonicalizer;
    }

    public function show(Request $request, Page $page): Response
    {
        $now = new \DateTimeImmutable();
        $canPreviewDraft = $this->canPreviewUnpublishedPage($page);
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

        if (null !== $normalizedPath = $this->facetQueryCanonicalizer->getNormalizedPath($page, $request)) {
            return new RedirectResponse($normalizedPath);
        }

        $request->attributes->set('_integrated_page_document', $page);

        $layoutTemplate = $this->resolveLayoutTemplate($page);
        $response = $this->render($layoutTemplate, [
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

    private function canPreviewUnpublishedPage(Page $page): bool
    {
        if ($this->isGranted('ROLE_WEBSITE_MANAGER') || $this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $channel = $page->getChannel();

        return $this->isGranted(PermissionInterface::READ, $channel)
            || $this->isGranted(PermissionInterface::WRITE, $channel);
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

    private function resolveLayoutTemplate(Page $page): string
    {
        $layout = trim((string) $page->getLayout());
        if ('' === $layout) {
            throw new NotFoundHttpException(
                \sprintf(
                    'Unable to resolve page layout template for page "%s" (path "%s"): layout is empty.',
                    trim((string) $page->getId()),
                    trim((string) $page->getPath())
                )
            );
        }

        $template = $this->themeManager->locateTemplate($layout);
        if ('' === trim((string) $template)) {
            throw new NotFoundHttpException(
                \sprintf(
                    'Unable to resolve page layout template for page "%s" (path "%s", layout "%s", active theme "%s").',
                    trim((string) $page->getId()),
                    trim((string) $page->getPath()),
                    $layout,
                    trim((string) $this->themeManager->getActiveTheme())
                )
            );
        }

        return $template;
    }

    private function isExpired(Page $page, \DateTimeInterface $now): bool
    {
        $expireAt = $page->getExpireAt();

        return null !== $expireAt && $expireAt <= $now;
    }

    private function getExpireRedirectUrl(Page $page): ?string
    {
        $redirectUrl = trim((string) $page->getExpireRedirectUrl());

        if ('' === $redirectUrl || !$this->isAllowedRedirectUrl($redirectUrl)) {
            return null;
        }

        return $redirectUrl;
    }

    private function isAllowedRedirectUrl(string $redirectUrl): bool
    {
        if (str_starts_with($redirectUrl, '/') && !str_starts_with($redirectUrl, '//')) {
            return true;
        }

        if (false === filter_var($redirectUrl, \FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($redirectUrl);
        if (false === $parts) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!\in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        if ('' === (string) ($parts['host'] ?? '')) {
            return false;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        return true;
    }
}
