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

use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use Integrated\Bundle\WebsiteBundle\Routing\PageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class EditableChecker
{
    protected ?Request $request;

    public function __construct(
        protected readonly AuthorizationChecker $authorizationChecker,
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router
    ) {
        $this->request = $requestStack->getMainRequest();
    }

    /**
     * @return bool
     */
    public function checkEditable()
    {
        if (null === $this->request) {
            return false;
        }

        if (null === $this->tokenStorage->getToken()) {
            return false;
        }

        if (!$this->authorizationChecker->isGranted('ROLE_WEBSITE_MANAGER') && !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return false;
        }

        $route = $this->request->attributes->get('_route');

        if ($routeObject = $this->router->getRouteCollection()->get($route)) {
            if ($routeObject->getOption('integratedEditable')) {
                return true;
            }
        }

        // check if route begins with page or contentTypePage prefix
        if (str_starts_with($route, ContentTypePageLoader::ROUTE_PREFIX)
              || str_starts_with($route, PageLoader::ROUTE_PREFIX)
        ) {
            return true;
        }

        return false;
    }
}
