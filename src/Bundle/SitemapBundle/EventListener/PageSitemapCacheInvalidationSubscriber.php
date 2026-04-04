<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\EventListener;

use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use Integrated\Common\Content\Channel\ChannelInterface;

#[AsDocumentListener(event: Events::postPersist)]
#[AsDocumentListener(event: Events::postUpdate)]
#[AsDocumentListener(event: Events::postRemove)]
final class PageSitemapCacheInvalidationSubscriber
{
    public function __construct(
        private readonly SitemapCacheVersionManager $versionManager,
    ) {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->invalidateForDocument($args->getDocument());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->invalidateForDocument($args->getDocument());
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->invalidateForDocument($args->getDocument());
    }

    private function invalidateForDocument(object $document): void
    {
        if (!$document instanceof AbstractPage) {
            return;
        }

        $channel = $document->getChannel();
        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $channelId = trim((string) $channel->getId());
        if ('' === $channelId) {
            return;
        }

        $this->versionManager->bumpChannelPages($channelId);
    }
}
