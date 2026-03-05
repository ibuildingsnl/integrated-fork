<?php

namespace Integrated\Bundle\ApiBundle\Security;

class TokenIntrospectionResult
{
    /**
     * @param string[] $scopes
     */
    public function __construct(
        private readonly bool $active,
        private readonly string $subject = '',
        private readonly array $scopes = []
    ) {
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}
