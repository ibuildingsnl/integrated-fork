<?php

namespace Integrated\Bundle\UserBundle\DataFixtures\Faker\Provider;

use Integrated\Bundle\UserBundle\Model\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class EncodeProvider
{
    /**
     * @var PasswordHasherFactoryInterface
     */
    private $factory;

    public function __construct(PasswordHasherFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public function encodePassword(User $user, $password)
    {
        $user->setSalt(null);

        return $this->factory->getPasswordHasher($user)->hash($password);
    }
}
