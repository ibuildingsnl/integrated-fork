<?php

namespace Integrated\Bundle\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScopeAuthorizationChecker
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @param string[] $requiredScopes
     */
    public function assertScopes(array $requiredScopes): void
    {
        if ([] === $requiredScopes) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new AccessDeniedException('Missing authenticated token.');
        }

        $roles = $token->getRoleNames();

        foreach ($requiredScopes as $scope) {
            $requiredRole = 'SCOPE_'.$scope;
            if (!in_array($requiredRole, $roles, true)) {
                throw new AccessDeniedException(sprintf('Missing required scope "%s".', $scope));
            }
        }
    }
}
