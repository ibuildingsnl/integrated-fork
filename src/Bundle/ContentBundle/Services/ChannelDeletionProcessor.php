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
        private readonly ContentReverseReferenceCleaner $contentReverseReferenceCleaner,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function process(Channel $channel, bool $deleteReferenced): ChannelDeletionReport
    {
        $report = new ChannelDeletionReport($channel->getId());

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
                        $this->safeDocumentStep($document, 'detach', $report, function () use ($document, $channel, $report): void {
                            $document->removeChannel($channel);
                            $primary = $document->getPrimaryChannel();
                            if ($primary && $primary->getId() === $channel->getId()) {
                                $document->setPrimaryChannel(null);
                            }

                            $this->documentManager->persist($document);
                            $report->markDetachedContent();
                        });

                        continue;
                    }

                    $this->safeDocumentStep($document, 'delete', $report, function () use ($document, $report): void {
                        $this->contentReverseReferenceCleaner->cleanup($document);

                        if ($this->dispatcher->hasListeners(ContentEvents::CONTENT_DELETED)) {
                            $this->dispatcher->dispatch(
                                new ContentDeletedEvent($document),
                                ContentEvents::CONTENT_DELETED
                            );
                        }

                        $this->documentManager->remove($document);
                        $report->markRemovedContent();
                    });

                    continue;
                }

                if ($document instanceof AbstractPage) {
                    $this->safeDocumentStep($document, 'delete', $report, function () use ($document, $report): void {
                        $this->documentManager->remove($document);
                        $report->markRemovedPage();
                    });

                    continue;
                }

                if ($document instanceof ChannelLink) {
                    continue;
                }

                if (method_exists($document, 'removeChannel')) {
                    $this->safeDocumentStep($document, 'update', $report, function () use ($document, $channel): void {
                        $document->removeChannel($channel);
                        if (method_exists($document, 'getPrimaryChannel') && method_exists($document, 'setPrimaryChannel')) {
                            $primary = $document->getPrimaryChannel();
                            if ($primary && $primary->getId() === $channel->getId()) {
                                $document->setPrimaryChannel(null);
                            }
                        }

                        $this->documentManager->persist($document);
                    });
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
            $this->safeDocumentStep($publication, 'delete', $report, function () use ($publication, $report): void {
                $this->documentManager->remove($publication);
                $report->markRemovedPublication();
            });
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
                $this->safeDocumentStep($brand, 'update', $report, function () use ($brand, $report): void {
                    $this->documentManager->persist($brand);
                    $report->markUpdatedBrand();
                });
            }
        }

        $this->documentManager->remove($channel);
        $this->documentManager->flush();
        $report->markRemovedChannel();

        $this->dispatcher->dispatch(new ChannelEvent($channel), ChannelEvents::CHANNEL_DELETED);

        return $report;
    }

    /**
     * @param callable(): void $operation
     */
    private function safeDocumentStep(object $document, string $step, ChannelDeletionReport $report, callable $operation): void
    {
        try {
            $operation();
        } catch (\Throwable $exception) {
            $class = $document::class;
            $id = $this->resolveDocumentId($document);

            $report->addWarning(new ChannelDeletionWarning(
                $step,
                $class,
                $id,
                $exception->getMessage(),
                $exception::class
            ));
            $report->addSkippedDocument($class, $id);
        }
    }

    private function resolveDocumentId(object $document): string
    {
        if (!method_exists($document, 'getId')) {
            return '';
        }

        return (string) $document->getId();
    }
}
