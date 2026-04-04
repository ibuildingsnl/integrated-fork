<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\EventListener;

use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use Integrated\Common\Content\Channel\ChannelInterface;

#[AsDocumentListener(event: Events::postPersist)]
#[AsDocumentListener(event: Events::postUpdate)]
#[AsDocumentListener(event: Events::postRemove)]
final class ContentSitemapCacheInvalidationSubscriber
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
        if (!$document instanceof Content) {
            return;
        }

        $contentType = trim((string) $document->getContentType());
        $isNews = $document instanceof News;

        foreach ($this->extractChannelIds($document) as $channelId) {
            $this->versionManager->bumpChannelContent($channelId);

            if ('' !== $contentType) {
                $this->versionManager->bumpChannelType($channelId, $contentType);
            }

            if ($isNews) {
                $this->versionManager->bumpChannelNews($channelId);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function extractChannelIds(Content $content): array
    {
        $channelIds = [];
        foreach ((array) $content->getChannels() as $channel) {
            if ($channel instanceof ChannelInterface) {
                $channelId = trim((string) $channel->getId());
                if ('' !== $channelId) {
                    $channelIds[$channelId] = true;
                }
            }
        }

        $primaryChannel = $content->getPrimaryChannel();
        if ($primaryChannel instanceof ChannelInterface) {
            $primaryChannelId = trim((string) $primaryChannel->getId());
            if ('' !== $primaryChannelId) {
                $channelIds[$primaryChannelId] = true;
            }
        }

        return array_keys($channelIds);
    }
}
