<?php

namespace Integrated\Bundle\UserBundle\Tests\Service;

use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Service\KeyGenerator;
use PHPUnit\Framework\TestCase;

class KeyGeneratorTest extends TestCase
{
    public function testGenerateAndValidateKey(): void
    {
        $timestamp = time();
        $user = $this->createConfiguredMock(UserInterface::class, [
            'getPassword' => 'hashed-password',
            'getId' => 42,
        ]);

        $userManager = $this->createMock(UserManager::class);
        $userManager
            ->expects(self::once())
            ->method('find')
            ->with(42)
            ->willReturn($user);

        $generator = new KeyGenerator($userManager, 'test-secret');
        $key = $generator->generateKey($timestamp, $user);

        self::assertTrue($generator->isValidKey(42, $timestamp, $key));
    }

    public function testRejectsInvalidAndExpiredKeys(): void
    {
        $timestamp = time() - (25 * 3600);
        $userManager = $this->createMock(UserManager::class);
        $userManager
            ->expects(self::never())
            ->method('find');

        $generator = new KeyGenerator($userManager, 'test-secret');

        self::assertFalse($generator->isValidKey(42, $timestamp, 'invalid-key'));
    }
}
