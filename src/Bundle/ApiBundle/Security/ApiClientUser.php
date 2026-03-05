<?php

namespace Integrated\Bundle\ApiBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class ApiClientUser implements UserInterface
{
    /**
     * @param string[] $scopes
     */
    public function __construct(
        private readonly string $identifier,
        private readonly array $scopes
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}
