<?php

namespace Integrated\Bundle\UserBundle\Tests\Form\Type;

use Integrated\Bundle\UserBundle\Form\Type\UserFormType;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\RoleInterface;
use Integrated\Bundle\UserBundle\Model\ScopeInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use PHPUnit\Framework\TestCase;

class UserFormTypeTest extends TestCase
{
    public function testResolveOptionalExistingUserReturnsExistingUserWhenProvided(): void
    {
        $existingUser = new TestUser('7');
        $currentData = new \stdClass();

        $resolved = UserFormType::resolveOptionalExistingUser($existingUser, $currentData);

        self::assertSame($existingUser, $resolved);
    }

    public function testResolveOptionalExistingUserKeepsCurrentDataWhenNoUserSelected(): void
    {
        $currentData = new \stdClass();

        $resolved = UserFormType::resolveOptionalExistingUser(null, $currentData);

        self::assertSame($currentData, $resolved);
    }

    public function testHasRelationConflictReturnsTrueWhenAnotherUserUsesSameRelation(): void
    {
        $currentUserId = '10';
        $existingUsers = [new TestUser('11')];

        self::assertTrue(UserFormType::hasRelationConflict('abc123', $currentUserId, $existingUsers));
    }

    public function testHasRelationConflictReturnsFalseWhenOnlyCurrentUserMatches(): void
    {
        $currentUserId = '10';
        $existingUsers = [new TestUser('10')];

        self::assertFalse(UserFormType::hasRelationConflict('abc123', $currentUserId, $existingUsers));
    }
}

class TestUser implements UserInterface
{
    public function __construct(private readonly string $id)
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username)
    {
    }

    public function setPassword($password)
    {
    }

    public function setSalt($salt)
    {
    }

    public function setEmail($email)
    {
    }

    public function getEmail()
    {
        return null;
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function setEnabled(bool $enabled): void
    {
    }

    public function addRole(RoleInterface $role)
    {
    }

    public function setScope(ScopeInterface $scope)
    {
    }

    public function getScope()
    {
        return null;
    }

    public function setGoogleAuthenticatorEnabled(bool $googleAuthenticatorEnabled): void
    {
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
    }

    public function getUserIdentifier(): string
    {
        return 'test@example.com';
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUsername()
    {
        return $this->getUserIdentifier();
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function isEqualTo(\Symfony\Component\Security\Core\User\UserInterface $user): bool
    {
        return $user->getUserIdentifier() === $this->getUserIdentifier();
    }

    public function addGroup(GroupInterface $group)
    {
    }

    public function removeGroup(GroupInterface $group)
    {
    }

    public function hasGroup(GroupInterface $group)
    {
        return false;
    }

    public function getGroups()
    {
        return [];
    }

    public function setGroups($groups)
    {
    }

    public function serialize()
    {
        return '';
    }

    public function unserialize($serialized)
    {
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return false;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return null;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return 'test@example.com';
    }
}

