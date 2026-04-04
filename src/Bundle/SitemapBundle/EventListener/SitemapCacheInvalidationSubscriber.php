<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Bundle\ContentBundle\Event\ContentDeletedEvent;
use Integrated\Bundle\ContentBundle\Event\ContentDistributedEvent;
use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events as ChannelEvents;
use Integrated\Common\Channel\ChannelManagerInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Form\Events as ContentEvents;
use Integrated\Common\ContentType\Event\ContentTypeEvent;
use Integrated\Common\ContentType\Events as ContentTypeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SitemapCacheInvalidationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SitemapCacheVersionManager $versionManager,
        private readonly ChannelManagerInterface $channelManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ChannelEvents::CHANNEL_CREATED => 'onChannelChanged',
            ChannelEvents::CHANNEL_UPDATED => 'onChannelChanged',
            ChannelEvents::CHANNEL_DELETED => 'onChannelChanged',
            ContentTypeEvents::CONTENT_TYPE_CREATED => 'onContentTypeChanged',
            ContentTypeEvents::CONTENT_TYPE_UPDATED => 'onContentTypeChanged',
            ContentTypeEvents::CONTENT_TYPE_DELETED => 'onContentTypeChanged',
            ContentEvents::CONTENT_DISTRIBUTED => 'onContentChanged',
            ContentEvents::CONTENT_DELETED => 'onContentChanged',
        ];
    }

    public function onChannelChanged(ChannelEvent $event): void
    {
        $channelId = trim((string) $event->getChannel()->getId());
        if ('' === $channelId) {
            return;
        }

        $this->versionManager->bumpChannel($channelId);
        $this->versionManager->bumpChannelContent($channelId);
        $this->versionManager->bumpChannelPages($channelId);
        $this->versionManager->bumpChannelNews($channelId);
    }

    public function onContentTypeChanged(ContentTypeEvent $event): void
    {
        $contentTypeId = trim((string) $event->getContentType()->getId());
        $isNewsType = News::class === trim((string) $event->getContentType()->getClass());

        foreach ($this->channelManager->findAll() as $channel) {
            if (!$channel instanceof ChannelInterface) {
                continue;
            }

            $channelId = trim((string) $channel->getId());
            if ('' === $channelId) {
                continue;
            }

            $this->versionManager->bumpChannel($channelId);
            $this->versionManager->bumpChannelContent($channelId);
            if ('' !== $contentTypeId) {
                $this->versionManager->bumpChannelType($channelId, $contentTypeId);
            }
            if ($isNewsType) {
                $this->versionManager->bumpChannelNews($channelId);
            }
        }
    }

    public function onContentChanged(ContentDistributedEvent|ContentDeletedEvent $event): void
    {
        $content = $event->getContent();
        $contentType = trim((string) $content->getContentType());
        $isNews = $content instanceof News;

        foreach ($this->extractChannelIds($content) as $channelId) {
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
