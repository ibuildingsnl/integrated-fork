<?php

namespace Integrated\Bundle\ContentBundle\DataFixtures\Util;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Storage\ManagerInterface;

class StorageUtil
{
    /**
     * @return StorageInterface
     *
     * @throws \Exception
     */
    public static function creatFromPath(ManagerInterface $manager, string $path)
    {
        // Make sure we've got a winner
        if (file_exists($path)) {
            // Use a reader and set the mime type manually based on the path
            return $manager->write(
                new MemoryReader(
                    // Use the file_get_contents to support local and remote (http) protocols
                    file_get_contents($path),
                    // Metadata
                    new Storage\Metadata(
                        substr($path, strrpos($path, '.') + 1),
                        mime_content_type($path),
                        new ArrayCollection(),
                        new ArrayCollection()
                    )
                )
            );
        }

        // File does not exist or is not readable
        throw new \Exception(\sprintf('The file %s to put in the storage does not exist', $path));
    }
}
