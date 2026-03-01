<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\EventListener;

use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Symfony\Contracts\Cache\CacheInterface;

#[AsDocumentListener(event: Events::prePersist)]
#[AsDocumentListener(event: Events::preUpdate)]
#[AsDocumentListener(event: Events::postPersist)]
#[AsDocumentListener(event: Events::postUpdate)]
#[AsDocumentListener(event: Events::postRemove)]
class BlockUsageSubscriber
{
    public function __construct(
        private ?CacheInterface $cache = null,
    ) {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $document = $args->getDocument();
        if ($document instanceof AbstractPage) {
            if ($document->getLayoutVersion() === 2) {
                $document->updateBlockIdsFromLayoutPayload();
            } else {
                $document->updateBlockIdsFromGrids();
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $document = $args->getDocument();
        if (!$document instanceof AbstractPage) {
            return;
        }

        if ($document->getLayoutVersion() === 2) {
            $document->updateBlockIdsFromLayoutPayload();
        } else {
            $document->updateBlockIdsFromGrids();
        }
        $dm = $args->getDocumentManager();
        $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($dm->getClassMetadata($document::class), $document);
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->invalidateCacheForPageDocument($args->getDocument());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->invalidateCacheForPageDocument($args->getDocument());
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->invalidateCacheForPageDocument($args->getDocument());
    }

    private function invalidateCacheForPageDocument(object $document): void
    {
        if (!$document instanceof AbstractPage || !$this->cache instanceof CacheInterface) {
            return;
        }

        $this->cache->delete(BlockUsageProvider::CACHE_KEY);
    }
}
