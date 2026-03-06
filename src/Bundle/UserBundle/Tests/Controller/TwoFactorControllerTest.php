<?php

namespace Integrated\Bundle\UserBundle\Tests\Controller;

use Integrated\Bundle\UserBundle\Controller\TwoFactorController;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class TwoFactorControllerTest extends TestCase
{
    public function testDeleteRedirectsWhenUserDoesNotExist(): void
    {
        [$controller, $manager] = $this->createController();

        $manager->method('find')->with('42')->willReturn(null);

        $response = $controller->delete(new Request(['id' => '42']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
    }

    public function testDeleteRedirectsWhenTwoFactorIsDisabled(): void
    {
        [$controller, $manager] = $this->createController();
        $user = new User();
        $user->setUsername('tester');

        $manager->method('find')->with('42')->willReturn($user);

        $response = $controller->delete(new Request(['id' => '42']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
    }

    /**
     * @return array{TestTwoFactorController, UserManagerInterface&MockObject}
     */
    private function createController(): array
    {
        $manager = $this->createMock(UserManagerInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $controller = new TestTwoFactorController($manager, $translator);

        return [$controller, $manager];
    }
}

final class TestTwoFactorController extends TwoFactorController
{
    public bool $granted = true;

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->granted;
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return new RedirectResponse('/'.$route, $status);
    }
}

