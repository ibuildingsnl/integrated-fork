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

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\UserBundle\Model\ScopeInterface;
use Integrated\Bundle\UserBundle\Model\ScopeManagerInterface;
use Integrated\Bundle\WorkflowBundle\Service\WorkflowAssigneeChoiceCacheInvalidator;

/**
 * @author Michael Jongman <michael@e-active.nl>
 */
class ScopeManager implements ScopeManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ObjectRepository
     */
    private $repository;

    private WorkflowAssigneeChoiceCacheInvalidator $workflowAssigneeChoiceCacheInvalidator;

    /**
     * @param string $class
     */
    public function __construct(ObjectManager $om, $class, WorkflowAssigneeChoiceCacheInvalidator $workflowAssigneeChoiceCacheInvalidator)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository($class);

        if (!is_subclass_of($this->repository->getClassName(), 'Integrated\\Bundle\\UserBundle\\Model\\ScopeInterface')) {
            throw new \InvalidArgumentException(\sprintf('The class "%s" is not subclass of Integrated\\Bundle\\UserBundle\\Model\\ScopeInterface', $this->repository->getClassName()));
        }

        $this->workflowAssigneeChoiceCacheInvalidator = $workflowAssigneeChoiceCacheInvalidator;
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

    public function persist(ScopeInterface $scope, $flush = true)
    {
        $this->om->persist($scope);

        if ($flush) {
            $this->om->flush();
        }

        $this->workflowAssigneeChoiceCacheInvalidator->invalidate();
    }

    public function remove(ScopeInterface $scope, $flush = true)
    {
        $this->om->remove($scope);

        if ($flush) {
            $this->om->flush();
        }

        $this->workflowAssigneeChoiceCacheInvalidator->invalidate();
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
        return $this->repository->findBy(['admin' => false]);
    }

    /**
     * @return ScopeInterface|null
     */
    public function findByName($name)
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function getClassName()
    {
        return $this->repository->getClassName();
    }
}
