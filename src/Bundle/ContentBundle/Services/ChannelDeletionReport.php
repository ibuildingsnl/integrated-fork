<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Services;

final class ChannelDeletionReport
{
    private int $removedContent = 0;

    private int $detachedContent = 0;

    private int $removedPages = 0;

    private int $removedPublications = 0;

    private int $updatedBrands = 0;

    private bool $removedChannel = false;

    /** @var ChannelDeletionWarning[] */
    private array $warnings = [];

    /** @var array<int, array{class: string, id: string}> */
    private array $skippedDocuments = [];

    public function __construct(
        private readonly string $channelId,
    ) {
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function markRemovedContent(): void
    {
        ++$this->removedContent;
    }

    public function markDetachedContent(): void
    {
        ++$this->detachedContent;
    }

    public function markRemovedPage(): void
    {
        ++$this->removedPages;
    }

    public function markRemovedPublication(): void
    {
        ++$this->removedPublications;
    }

    public function markUpdatedBrand(): void
    {
        ++$this->updatedBrands;
    }

    public function markRemovedChannel(): void
    {
        $this->removedChannel = true;
    }

    public function addWarning(ChannelDeletionWarning $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function addSkippedDocument(string $class, string $id): void
    {
        $this->skippedDocuments[] = [
            'class' => $class,
            'id' => $id,
        ];
    }

    public function getRemovedContent(): int
    {
        return $this->removedContent;
    }

    public function getDetachedContent(): int
    {
        return $this->detachedContent;
    }

    public function getRemovedPages(): int
    {
        return $this->removedPages;
    }

    public function getRemovedPublications(): int
    {
        return $this->removedPublications;
    }

    public function getUpdatedBrands(): int
    {
        return $this->updatedBrands;
    }

    public function isRemovedChannel(): bool
    {
        return $this->removedChannel;
    }

    /**
     * @return ChannelDeletionWarning[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getWarningCount(): int
    {
        return \count($this->warnings);
    }

    /**
     * @return array<int, array{class: string, id: string}>
     */
    public function getSkippedDocuments(): array
    {
        return $this->skippedDocuments;
    }

    public function getStatus(): string
    {
        if (!$this->removedChannel) {
            return 'failed';
        }

        if ($this->warnings !== []) {
            return 'success_with_warnings';
        }

        return 'success';
    }

    public function isFailed(): bool
    {
        return $this->getStatus() === 'failed';
    }
}
