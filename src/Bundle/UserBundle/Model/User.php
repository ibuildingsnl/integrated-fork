<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class User implements UserInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string|null
     */
    protected $salt;

    /**
     * @var string|null
     */
    protected $email;

    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var Collection<int, GroupInterface>|null
     */
    protected $groups;

    /**
     * @var Collection<int, RoleInterface>|null
     */
    protected $roles;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var string
     */
    protected $relation;

    /**
     * @var Scope
     */
    protected $scope;

    /**
     * @var string
     */
    protected $googleSecret;

    /**
     * @var bool
     */
    protected $googleEnabled = false;

    /**
     * @var \Integrated\Bundle\ContentBundle\Document\Content\Relation\Relation
     */
    protected $relation_instance;

    /**
     * @var bool|null
     */
    private $serializedEnabled;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function serialize()
    {
        [$enabled, $roles, $scopeFingerprint] = $this->getSerializedSecurityState();

        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $enabled,
            $roles,
            $scopeFingerprint,
        ]);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $enabled = $data[4] ?? null;
        $roles = $data[5] ?? null;
        $scopeFingerprint = $data[6] ?? null;

        list(
            $this->id,
            $this->username,
            $this->password,
            $this->salt) = $data;

        $this->serializedEnabled = \is_bool($enabled) ? $enabled : null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username)
    {
        $this->username = (string) $username;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function setPassword($password)
    {
        $this->password = (string) $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt !== null ? (string) $salt : null;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setEmail($email)
    {
        $this->email = $email !== null ? (string) $email : null;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function addGroup(GroupInterface $group)
    {
        $groups = $this->getGroupCollection();

        if (!$groups->contains($group)) {
            $groups->add($group);
        }
    }

    public function removeGroup(GroupInterface $group)
    {
        $this->getGroupCollection()->removeElement($group);
    }

    /**
     * @return bool
     */
    public function hasGroup(GroupInterface $group)
    {
        return $this->getGroupCollection()->contains($group);
    }

    public function getGroups()
    {
        return $this->getGroupCollection()->toArray();
    }

    /**
     * @param GroupInterface[] $groups
     */
    public function setGroups($groups)
    {
        $this->groups = new ArrayCollection();

        foreach ($groups as $group) {
            $this->addGroup($group);
        }
    }

    public function addRole(RoleInterface $role)
    {
        $roles = $this->getRoleCollection();

        if (!$roles->contains($role)) {
            $roles->add($role);
        }
    }

    public function removeRole(RoleInterface $role)
    {
        $this->getRoleCollection()->removeElement($role);
    }

    /**
     * @return bool
     */
    public function hasRole(RoleInterface $role)
    {
        return $this->getRoleCollection()->contains($role);
    }

    public function getRoles(): array
    {
        $roles = [];
        $roleCollection = $this->getRoleCollection();

        if ($this->enabled) {
            $roles[] = 'ROLE_USER'; // Every user must have this role
        }

        foreach ($roleCollection as $role) {
            $roles[] = $role->getRole();
        }

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        return array_unique($roles);
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = (bool) $enabled;
    }

    /**
     * @param \Integrated\Bundle\ContentBundle\Document\Content\Relation\Relation $relation
     */
    public function setRelation($relation = null)
    {
        $relation = $relation instanceof \Integrated\Bundle\ContentBundle\Document\Content\Relation\Relation ? $relation : null;

        $this->email = $relation ? $relation->getEmail() : null;
        $this->relation = $relation ? $relation->getId() : null;
        $this->relation_instance = $relation;
    }

    /**
     * @return \Integrated\Bundle\ContentBundle\Document\Content\Relation\Relation
     */
    public function getRelation()
    {
        return $this->relation_instance;
    }

    /**
     * @return ScopeInterface
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return $this
     */
    public function setScope(ScopeInterface $scope)
    {
        $this->scope = $scope;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function eraseCredentials(): void
    {
        /* do nothing as there are no unsecured credentials, password should be encrypted */
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->googleEnabled && $this->googleSecret;
    }

    public function setGoogleAuthenticatorEnabled(bool $googleAuthenticatorEnabled): void
    {
        $this->googleEnabled = $googleAuthenticatorEnabled ? (bool) $this->googleSecret : false;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->username;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->googleSecret = $googleAuthenticatorSecret ?: null;

        if ($this->googleSecret === null) {
            $this->googleEnabled = false;
        }
    }

    /**
     * Get the string representation of the user object.
     *
     * This can be useful for debugging
     *
     * @return string
     */
    public function __toString()
    {
        return \sprintf(
            "ID: %s\nUsername: %s\n CreatedAt: %s\nEnabled: %s",
            $this->getId(),
            $this->getUserIdentifier(),
            $this->getCreatedAt()->format('r'),
            $this->isEnabled() ? 'TRUE' : 'FALSE'
        );
    }

    public function isEqualTo(BaseUserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return $user->getUserIdentifier() === $this->getUserIdentifier()
            && $user->getPassword() === $this->getPassword()
            && $user->getSalt() === $this->getSalt()
            && $this->getEnabledSnapshot($user) === $this->getEnabledSnapshot($this);
    }

    /**
     * @param array<int, string> $roles
     *
     * @return array<int, string>
     */
    private function normalizeRoles(array $roles): array
    {
        $roles = array_values(array_unique($roles));
        sort($roles);

        return $roles;
    }

    private function getScopeFingerprint(self $user): string
    {
        $scope = $user->getScope();

        return \sprintf('%s:%d', (string) $scope->getId(), $scope->isAdmin() ? 1 : 0);
    }

    private function getEnabledSnapshot(self $user): bool
    {
        return $user->serializedEnabled ?? $user->isEnabled();
    }

    /**
     * @return array{0: bool, 1: string[], 2: string}
     */
    private function getSerializedSecurityState(): array
    {
        return [
            $this->isEnabled(),
            $this->normalizeRoles($this->getRoles()),
            $this->getScopeFingerprint($this),
        ];
    }

    /**
     * @return Collection<int, GroupInterface>
     */
    private function getGroupCollection(): Collection
    {
        if (!$this->groups instanceof Collection) {
            $this->groups = new ArrayCollection();
        }

        return $this->groups;
    }

    /**
     * @return Collection<int, RoleInterface>
     */
    private function getRoleCollection(): Collection
    {
        if (!$this->roles instanceof Collection) {
            $this->roles = new ArrayCollection();
        }

        return $this->roles;
    }

    public function __serialize(): array
    {
        [$enabled, $roles, $scopeFingerprint] = $this->getSerializedSecurityState();

        return [
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $enabled,
            $roles,
            $scopeFingerprint,
        ];
    }

    public function __unserialize(array $data): void
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->salt) = $data;

        $this->serializedEnabled = isset($data[4]) && \is_bool($data[4]) ? $data[4] : null;
    }
}
