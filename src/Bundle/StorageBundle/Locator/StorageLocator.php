<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Locator;

use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Storage\Cache\CacheInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class StorageLocator extends FileLocator
{
    /**
     * @var CacheInterface|null
     */
    private $cache;

    public function __construct(KernelInterface $kernel, CacheInterface $cache)
    {
        $this->cache = $cache;

        parent::__construct($kernel);
    }

    public function __serialize(): array
    {
        $this->cache = null;

        return [];
    }

    public function locate(string|StorageInterface $file, ?string $currentPath = null, bool $first = true): array|string
    {
        if ($file instanceof StorageInterface) {
            if (null === $this->cache) {
                throw new \InvalidArgumentException('File not found.');
            }

            try {
                return $this->cache->path($file)->getPathname();
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('File not found.');
            }
        }

        // Continue the normal symfony stuff
        return parent::locate($file, $currentPath, $first);
    }
}
