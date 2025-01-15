<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Storage;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Common\Storage\DecisionInterface;
use Integrated\Common\Storage\FilesystemRegistryInterface;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class Decision implements DecisionInterface
{
    /**
     * @var FilesystemRegistryInterface
     */
    protected $registry;

    /**
     * @var array
     */
    protected $decisionMap;

    private EntityManagerInterface $entityManager;

    public function __construct(FilesystemRegistryInterface $registry, array $decisionMap, EntityManagerInterface $entityManager)
    {
        $this->registry = $registry;
        $this->decisionMap = $decisionMap;
        $this->entityManager = $entityManager;
    }

    public function getFilesystems($object)
    {
        $className = $this->entityManager->getClassMetadata($object::class)->getName();
        if (isset($this->decisionMap[$className])) {
            return new ArrayCollection(array_values($this->decisionMap[$className]));
        }

        return new ArrayCollection($this->registry->keys());
    }
}
