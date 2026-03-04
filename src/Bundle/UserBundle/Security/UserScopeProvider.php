<?php

namespace Integrated\Bundle\UserBundle\Security;

use Integrated\Bundle\UserBundle\Context\ScopeContext;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserScopeProvider extends UserProvider
{
    /**
     * @var UserManagerInterface
     */
    protected $manager;

    /**
     * @var ScopeContext
     */
    private $context;

    public function __construct(UserManagerInterface $manager, ScopeContext $context)
    {
        $this->context = $context;
        parent::__construct($manager);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!$user = $this->manager->findEnabledByUsernameOrEmailAndScope($identifier, $this->context->getScope())) {
            $exception = new UserNotFoundException(\sprintf('No user with the identifier "%s" exists', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }
}
