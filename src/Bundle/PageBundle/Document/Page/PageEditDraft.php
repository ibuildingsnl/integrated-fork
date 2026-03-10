<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Document\Page;

use Integrated\Bundle\PageBundle\Document\Page\Embedded\PageEditDraftVersion;

class PageEditDraft
{
    private string $id;

    private array $gridPayload = [];

    private array $menuPayload = [];

    private ?string $basePageUpdatedAt = null;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    /**
     * @var iterable<int, PageEditDraftVersion>
     */
    private $versions = [];

    public function __construct(
        private string $pageId,
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

    public function getPageId(): string
    {
        return $this->pageId;
    }

    public function setPageId(string $pageId): void
    {
        $this->pageId = $pageId;
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
     * @return array<mixed>
     */
    public function getGridPayload(): array
    {
        return $this->gridPayload;
    }

    /**
     * @param array<mixed> $gridPayload
     */
    public function setGridPayload(array $gridPayload): void
    {
        $this->gridPayload = $gridPayload;
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return array<mixed>
     */
    public function getMenuPayload(): array
    {
        return $this->menuPayload;
    }

    /**
     * @param array<mixed> $menuPayload
     */
    public function setMenuPayload(array $menuPayload): void
    {
        $this->menuPayload = $menuPayload;
        $this->updatedAt = new \DateTime();
    }

    public function getBasePageUpdatedAt(): ?string
    {
        return $this->basePageUpdatedAt;
    }

    public function setBasePageUpdatedAt(?string $basePageUpdatedAt): void
    {
        $this->basePageUpdatedAt = $basePageUpdatedAt !== null ? trim($basePageUpdatedAt) : null;
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return array<int, PageEditDraftVersion>
     */
    public function getVersions(): array
    {
        return $this->normalizeVersions($this->versions);
    }

    /**
     * @param iterable<int, PageEditDraftVersion> $versions
     */
    public function setVersions(iterable $versions): void
    {
        $this->versions = $this->normalizeVersions($versions);
    }

    /**
     * @param array<mixed> $gridPayload
     * @param array<mixed> $menuPayload
     */
    public function pushVersion(array $gridPayload, array $menuPayload, int $maxVersions = 25): void
    {
        $versions = $this->getVersions();
        array_unshift($versions, new PageEditDraftVersion($gridPayload, $menuPayload));

        if ($maxVersions > 0 && count($versions) > $maxVersions) {
            $versions = array_slice($versions, 0, $maxVersions);
        }

        $this->versions = $versions;
    }

    public function pruneVersions(?\DateTimeInterface $minimumSavedAt = null, int $maxVersions = 25): int
    {
        $versions = $this->getVersions();
        $before = count($versions);

        if ($minimumSavedAt instanceof \DateTimeInterface) {
            $threshold = $minimumSavedAt->getTimestamp();
            $versions = array_values(
                array_filter(
                    $versions,
                    static fn (PageEditDraftVersion $version): bool => $version->getSavedAt()->getTimestamp() >= $threshold
                )
            );
        }

        if ($maxVersions > 0 && count($versions) > $maxVersions) {
            $versions = array_slice($versions, 0, $maxVersions);
        }

        $this->versions = $versions;

        return max(0, $before - count($versions));
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
     * @return array<int, PageEditDraftVersion>
     */
    private function normalizeVersions(iterable $versions): array
    {
        if (!\is_array($versions)) {
            $versions = iterator_to_array($versions, false);
        }

        return array_values(
            array_filter(
                $versions,
                static fn ($version): bool => $version instanceof PageEditDraftVersion
            )
        );
    }
}
