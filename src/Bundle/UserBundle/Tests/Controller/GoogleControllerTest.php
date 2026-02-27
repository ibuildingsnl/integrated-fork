<?php

namespace Integrated\Bundle\UserBundle\Tests\Controller;

use Integrated\Bundle\UserBundle\Controller\TwoFactor\GoogleController;
use Integrated\Bundle\UserBundle\Handler\TwoFactor\HandlerFactoryInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\UserBundle\Security\TwoFactor\Http\ContextResolverInterface;
use Integrated\Bundle\UserBundle\Security\TwoFactor\Http\TargetProvider;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\HttpUtils;

class GoogleControllerTest extends TestCase
{
    public function testFormThrowsAccessDeniedWhenContextCannotBeResolved(): void
    {
        $resolver = $this->createMock(ContextResolverInterface::class);
        $resolver->method('resolve')->willReturn(null);

        $controller = new GoogleController(
            $resolver,
            $this->createMock(UserManagerInterface::class),
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(HandlerFactoryInterface::class),
            $this->createMock(TargetProvider::class),
            $this->createMock(HttpUtils::class)
        );

        $this->expectException(AccessDeniedException::class);

        $controller->form(new Request());
    }

    public function testCheckThrowsAccessDeniedWhenContextCannotBeResolved(): void
    {
        $resolver = $this->createMock(ContextResolverInterface::class);
        $resolver->method('resolve')->willReturn(null);

        $controller = new GoogleController(
            $resolver,
            $this->createMock(UserManagerInterface::class),
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(HandlerFactoryInterface::class),
            $this->createMock(TargetProvider::class),
            $this->createMock(HttpUtils::class)
        );

        $this->expectException(AccessDeniedException::class);

        $controller->check(new Request());
    }
}
