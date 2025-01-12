<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Security;

use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserInterface as IntegratedUserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $manager;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;

        if (!is_subclass_of($this->manager->getClassName(), IntegratedUserInterface::class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'The user class "%s" is not subclass of %s',
                    $this->manager->getClassName(),
                    IntegratedUserInterface::class
                )
            );
        }
    }

    /**
     * @return UserManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    public function loadUserByIdentifier($username): UserInterface
    {
        /** @var User $user */
        $user = $this->manager->findEnabledByUsernameAndScope($username);

        if (!$user) {
            $exception = new UserNotFoundException(sprintf('No user with the username "%s" exists', $username));
            $exception->setUserIdentifier($username);

            throw $exception;
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$this->supportsClass($user::class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'The user class "%s" is not a instance or subclass of %s',
                    $user::class,
                    $this->manager->getClassName()
                )
            );
        }

        /** @var IntegratedUserInterface $user */
        $loaded = $this->manager->find($user->getId());

        if (!$loaded) {
            $exception = new UserNotFoundException(
                sprintf(
                    'The user with id "%s" could not be refreshed',
                    $user->getId()
                )
            );
            $exception->setUserIdentifier($user->getUserIdentifier());

            throw $exception;
        }

        return $loaded;
    }

    public function supportsClass(string $class): bool
    {
        return is_a($class, $this->manager->getClassName(), true);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof IntegratedUserInterface) {
            return;
        }

        $user->setPassword($newHashedPassword);
        $user->setSalt(null);

        $this->manager->persist($user);
    }
}
