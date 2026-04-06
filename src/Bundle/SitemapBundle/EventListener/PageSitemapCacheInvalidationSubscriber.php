<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\EventListener;

use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use Integrated\Common\Content\Channel\ChannelInterface;

#[AsDocumentListener(event: Events::preUpdate)]
#[AsDocumentListener(event: Events::preRemove)]
#[AsDocumentListener(event: Events::postPersist)]
#[AsDocumentListener(event: Events::postUpdate)]
#[AsDocumentListener(event: Events::postRemove)]
final class PageSitemapCacheInvalidationSubscriber
{
    /** @var array<int, list<string>> */
    private array $previousChannelIds = [];

    public function __construct(
        private readonly SitemapCacheVersionManager $versionManager,
    ) {
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $document = $args->getDocument();
        if (!$document instanceof AbstractPage) {
            return;
        }

        $channelIds = [];

        if ($args->hasChangedField('channel')) {
            $channelIds = $this->extractChannelIds($args->getOldValue('channel'));
        }

        $this->rememberPreviousChannelIds($document, $channelIds);
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $document = $args->getDocument();
        if (!$document instanceof AbstractPage) {
            return;
        }

        $this->rememberPreviousChannelIds($document, $this->extractChannelIds($document->getChannel()));
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

        $channelIds = $this->extractChannelIds($document->getChannel());
        $channelIds = array_values(array_unique(array_merge(
            $channelIds,
            $this->pullPreviousChannelIds($document)
        )));

        foreach ($channelIds as $channelId) {
            $this->versionManager->bumpChannelPages($channelId);
        }
    }

    /**
     * @return list<string>
     */
    private function extractChannelIds(mixed $channels): array
    {
        if ($channels instanceof ChannelInterface) {
            $channelId = trim((string) $channels->getId());

            return '' === $channelId ? [] : [$channelId];
        }

        if (!is_iterable($channels)) {
            return [];
        }

        $channelIds = [];
        foreach ($channels as $channel) {
            if (!$channel instanceof ChannelInterface) {
                continue;
            }

            $channelId = trim((string) $channel->getId());
            if ('' !== $channelId) {
                $channelIds[$channelId] = true;
            }
        }

        return array_keys($channelIds);
    }

    /**
     * @param list<string> $channelIds
     */
    private function rememberPreviousChannelIds(AbstractPage $document, array $channelIds): void
    {
        if ([] === $channelIds) {
            return;
        }

        $this->previousChannelIds[spl_object_id($document)] = $channelIds;
    }

    /**
     * @return list<string>
     */
    private function pullPreviousChannelIds(AbstractPage $document): array
    {
        $key = spl_object_id($document);
        $channelIds = $this->previousChannelIds[$key] ?? [];
        unset($this->previousChannelIds[$key]);

        return $channelIds;
    }
}
