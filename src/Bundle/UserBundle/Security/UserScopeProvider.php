<?php

namespace Integrated\Bundle\UserBundle\Security;

use Integrated\Bundle\UserBundle\Context\ScopeContext;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

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

    public function loadUserByUsername($username)
    {
        if (!$user = $this->manager->findEnabledByUsernameAndScope($username, $this->context->getScope())) {
            $exception = new UserNotFoundException(sprintf('No user with the username "%s" exists', $username));
            $exception->setUserIdentifier($username);

            throw $exception;
        }

        return $user;
    }
}
