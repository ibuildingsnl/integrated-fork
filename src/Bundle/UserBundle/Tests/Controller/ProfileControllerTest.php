<?php

namespace Integrated\Bundle\UserBundle\Tests\Controller;

use Integrated\Bundle\UserBundle\Controller\ProfileController;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class ProfileControllerTest extends TestCase
{
    public function testDeactivateTwoFactorRejectsInvalidToken(): void
    {
        [$controller, $manager] = $this->createController();
        $controller->csrfValid = false;
        $controller->currentUser = $this->createTwoFactorUser('user-a');

        $manager->expects(self::never())->method('persist');

        $response = $controller->deactivateTwoFactor(new Request([], ['_token' => 'invalid']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_profile_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
    }

    public function testDeactivateTwoFactorWhenAlreadyDisabled(): void
    {
        [$controller, $manager] = $this->createController();
        $controller->currentUser = $this->createUser('user-a');

        $manager->expects(self::never())->method('persist');

        $response = $controller->deactivateTwoFactor(new Request([], ['_token' => 'valid']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_profile_index', $response->getTargetUrl());
        self::assertSame('info', $controller->flashes[0]['type']);
    }

    public function testDeactivateTwoFactorSuccess(): void
    {
        [$controller, $manager] = $this->createController();
        $user = $this->createTwoFactorUser('user-a');
        $controller->currentUser = $user;

        $manager->expects(self::once())->method('persist')->with($user);

        $response = $controller->deactivateTwoFactor(new Request([], ['_token' => 'valid']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_profile_index', $response->getTargetUrl());
        self::assertSame('success', $controller->flashes[0]['type']);
        self::assertFalse($user->isGoogleAuthenticatorEnabled());
    }

    public function testResetTwoFactorRejectsInvalidToken(): void
    {
        [$controller, $manager] = $this->createController();
        $controller->csrfValid = false;
        $controller->currentUser = $this->createTwoFactorUser('user-a');

        $manager->expects(self::never())->method('persist');

        $response = $controller->resetTwoFactor(new Request([], ['_token' => 'invalid']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_profile_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
    }

    public function testResetTwoFactorSuccessRedirectsToActivation(): void
    {
        [$controller, $manager] = $this->createController();
        $user = $this->createTwoFactorUser('user-a');
        $controller->currentUser = $user;

        $manager->expects(self::once())->method('persist')->with($user);

        $response = $controller->resetTwoFactor(new Request([], ['_token' => 'valid']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_two_factor_authenticator_activate', $response->getTargetUrl());
        self::assertSame('success', $controller->flashes[0]['type']);
    }

    /**
     * @return array{TestProfileController, UserManagerInterface&MockObject}
     */
    private function createController(): array
    {
        $manager = $this->createMock(UserManagerInterface::class);
        $hasherFactory = $this->createMock(PasswordHasherFactoryInterface::class);
        $controller = new TestProfileController($manager, $hasherFactory);

        return [$controller, $manager];
    }

    private function createUser(string $username): User
    {
        $user = new User();
        $user->setUsername($username);

        return $user;
    }

    private function createTwoFactorUser(string $username): User
    {
        $user = $this->createUser($username);
        $user->setGoogleAuthenticatorSecret('secret-123');
        $user->setGoogleAuthenticatorEnabled(true);

        return $user;
    }
}

final class TestProfileController extends ProfileController
{
    public bool $csrfValid = true;
    public ?SymfonyUserInterface $currentUser = null;
    public array $flashes = [];

    protected function getUser(): ?SymfonyUserInterface
    {
        return $this->currentUser;
    }

    protected function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfValid;
    }

    protected function addFlash(string $type, mixed $message): void
    {
        $this->flashes[] = ['type' => $type, 'message' => $message];
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return new RedirectResponse('/'.$route, $status);
    }
}

