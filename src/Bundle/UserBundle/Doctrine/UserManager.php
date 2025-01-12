<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\UserBundle\Model\ScopeInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class UserManager implements UserManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $om;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var PasswordHasherFactoryInterface
     */
    private $hasherFactory;

    public function __construct(EntityManagerInterface $om, string $class, PasswordHasherFactoryInterface $hasherFactory)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository($class);

        if (!is_subclass_of($this->repository->getClassName(), 'Integrated\\Bundle\\UserBundle\\Model\\UserInterface')) {
            throw new \InvalidArgumentException(sprintf('The class "%s" is not subclass of Integrated\\Bundle\\UserBundle\\Model\\UserInterface', $this->repository->getClassName()));
        }

        $this->hasherFactory = $hasherFactory;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    public function create()
    {
        $class = $this->getClassName();

        return new $class();
    }

    public function persist(UserInterface $user, $flush = true)
    {
        $this->om->persist($user);

        if ($flush) {
            $this->om->flush();
        }
    }

    public function remove(UserInterface $user, $flush = true)
    {
        $this->om->remove($user);

        if ($flush) {
            $this->om->flush();
        }
    }

    public function clear()
    {
        $this->om->clear();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findAll()
    {
        return $this->repository->findAll();
    }

    public function findByUsername($criteria)
    {
        return $this->repository->findOneBy(['username' => $criteria]);
    }

    public function findByEmail($criteria)
    {
        return $this->repository->findOneBy(['email' => $criteria]);
    }

    public function findByUsernameOrEmail($criteria)
    {
        if ($user = $this->findByUsername($criteria)) {
            return $user;
        }

        return $this->findByEmail($criteria);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function getClassName()
    {
        return $this->repository->getClassName();
    }

    public function findEnabledByUsernameAndScope($username, ?ScopeInterface $scope = null)
    {
        $builder = $this->createQueryBuilder()
            ->select('User')
            ->leftJoin('User.scope', 'Scope')
            ->where('User.username = :username')
            ->andWhere('User.enabled = true')
            ->setParameter('username', $username);

        if ($scope) {
            $builder->andWhere('(User.scope = :scope)');
            $builder->setParameter('scope', (int) $scope->getId());
        } else {
            $builder->andWhere('(Scope.admin = true)');
        }

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->repository->createQueryBuilder('User');
    }

    /**
     * @throws \Exception
     */
    public function changePassword(int $id, string $password): bool
    {
        if (!$user = $this->find($id)) {
            return false;
        }

        $user->setPassword($this->hasherFactory->getPasswordHasher($user)->hash($password));
        $user->setSalt(null);

        $this->persist($user);

        return true;
    }
}
