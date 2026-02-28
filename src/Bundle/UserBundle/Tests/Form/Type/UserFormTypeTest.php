<?php

namespace Integrated\Bundle\UserBundle\Tests\Form\Type;

use Integrated\Bundle\UserBundle\Form\Type\UserFormType;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\RoleInterface;
use Integrated\Bundle\UserBundle\Model\ScopeInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;

class UserFormTypeTest extends TestCase
{
    public function testBuildFormConfiguresRelationAsSelect2(): void
    {
        $manager = $this->createMock(UserManagerInterface::class);
        $hasherFactory = $this->createMock(PasswordHasherFactoryInterface::class);
        $type = new UserFormType($manager, $hasherFactory);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('addEventSubscriber')->willReturnSelf();
        $builder->method('addEventListener')->willReturnSelf();

        $relationOptions = null;
        $builder
            ->method('add')
            ->willReturnCallback(function ($child, $type = null, array $options = []) use ($builder, &$relationOptions) {
                if ('relation' === $child) {
                    $relationOptions = $options;
                }

                return $builder;
            });

        $type->buildForm($builder, ['optional' => false]);

        self::assertIsArray($relationOptions);
        self::assertSame('select2', $relationOptions['attr']['class'] ?? null);

        $person = new Person();
        $person->setFirstName('Jane');
        $person->setLastName('Doe');
        $person->setContentType('Author');

        self::assertSame('Jane Doe (Author)', ($relationOptions['choice_label'])($person));
    }

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
    private string $email = '';
    private ScopeInterface $scope;

    public function __construct(private readonly string $id)
    {
        $this->scope = new class implements ScopeInterface {
            private string $id = 'scope';
            private string $name = 'scope';
            private bool $admin = false;

            public function getId()
            {
                return $this->id;
            }

            public function setName($name)
            {
                $this->name = (string) $name;

                return $this;
            }

            public function getName()
            {
                return $this->name;
            }

            public function isAdmin()
            {
                return $this->admin;
            }

            public function setAdmin($admin)
            {
                $this->admin = (bool) $admin;

                return $this;
            }
        };
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username): void
    {
    }

    public function setPassword($password): void
    {
    }

    public function setSalt($salt): void
    {
    }

    public function setEmail($email): void
    {
        $this->email = (string) $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function setEnabled(bool $enabled): void
    {
    }

    public function addRole(RoleInterface $role): void
    {
    }

    public function setScope(ScopeInterface $scope): void
    {
        $this->scope = $scope;
    }

    public function getScope(): ScopeInterface
    {
        return $this->scope;
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

    public function getUsername(): string
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

    public function addGroup(GroupInterface $group): void
    {
    }

    public function removeGroup(GroupInterface $group): void
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

    public function setGroups($groups): void
    {
    }

    public function serialize(): string
    {
        return '';
    }

    public function unserialize($serialized): void
    {
    }

    public function __serialize(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $data
     */
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
