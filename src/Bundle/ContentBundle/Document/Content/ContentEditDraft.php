<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\ContentEditDraftVersion;

class ContentEditDraft
{
    private string $id;

    /**
     * @var array<string, mixed>
     */
    private array $payload = [];

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    /**
     * @var iterable<int, ContentEditDraftVersion>
     */
    private $versions = [];

    public function __construct(
        private string $contentId,
        private string $userId,
    ) {
        $now = new \DateTime();
        $this->createdAt = $now;
        $this->updatedAt = clone $now;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getContentId(): string
    {
        return $this->contentId;
    }

    public function setContentId(string $contentId): void
    {
        $this->contentId = $contentId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return array<int, ContentEditDraftVersion>
     */
    public function getVersions(): array
    {
        return $this->normalizeVersions($this->versions);
    }

    /**
     * @param iterable<int, ContentEditDraftVersion> $versions
     */
    public function setVersions(iterable $versions): void
    {
        $this->versions = $this->normalizeVersions($versions);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function pushVersion(array $payload, int $maxVersions = 25): void
    {
        $versions = $this->getVersions();
        array_unshift($versions, new ContentEditDraftVersion($payload));

        if ($maxVersions > 0 && \count($versions) > $maxVersions) {
            $versions = \array_slice($versions, 0, $maxVersions);
        }

        $this->versions = $versions;
    }

    public function pruneVersions(?\DateTimeInterface $minimumSavedAt = null, int $maxVersions = 25): int
    {
        $versions = $this->getVersions();
        $before = \count($versions);

        if ($minimumSavedAt instanceof \DateTimeInterface) {
            $threshold = $minimumSavedAt->getTimestamp();
            $versions = array_values(
                array_filter(
                    $versions,
                    static fn (ContentEditDraftVersion $version): bool => $version->getSavedAt()->getTimestamp() >= $threshold
                )
            );
        }

        if ($maxVersions > 0 && \count($versions) > $maxVersions) {
            $versions = \array_slice($versions, 0, $maxVersions);
        }

        $this->versions = $versions;

        return max(0, $before - \count($versions));
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param iterable<int, mixed> $versions
     *
     * @return array<int, ContentEditDraftVersion>
     */
    private function normalizeVersions(iterable $versions): array
    {
        if (!\is_array($versions)) {
            $versions = iterator_to_array($versions, false);
        }

        return array_values(
            array_filter(
                $versions,
                static fn ($version): bool => $version instanceof ContentEditDraftVersion
            )
        );
    }
}
