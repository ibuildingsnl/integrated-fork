<?php

namespace Integrated\Bundle\InstallerBundle\Install;

use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ContentBundle\StaticContent\StaticContentRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StaticContent
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StaticContentRepository
     */
    private $repository;

    /**
     * Migrations constructor.
     *
     * @param EntityManager           $entityManager
     * @param ContainerInterface      $container
     * @param StaticContentRepository $repository
     */
    public function __construct(EntityManager $entityManager, ContainerInterface $container, StaticContentRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->repository = $repository;
    }

    public function execute()
    {
    }
}
