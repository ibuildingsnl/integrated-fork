<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Controller\TwoFactor;

use Integrated\Bundle\UserBundle\Handler\TwoFactor\HandlerFactoryInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\UserBundle\Security\TwoFactor\Http\ContextResolverInterface;
use Integrated\Bundle\UserBundle\Security\TwoFactor\Http\TargetProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;

class GoogleController extends AbstractController
{
    private ContextResolverInterface $resolver;
    private UserManagerInterface $manager;
    private GoogleAuthenticatorInterface $authenticator;
    private HandlerFactoryInterface $factory;
    private TargetProvider $provider;
    private HttpUtils $utils;

    public function __construct(
        ContextResolverInterface $resolver,
        UserManagerInterface $manager,
        GoogleAuthenticatorInterface $authenticator,
        HandlerFactoryInterface $factory,
        TargetProvider $provider,
        HttpUtils $utils,
    ) {
        $this->resolver = $resolver;
        $this->manager = $manager;
        $this->authenticator = $authenticator;
        $this->factory = $factory;
        $this->provider = $provider;
        $this->utils = $utils;
    }

    public function form(Request $request): Response
    {
        $context = $this->resolver->resolve($request);

        if (!$context) {
            throw $this->createAccessDeniedException();
        }

        $user = $context->getUser();

        if (!$user || $user->isGoogleAuthenticatorEnabled()) {
            return $this->utils->createRedirectResponse($request, $this->provider->getTargetPath($context));
        }

        if (!$user->getGoogleAuthenticatorSecret()) {
            $user->setGoogleAuthenticatorSecret($this->authenticator->generateSecret());

            $this->manager->persist($user);

            return $this->redirectToRoute($context->getConfig()->getFormPath());
        }

        return new Response($this->factory->create($context)->render());
    }

    public function check(Request $request): Response
    {
        $context = $this->resolver->resolve($request);

        if (!$context) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();

        if (!$user instanceof UserInterface || $user->isGoogleAuthenticatorEnabled()) {
            return $this->utils->createRedirectResponse($request, $this->provider->getTargetPath($context));
        }

        $handler = $this->factory->create($context);

        if ($handler->validate()) {
            $user->setGoogleAuthenticatorEnabled(true);

            $this->manager->persist($user);

            return $this->utils->createRedirectResponse($request, $this->provider->getTargetPath($context));
        }

        return $this->redirectToRoute($context->getConfig()->getFormPath());
    }
}
