<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use Integrated\Bundle\WebsiteBundle\Routing\PageLoader;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class EditableChecker
{
    protected ?Request $request;
    /** @var ObjectRepository<AbstractPage> */
    private ObjectRepository $pageRepository;

    public function __construct(
        protected readonly AuthorizationCheckerInterface $authorizationChecker,
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
        DocumentManager $documentManager,
    ) {
        $this->request = $requestStack->getMainRequest();
        $this->pageRepository = $documentManager->getRepository(AbstractPage::class);
    }

    /**
     * @return bool
     */
    public function checkEditable()
    {
        if (null === $this->request) {
            return false;
        }

        if (!$this->hasAuthenticationHints()) {
            return false;
        }

        if (null === $this->tokenStorage->getToken()) {
            return false;
        }

        $route = (string) $this->request->attributes->get('_route', '');
        $hasGlobalWebsiteAccess = $this->authorizationChecker->isGranted('ROLE_WEBSITE_MANAGER')
            || $this->authorizationChecker->isGranted('ROLE_ADMIN');
        $hasChannelWriteAccess = $this->hasChannelWriteAccessForCurrentPage();

        if ($routeObject = $this->router->getRouteCollection()->get($route)) {
            if ($routeObject->getOption('integratedEditable')) {
                return $hasGlobalWebsiteAccess || $hasChannelWriteAccess;
            }
        }

        // check if route begins with page or contentTypePage prefix
        if (str_starts_with($route, ContentTypePageLoader::ROUTE_PREFIX)
              || str_starts_with($route, PageLoader::ROUTE_PREFIX)
        ) {
            return $hasGlobalWebsiteAccess || $hasChannelWriteAccess;
        }

        return false;
    }

    private function hasChannelWriteAccessForCurrentPage(): bool
    {
        $page = $this->request?->attributes->get('page');

        if (\is_string($page) && $page !== '') {
            $page = $this->pageRepository->find($page);
        }

        if (!$page instanceof AbstractPage) {
            return false;
        }

        return $this->authorizationChecker->isGranted(PermissionInterface::WRITE, $page->getChannel());
    }

    private function hasAuthenticationHints(): bool
    {
        if (null === $this->request) {
            return false;
        }

        $sessionCookieName = session_name();
        if (\is_string($sessionCookieName) && $sessionCookieName !== '' && $this->request->cookies->has($sessionCookieName)) {
            return true;
        }

        return $this->request->cookies->has('REMEMBERME');
    }
}
