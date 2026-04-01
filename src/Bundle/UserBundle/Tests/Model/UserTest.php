<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Model;

use Integrated\Bundle\UserBundle\Model\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testGetUserIdentifierReturnsEmptyStringWhenUsernameIsMissing(): void
    {
        $user = new User();

        self::assertSame('', $user->getUserIdentifier());
    }
}
