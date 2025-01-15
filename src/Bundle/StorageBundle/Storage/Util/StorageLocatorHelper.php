<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Storage\Util;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\StorageBundle\Storage\Mapping\Metadata;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;

/**
 * This objects helps to create a storage object, to help finding the file object.
 *
 * @author Marijn Otte <marijn@e-active.nl>
 */
class StorageLocatorHelper implements StorageInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $pathname;

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var ArrayCollection
     */
    protected $filesystems;

    /**
     * @param string $identifier
     * @param array  $filesystems
     */
    public function __construct($identifier, $filesystems)
    {
        $this->identifier = $identifier;
        $this->filesystems = new ArrayCollection($filesystems);
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getPathname()
    {
        return $this->pathname;
    }

    public function getFilesystems()
    {
        return new ArrayCollection($this->filesystems);
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function __toString()
    {
        return $this->pathname;
    }
}
