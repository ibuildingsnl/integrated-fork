<?php

namespace Integrated\Bundle\UserBundle\Handler;

use Integrated\Bundle\UserBundle\Handler\Exception\UniqueUserException;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class RegisterHandler
{
    /**
     * @var UserManagerInterface
     */
    private $manager;

    /**
     * @var PasswordHasherFactoryInterface
     */
    private $factory;

    public function __construct(UserManagerInterface $manager, PasswordHasherFactoryInterface $factory)
    {
        $this->manager = $manager;
        $this->factory = $factory;
    }

    public function handle(User $user): UserInterface
    {
        if ($data = $this->manager->findOneBy(['username' => $user->getUserIdentifier(), 'scope' => $user->getScope()])) {
            if ($data->isEnabled()) {
                throw new UniqueUserException('E-mail address is already in use');
            }
        } else {
            $data = $user;
        }

        $data->setPassword($this->factory->getPasswordHasher($data)->hash($user->getPassword()));

        $this->manager->persist($data);

        return $data;
    }
}
