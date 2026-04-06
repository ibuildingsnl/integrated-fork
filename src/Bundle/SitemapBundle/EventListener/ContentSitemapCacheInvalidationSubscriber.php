<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\EventListener;

use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use Integrated\Common\Content\Channel\ChannelInterface;

#[AsDocumentListener(event: Events::preUpdate)]
#[AsDocumentListener(event: Events::preRemove)]
#[AsDocumentListener(event: Events::postPersist)]
#[AsDocumentListener(event: Events::postUpdate)]
#[AsDocumentListener(event: Events::postRemove)]
final class ContentSitemapCacheInvalidationSubscriber
{
    /** @var array<int, list<string>> */
    private array $previousChannelIds = [];

    /** @var array<int, string> */
    private array $previousContentTypes = [];

    public function __construct(
        private readonly SitemapCacheVersionManager $versionManager,
    ) {
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $document = $args->getDocument();
        if (!$document instanceof Content) {
            return;
        }

        $channelIds = [];
        if ($args->hasChangedField('channels')) {
            $channelIds = array_merge($channelIds, $this->extractChannelIds($args->getOldValue('channels')));
        }
        if ($args->hasChangedField('primaryChannel')) {
            $channelIds = array_merge($channelIds, $this->extractChannelIds($args->getOldValue('primaryChannel')));
        }

        $this->rememberPreviousChannelIds($document, array_values(array_unique($channelIds)));

        if ($args->hasChangedField('contentType')) {
            $oldContentType = trim((string) $args->getOldValue('contentType'));
            if ('' !== $oldContentType) {
                $this->previousContentTypes[spl_object_id($document)] = $oldContentType;
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $document = $args->getDocument();
        if (!$document instanceof Content) {
            return;
        }

        $this->rememberPreviousChannelIds($document, $this->extractChannelIds($document->getChannels()));

        $primaryChannelIds = $this->extractChannelIds($document->getPrimaryChannel());
        if ([] !== $primaryChannelIds) {
            $this->rememberPreviousChannelIds($document, array_values(array_unique(array_merge(
                $this->previousChannelIds[spl_object_id($document)] ?? [],
                $primaryChannelIds
            ))));
        }

        $contentType = trim((string) $document->getContentType());
        if ('' !== $contentType) {
            $this->previousContentTypes[spl_object_id($document)] = $contentType;
        }
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

        $channelIds = array_values(array_unique(array_merge(
            $this->extractChannelIds($document->getChannels()),
            $this->extractChannelIds($document->getPrimaryChannel()),
            $this->pullPreviousChannelIds($document)
        )));

        $contentTypes = [];
        $contentType = trim((string) $document->getContentType());
        if ('' !== $contentType) {
            $contentTypes[$contentType] = true;
        }

        $previousContentType = $this->pullPreviousContentType($document);
        if ('' !== $previousContentType) {
            $contentTypes[$previousContentType] = true;
        }

        $isNews = $document instanceof News;

        foreach ($channelIds as $channelId) {
            $this->versionManager->bumpChannelContent($channelId);

            foreach (array_keys($contentTypes) as $contentType) {
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
            if ($channel instanceof ChannelInterface) {
                $channelId = trim((string) $channel->getId());
                if ('' !== $channelId) {
                    $channelIds[$channelId] = true;
                }
            }
        }

        return array_keys($channelIds);
    }

    /**
     * @param list<string> $channelIds
     */
    private function rememberPreviousChannelIds(Content $document, array $channelIds): void
    {
        if ([] === $channelIds) {
            return;
        }

        $key = spl_object_id($document);
        $this->previousChannelIds[$key] = array_values(array_unique(array_merge(
            $this->previousChannelIds[$key] ?? [],
            $channelIds
        )));
    }

    /**
     * @return list<string>
     */
    private function pullPreviousChannelIds(Content $document): array
    {
        $key = spl_object_id($document);
        $channelIds = $this->previousChannelIds[$key] ?? [];
        unset($this->previousChannelIds[$key]);

        return $channelIds;
    }

    private function pullPreviousContentType(Content $document): string
    {
        $key = spl_object_id($document);
        $contentType = $this->previousContentTypes[$key] ?? '';
        unset($this->previousContentTypes[$key]);

        return $contentType;
    }
}
