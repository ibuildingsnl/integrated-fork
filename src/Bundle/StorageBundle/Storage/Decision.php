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
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\Mapping\MappingException;
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

    private DocumentManager $manager;

    public function __construct(FilesystemRegistryInterface $registry, array $decisionMap, DocumentManager $manager)
    {
        $this->registry = $registry;
        $this->decisionMap = $decisionMap;
        $this->manager = $manager;
    }

    public function getFilesystems($object)
    {
        try {
            $className = $this->manager->getClassMetadata($object::class)->getName();
        } catch (MappingException $e) {
            $className = $object::class;
        }

        if (isset($this->decisionMap[$className])) {
            return new ArrayCollection(array_values($this->decisionMap[$className]));
        }

        return new ArrayCollection($this->registry->keys());
    }
}
