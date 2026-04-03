<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
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
                        $this->safeMutationStep(
                            $document,
                            'detach',
                            $report,
                            fn (): array => [
                                'channels' => $document->getChannels(),
                                'primary_channel' => $document->getPrimaryChannel(),
                            ],
                            function (array $snapshot) use ($document, $channel, $report): void {
                                $document->removeChannel($channel);
                                $primary = $snapshot['primary_channel'];
                                if ($primary && $primary->getId() === $channel->getId()) {
                                    $document->setPrimaryChannel(null);
                                }

                                $this->documentManager->persist($document);
                                $report->markDetachedContent();
                            },
                            fn (array $snapshot): bool => $this->restoreDocumentChannelState(
                                $document,
                                $snapshot['channels'],
                                $snapshot['primary_channel']
                            )
                        );

                        continue;
                    }

                    $this->contentReverseReferenceCleaner->cleanup($document);
                    $this->safeWarningStep($document, 'dispatch', $report, function () use ($document): void {
                        if (!$this->dispatcher->hasListeners(ContentEvents::CONTENT_DELETED)) {
                            return;
                        }

                        $this->dispatcher->dispatch(
                            new ContentDeletedEvent($document),
                            ContentEvents::CONTENT_DELETED
                        );
                    });

                    $this->safeDocumentStep($document, 'delete', $report, function () use ($document, $report): void {
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
                    $this->safeMutationStep(
                        $document,
                        'update',
                        $report,
                        fn (): array => [
                            'channels' => method_exists($document, 'getChannels') ? (array) $document->getChannels() : [],
                            'primary_channel' => method_exists($document, 'getPrimaryChannel') ? $document->getPrimaryChannel() : null,
                        ],
                        function (array $snapshot) use ($document, $channel): void {
                            $document->removeChannel($channel);
                            $primary = $snapshot['primary_channel'];
                            if ($primary && method_exists($document, 'setPrimaryChannel') && $primary->getId() === $channel->getId()) {
                                $document->setPrimaryChannel(null);
                            }

                            $this->documentManager->persist($document);
                        },
                        fn (array $snapshot): bool => $this->restoreDocumentChannelState(
                            $document,
                            $snapshot['channels'],
                            $snapshot['primary_channel']
                        )
                    );
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
            $this->safeMutationStep(
                $brand,
                'update',
                $report,
                fn (): array => $brand->getChannelLinks()->toArray(),
                function (array $links) use ($brand, $channel, $report): void {
                    $changed = false;
                    foreach ($links as $link) {
                        if (!$link instanceof ChannelLink) {
                            continue;
                        }

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

                    if (!$changed) {
                        return;
                    }

                    $this->documentManager->persist($brand);
                    $report->markUpdatedBrand();
                },
                fn (array $links): bool => $this->restoreBrandChannelLinks($brand, $links)
            );
        }

        $this->documentManager->remove($channel);
        $this->documentManager->flush();
        $report->markRemovedChannel();

        $this->safeWarningStep($channel, 'dispatch', $report, function () use ($channel): void {
            $this->dispatcher->dispatch(new ChannelEvent($channel), ChannelEvents::CHANNEL_DELETED);
        });

        return $report;
    }

    /**
     * @param callable(): void $operation
     */
    private function safeDocumentStep(object $document, string $step, ChannelDeletionReport $report, callable $operation): void
    {
        $this->safeWarningStep($document, $step, $report, $operation, true);
    }

    /**
     * @param callable(): mixed $snapshotFactory
     * @param callable(mixed): void $operation
     * @param callable(mixed): bool $rollback
     */
    private function safeMutationStep(
        object $document,
        string $step,
        ChannelDeletionReport $report,
        callable $snapshotFactory,
        callable $operation,
        callable $rollback
    ): void {
        $snapshotCaptured = false;
        $snapshot = null;

        try {
            $snapshot = $snapshotFactory();
            $snapshotCaptured = true;
            $operation($snapshot);
        } catch (\Throwable $exception) {
            if ($snapshotCaptured && !$rollback($snapshot)) {
                throw $exception;
            }

            $this->recordWarning($document, $step, $report, $exception, true);
        }
    }

    /**
     * @param callable(): void $operation
     */
    private function safeWarningStep(
        object $document,
        string $step,
        ChannelDeletionReport $report,
        callable $operation,
        bool $recordSkippedDocument = false
    ): void
    {
        try {
            $operation();
        } catch (\Throwable $exception) {
            $this->recordWarning($document, $step, $report, $exception, $recordSkippedDocument);
        }
    }

    private function recordWarning(
        object $document,
        string $step,
        ChannelDeletionReport $report,
        \Throwable $exception,
        bool $recordSkippedDocument
    ): void {
        $class = $document::class;
        $id = $this->resolveDocumentId($document);

        $report->addWarning(new ChannelDeletionWarning(
            $step,
            $class,
            $id,
            $exception->getMessage(),
            $exception::class
        ));
        if ($recordSkippedDocument) {
            $report->addSkippedDocument($class, $id);
        }
    }

    /**
     * @param array<int, object> $channels
     */
    private function restoreDocumentChannelState(object $document, array $channels, ?object $primaryChannel): bool
    {
        if (method_exists($document, 'removeChannels') && method_exists($document, 'addChannel')) {
            $document->removeChannels();
            foreach ($channels as $channel) {
                $document->addChannel($channel);
            }
            if (method_exists($document, 'setPrimaryChannel')) {
                $document->setPrimaryChannel($primaryChannel);
            }

            return true;
        }

        $channelsRestored = $this->writeObjectProperty($document, 'channels', new ArrayCollection($channels));
        $primaryRestored = true;

        if (method_exists($document, 'setPrimaryChannel')) {
            $document->setPrimaryChannel($primaryChannel);
        } else {
            $primaryRestored = $this->writeObjectProperty($document, 'primaryChannel', $primaryChannel);
        }

        return $channelsRestored && $primaryRestored;
    }

    /**
     * @param array<int, ChannelLink> $links
     */
    private function restoreBrandChannelLinks(Brand $brand, array $links): bool
    {
        return $this->writeObjectProperty($brand, 'channelLinks', new ArrayCollection($links));
    }

    private function writeObjectProperty(object $document, string $property, mixed $value): bool
    {
        $reflection = new \ReflectionObject($document);

        do {
            if ($reflection->hasProperty($property)) {
                $reflectionProperty = $reflection->getProperty($property);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($document, $value);

                return true;
            }

            $reflection = $reflection->getParentClass();
        } while ($reflection !== false);

        return false;
    }

    private function resolveDocumentId(object $document): string
    {
        if (!method_exists($document, 'getId')) {
            return '';
        }

        return (string) $document->getId();
    }
}
