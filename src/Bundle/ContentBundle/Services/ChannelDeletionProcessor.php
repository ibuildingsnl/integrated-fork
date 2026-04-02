<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Event\ContentDeletedEvent;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events as ChannelEvents;
use Integrated\Common\Content\Form\Events as ContentEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ChannelDeletionProcessor
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly SearchContentReferenced $searchContentReferenced,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @return array{
     *   removed_content: int,
     *   detached_content: int,
     *   removed_pages: int,
     *   removed_publications: int,
     *   updated_brands: int
     * }
     */
    public function process(Channel $channel, bool $deleteReferenced): array
    {
        $summary = [
            'removed_content' => 0,
            'detached_content' => 0,
            'removed_pages' => 0,
            'removed_publications' => 0,
            'updated_brands' => 0,
        ];

        $referencedDocuments = $this->searchContentReferenced->getReferencedDocuments($channel);
        if (!$deleteReferenced && \count($referencedDocuments) > 0) {
            throw new \RuntimeException('Channel has related content/pages; deletion was not confirmed to remove related documents.');
        }

        if ($deleteReferenced) {
            foreach ($referencedDocuments as $document) {
                if ($document instanceof Content) {
                    $channels = $document->getChannels();
                    $onlyThisChannel = \count($channels) <= 1;
                    if (!$onlyThisChannel) {
                        $document->removeChannel($channel);
                        $primary = $document->getPrimaryChannel();
                        if ($primary && $primary->getId() === $channel->getId()) {
                            $document->setPrimaryChannel(null);
                        }
                        $this->documentManager->persist($document);
                        ++$summary['detached_content'];

                        continue;
                    }

                    if ($this->dispatcher->hasListeners(ContentEvents::CONTENT_DELETED)) {
                        $this->dispatcher->dispatch(
                            new ContentDeletedEvent($document),
                            ContentEvents::CONTENT_DELETED
                        );
                    }
                    $this->documentManager->remove($document);
                    ++$summary['removed_content'];

                    continue;
                }

                if ($document instanceof AbstractPage) {
                    $this->documentManager->remove($document);
                    ++$summary['removed_pages'];

                    continue;
                }

                if ($document instanceof ChannelLink) {
                    continue;
                }

                if (method_exists($document, 'removeChannel')) {
                    $document->removeChannel($channel);
                    if (method_exists($document, 'getPrimaryChannel') && method_exists($document, 'setPrimaryChannel')) {
                        $primary = $document->getPrimaryChannel();
                        if ($primary && $primary->getId() === $channel->getId()) {
                            $document->setPrimaryChannel(null);
                        }
                    }
                    $this->documentManager->persist($document);
                }
            }
        }

        $publications = $this->documentManager->getRepository(Publication::class)
            ->createQueryBuilder()
            ->field('channel.$id')
            ->equals($channel->getId())
            ->getQuery()
            ->toArray();

        foreach ($publications as $publication) {
            $this->documentManager->remove($publication);
            ++$summary['removed_publications'];
        }

        $brands = $this->documentManager->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            $changed = false;
            foreach ($brand->getChannelLinks()->toArray() as $link) {
                if (!$link->channel) {
                    $brand->removeChannelLink($link);
                    $changed = true;

                    continue;
                }
                if ($link->channel->getId() === $channel->getId()) {
                    $brand->removeChannelLink($link);
                    $changed = true;
                }
            }
            if ($changed) {
                $this->documentManager->persist($brand);
                ++$summary['updated_brands'];
            }
        }

        $this->documentManager->remove($channel);
        $this->documentManager->flush();

        $this->dispatcher->dispatch(new ChannelEvent($channel), ChannelEvents::CHANNEL_DELETED);

        return $summary;
    }
}
